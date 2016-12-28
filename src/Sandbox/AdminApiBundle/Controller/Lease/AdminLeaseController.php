<?php

namespace Sandbox\AdminApiBundle\Controller\Lease;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AdminLeaseController extends AdminRestController
{
    use GenerateSerialNumberTrait;
    use HasAccessToEntityRepositoryTrait;

    /**
     * Get Lease Detail.
     *
     * @param $id
     *
     * @Route("/leases/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLeaseAction(
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_VIEW);

        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')->find($id);

        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $this->setLeaseAttributions($lease);

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['main'])
        );
        $view->setData($lease);

        return $view;
    }

    /**
     * @param Lease $lease
     */
    private function setLeaseAttributions(
        $lease
    ) {
        $changeLogs = array();
        $appointment = $lease->getProductAppointment();
        if (!is_null($appointment)) {
            $changeLogs['applicant'] = $appointment->getApplicantName();
            $changeLogs['apply_date'] = $appointment->getCreationDate();
        }

        $logConforming = $this->getDoctrine()
           ->getRepository('SandboxApiBundle:Log\Log')
           ->getLatestAdminLog(
               Log::MODULE_LEASE,
               Log::OBJECT_LEASE,
               $lease->getId(),
               array(
                   Log::ACTION_CREATE,
               )
           );
        if (!is_null($logConforming)) {
            $changeLogs['lease_conforming_admin'] = $this->getUserProfileName($logConforming->getAdminUsername());
            $changeLogs['lease_conforming_date'] = $logConforming->getCreationDate();
        }

        if (!is_null($logConformed)) {
            $changeLogs['lease_conformed_user'] = $this->getUserProfileName($lease->getSupervisor());
            $changeLogs['lease_conformed_date'] = $lease->getConformedDate();
        }

        $logPerforming = $this->getDoctrine()
           ->getRepository('SandboxApiBundle:Log\Log')
           ->getLatestAdminLog(
               Log::MODULE_LEASE,
               Log::OBJECT_LEASE,
               $lease->getId(),
               array(
                   Log::ACTION_PERFORMING,
               )
           );
        if (!is_null($logPerforming)) {
            $changeLogs['lease_performing_admin'] = $this->getUserProfileName($logPerforming->getAdminUsername());
            $changeLogs['lease_performing_date'] = $logPerforming->getCreationDate();
        }

        $logClose = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Log\Log')
            ->getLatestAdminLog(
                Log::MODULE_LEASE,
                Log::OBJECT_LEASE,
                $lease->getId(),
                array(
                    Log::ACTION_CLOSE,
                    Log::ACTION_TERMINATE,
                    Log::ACTION_END,
                )
            );
        if (!is_null($logClose)) {
            $changeLogs['lease_close_admin'] = $this->getUserProfileName($logClose->getAdminUsername());
            $changeLogs['lease_close_date'] = $logClose->getCreationDate();
        }

        $lease->setChangeLogs($changeLogs);

        $bills = $this->getLeaseBillRepo()->findBy(array(
            'lease' => $lease,
            'type' => LeaseBill::TYPE_LEASE,
        ));
        $lease->setBills($bills);

        $totalLeaseBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBills(
                $lease,
                LeaseBill::TYPE_LEASE
            );
        $lease->setTotalLeaseBillsAmount($totalLeaseBills);

        $paidLeaseBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBills(
                $lease,
                LeaseBill::TYPE_LEASE,
                [LeaseBill::STATUS_UNPAID, LeaseBill::STATUS_PAID]
            );
        $lease->setPaidLeaseBillsAmount($paidLeaseBills);

        $otherBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBills(
                $lease,
                LeaseBill::TYPE_OTHER
            );
        $lease->setOtherBillsAmount($otherBills);

        $pendingLeaseBill = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->sumBillsFees(
                $lease,
                LeaseBill::STATUS_PENDING
            );
        $pendingLeaseBill = is_null($pendingLeaseBill) ? 0 : $pendingLeaseBill;
        $lease->setPushedLeaseBillsFees($pendingLeaseBill);
    }

    /**
     * @param $userId
     *
     * @return string
     */
    private function getUserProfileName(
        $userId
    ) {
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->find($userId);

        if (is_null($user)) {
            return '';
        }

        return $user->getName();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/leases/{id}/export_to_pdf")
     * @Method({"GET"})
     *
     * @return Response
     */
    public function exportLeaseToPdfAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        //authenticate with web browser cookie
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_LEASE,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW,
            AdminPermission::PERMISSION_PLATFORM_OFFICIAL
        );

        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')->find($id);

        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBy(array(
                'lease' => $lease,
                'type' => LeaseBill::TYPE_LEASE,
            ));

        $drawee = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->find($lease->getDrawee()->getId());

        $supervisor = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->find($lease->getSupervisor()->getId());

        $excludeLeaseRentTypes = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseRentTypes')
            ->getExcludeLeaseRentTypes($lease);

        $html = $this->renderView(':Leases:leases_print.html.twig', array(
            'lease' => $lease,
            'drawee' => $drawee,
            'supervisor' => $supervisor,
            'draweeAvatarUrl' => $this->generateAvatarUrl($drawee->getId()),
            'supervisorAvatarUrl' => $this->generateAvatarUrl($supervisor->getId()),
            'excludeTypes' => $excludeLeaseRentTypes,
            'bills' => $bills,
        ));

        $fileName = $lease->getSerialNumber().'.pdf';

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename='$fileName'",
            )
        );
    }

    /**
     * @param $userId
     *
     * @return string
     */
    private function generateAvatarUrl(
        $userId
    ) {
        $imageDomain = $this->container->getParameter('image_url');
        $supervisorAvatarUrl = $imageDomain.'/person/'.$userId.'/avatar_small.jpg';
        $ch = curl_init($supervisorAvatarUrl);
        $this->callAPI($ch, 'GET');
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == '404') {
            return 'https://property.sandbox3.cn/img/head.png';
        }
    }

    /**
     * authenticate with web browser cookie.
     */
    protected function authenticateAdminCookie()
    {
        $cookie_name = self::ADMIN_COOKIE_NAME;
        if (!isset($_COOKIE[$cookie_name])) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $token = $_COOKIE[$cookie_name];
        $adminToken = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserToken')
            ->findOneBy(array(
                'token' => $token,
            ));
        if (is_null($adminToken)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        return $adminToken->getUser();
    }
    /**
     * Get List of Lease.
     *
     * @Route("/leases")
     * @Method({"GET"})
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default="all",
     *    nullable=true,
     *    strict=true,
     *    description="status of lease"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="applicant, room, number"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_date_range",
     *    default=null,
     *    nullable=true,
     *    description="last_week, last_month"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    default=null,
     *    nullable=true,
     *    description="create start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_end",
     *    default=null,
     *    nullable=true,
     *    description="create end date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="rent_filter",
     *    default=null,
     *    nullable=true,
     *    description="rent time filter keywords"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment end date"
     * )
     *
     * @return View
     */
    public function getLeasesAction(
        ParamFetcherInterface $paramFetcher,
        Request $request
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

        $status = $paramFetcher->get('status');

        // search keyword and query
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        // creation date filter
        $createRange = $paramFetcher->get('create_date_range');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');

        // rent date filter
        $rentFilter = $paramFetcher->get('rent_filter');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        $leases = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->findLeases(
                null,
                $status,
                $keyword,
                $keywordSearch,
                $createRange,
                $createStart,
                $createEnd,
                $rentFilter,
                $startDate,
                $endDate,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->countLeasesAmount(
                null,
                $status,
                $keyword,
                $keywordSearch,
                $createRange,
                $createStart,
                $createEnd,
                $rentFilter,
                $startDate,
                $endDate
            );

        foreach ($leases as $lease) {
            $totalLeaseBills = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease,
                    LeaseBill::TYPE_LEASE
                );
            $lease->setTotalLeaseBillsAmount($totalLeaseBills);

            $paidLeaseBills = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease,
                    LeaseBill::TYPE_LEASE,
                    [LeaseBill::STATUS_UNPAID, LeaseBill::STATUS_PAID]
                );
            $lease->setPaidLeaseBillsAmount($paidLeaseBills);

            $otherBills = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease,
                    LeaseBill::TYPE_OTHER
                );
            $lease->setOtherBillsAmount($otherBills);

            $pendingLeaseBill = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->sumBillsFees(
                    $lease,
                    LeaseBill::STATUS_PENDING
                );
            $pendingLeaseBill = is_null($pendingLeaseBill) ? 0 : $pendingLeaseBill;
            $lease->setPushedLeaseBillsFees($pendingLeaseBill);
        }

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['lease_list'])
        );

        $view->setData(
            array(
            'current_page_number' => (int) $pageIndex,
            'num_items_per_page' => (int) $pageLimit,
            'items' => $leases,
            'total_count' => (int) $count,
        ));

        return $view;
    }

    /**
     * @param $opLevel
     */
    private function checkAdminLeasePermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_LEASE],
            ],
            $opLevel
        );
    }
}
