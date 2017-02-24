<?php

namespace Sandbox\AdminApiBundle\Controller\Lease;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
use Sandbox\ApiBundle\Traits\LeaseTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Response;

class AdminLeaseController extends AdminRestController
{
    use GenerateSerialNumberTrait;
    use HasAccessToEntityRepositoryTrait;
    use LeaseTrait;

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

        $this->setLeaseLogs($lease);

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['main'])
        );
        $view->setData($lease);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/leases/export_to_pdf/{id}")
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
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="room",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by room id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by company id"
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
        $pageLimit = (int) $paramFetcher->get('pageLimit');
        $pageIndex = (int) $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

        $status = $paramFetcher->get('status');
        $buildingId = $paramFetcher->get('building');
        $companyId = $paramFetcher->get('company');
        $roomId = $paramFetcher->get('room');

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

        $buildingIds = null;
        if (!is_null($buildingId)) {
            $buildingIds = array((int) $buildingId);
        }

        $leases = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->findLeases(
                $buildingIds,
                $status,
                $keyword,
                $keywordSearch,
                $createRange,
                $createStart,
                $createEnd,
                $rentFilter,
                $startDate,
                $endDate,
                $companyId,
                $roomId,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->countLeasesAmount(
                $buildingIds,
                $status,
                $keyword,
                $keywordSearch,
                $createRange,
                $createStart,
                $createEnd,
                $rentFilter,
                $startDate,
                $endDate,
                $companyId,
                $roomId
            );

        foreach ($leases as $lease) {
            $this->setLeaseAttributions($lease);

            $this->setLeaseLogs($lease);
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
