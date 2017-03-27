<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;
use Sandbox\ApiBundle\Form\Lease\LeasePatchType;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
use Sandbox\ApiBundle\Traits\LeaseNotificationTrait;
use Sandbox\ApiBundle\Traits\LeaseTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations;

class AdminLeaseController extends SalesRestController
{
    use GenerateSerialNumberTrait;
    use HasAccessToEntityRepositoryTrait;
    use LeaseNotificationTrait;
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

        $this->throwNotFoundIfNull($lease, CustomErrorMessagesConstants::ERROR_LEASE_NOT_FOUND_MESSAGE);

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
     * @Annotations\QueryParam(
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="company id"
     * )
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
        $companyId = $paramFetcher->get('company');

        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW,
            AdminPermission::PERMISSION_PLATFORM_SALES,
            $companyId
        );

        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')->find($id);

        $this->throwNotFoundIfNull($lease, CustomErrorMessagesConstants::ERROR_LEASE_NOT_FOUND_MESSAGE);

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
     *    name="user",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by user id"
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

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

        $status = $paramFetcher->get('status');
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

        $userId = $paramFetcher->get('user');

        //get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
            )
        );

        $leases = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->findLeases(
                $myBuildingIds,
                $status,
                $keyword,
                $keywordSearch,
                $createRange,
                $createStart,
                $createEnd,
                $rentFilter,
                $startDate,
                $endDate,
                $salesCompanyId,
                $roomId,
                $limit,
                $offset,
                $userId
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->countLeasesAmount(
                $myBuildingIds,
                $status,
                $keyword,
                $keywordSearch,
                $createRange,
                $createStart,
                $createEnd,
                $rentFilter,
                $startDate,
                $endDate,
                $salesCompanyId,
                $roomId,
                $userId
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
     * Create a new lease.
     *
     * @param $request
     *
     * @Route("/leases")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postLeaseAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $payload = json_decode($request->getContent(), true);

        return $this->handleLeasePost($payload);
    }

    /**
     * Edit a lease.
     *
     * @param $request
     * @param $id
     *
     * @Route("/leases/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putLeaseAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $payload = json_decode($request->getContent(), true);

        return $this->handleLeasePut($payload, $id);
    }

    /**
     * Patch Lease Status.
     *
     * @param $request
     * @param $id
     *
     * @Route("/leases/{id}/status")
     * @Method({"PATCH"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function patchLeaseStatusAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $payload = json_decode($request->getContent(), true);

        $lease = $this->getLeaseRepo()->find($id);
        $this->throwNotFoundIfNull(
            $lease,
            CustomErrorMessagesConstants::ERROR_LEASE_NOT_FOUND_MESSAGE
        );

        $em = $this->getDoctrine()->getManager();

        $status = $lease->getStatus();
        $newStatus = $payload['status'];

        $leaseId = $lease->getId();
        $urlParam = 'ptype=leasesDetail&leasesId='.$leaseId;
        $contentArray = $this->generateLeaseContentArray($urlParam);

        $now = new \DateTime('now');
        switch ($newStatus) {
            case Lease::LEASE_STATUS_CONFIRMING:
                if ($status != Lease::LEASE_STATUS_DRAFTING) {
                    throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE);
                }

                $lease->setConfirmingDate($now);

                // send Jpush notification
                $this->generateJpushNotification(
                    [
                        $lease->getSupervisorId(),
                    ],
                    LeaseConstants::LEASE_CONFIRMING_MESSAGE,
                    null,
                    $contentArray
                );

                $action = Log::ACTION_CONFORMING;
                break;
            case Lease::LEASE_STATUS_PERFORMING:
                if ($status != Lease::LEASE_STATUS_CONFIRMED) {
                    throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE);
                }

                // send Jpush notification
                $this->generateJpushNotification(
                    [
                        $lease->getSupervisorId(),
                    ],
                    LeaseConstants::LEASE_PERFORMING_MESSAGE,
                    null,
                    $contentArray
                );

                $action = Log::ACTION_PERFORMING;
                break;
            case Lease::LEASE_STATUS_CLOSED:
                if (
                    $status != Lease::LEASE_STATUS_DRAFTING &&
                    $status != Lease::LEASE_STATUS_CONFIRMING &&
                    $status != Lease::LEASE_STATUS_CONFIRMED
                ) {
                    throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE);
                }

                // send Jpush notification
                $this->generateJpushNotification(
                    [
                        $lease->getSupervisorId(),
                    ],
                    LeaseConstants::LEASE_CLOSED_MESSAGE,
                    null,
                    $contentArray
                );

                if ($status == Lease::LEASE_STATUS_CONFIRMED) {
                    $this->setAccessActionToDelete($lease->getAccessNo());

                    $em->flush();

                    // remove door access
                    $this->callRepealRoomOrderCommand(
                        $lease->getBuilding()->getServer(),
                        $lease->getAccessNo()
                    );

                    // send notification to removed users
                    $removeUsers = $lease->getInvitedPeopleIds();
                    array_push($removeUsers, $lease->getSupervisorId());
                    if (!empty($removeUsers)) {
                        $this->sendXmppLeaseNotification(
                            $lease,
                            $removeUsers,
                            ProductOrder::ACTION_INVITE_REMOVE,
                            $lease->getSupervisorId(),
                            [],
                            ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                            ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
                        );
                    }
                }

                $unpaidBills = $this->getLeaseBillRepo()->findBy(array(
                    'lease' => $lease,
                    'status' => LeaseBill::STATUS_UNPAID,
                ));
                foreach ($unpaidBills as $unpaidBill) {
                    $unpaidBill->setStatus(LeaseBill::STATUS_CANCELLED);
                }

                $action = Log::ACTION_CLOSE;
                break;
            case Lease::LEASE_STATUS_END:
                if (
                    $status != Lease::LEASE_STATUS_MATURED &&
                    $status != Lease::LEASE_STATUS_PERFORMING &&
                    $status != Lease::LEASE_STATUS_RECONFIRMING
                ) {
                    throw new BadRequestHttpException(
                        CustomErrorMessagesConstants::ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE
                    );
                }

                $unpaidBills = $this->getLeaseBillRepo()->findBy(array(
                    'lease' => $lease,
                    'status' => LeaseBill::STATUS_UNPAID,
                ));

                if ($now < $lease->getEndDate()) {
                    foreach ($unpaidBills as $unpaidBill) {
                        $unpaidBill->setStatus(LeaseBill::STATUS_CANCELLED);
                    }

                    $this->setAccessActionToDelete($lease->getAccessNo());

                    $em->flush();

                    // remove door access
                    $this->callRepealRoomOrderCommand(
                        $lease->getBuilding()->getServer(),
                        $lease->getAccessNo()
                    );

                    // send notification to removed users
                    $removeUsers = $lease->getInvitedPeopleIds();
                    array_push($removeUsers, $lease->getSupervisorId());
                    if (!empty($removeUsers)) {
                        $this->sendXmppLeaseNotification(
                            $lease,
                            $removeUsers,
                            ProductOrder::ACTION_INVITE_REMOVE,
                            $lease->getSupervisorId(),
                            [],
                            ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                            ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
                        );
                    }

                    $newStatus = Lease::LEASE_STATUS_TERMINATED;
                    $action = Log::ACTION_TERMINATE;

                    // send Jpush notification
                    $this->generateJpushNotification(
                        [
                            $lease->getSupervisorId(),
                        ],
                        LeaseConstants::LEASE_TERMINATED_MESSAGE,
                        null,
                        $contentArray
                    );
                } else {
                    // send Jpush notification
                    $this->generateJpushNotification(
                        [
                            $lease->getSupervisorId(),
                        ],
                        LeaseConstants::LEASE_ENDED_MESSAGE,
                        null,
                        $contentArray
                    );

                    $action = Log::ACTION_END;
                }

                break;
            default:
                throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE);
        }

        $lease->setStatus($newStatus);

        $em->flush();

        if ($payload['status'] == Lease::LEASE_STATUS_CONFIRMING) {
            $this->setAppointmentStatusToAccepted($lease);
        }

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => $action,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));

        return new View();
    }

    /**
     * Delete Draft of Lease.
     *
     * @Route("/leases/{id}")
     * @Method({"DELETE"})
     *
     * @param int $id
     *
     * @throws \Exception
     */
    public function deleteLeaseAction(
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        $lease = $this->getLeaseRepo()->findOneBy(
                array(
                    'id' => $id,
                    'status' => Lease::LEASE_STATUS_DRAFTING,
                )
            );

        $this->throwNotFoundIfNull($lease, CustomErrorMessagesConstants::ERROR_LEASE_NOT_FOUND_MESSAGE);

        $em->remove($lease);
        $em->flush();

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_DELETE,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));
    }

    /**
     * Patch Lease Deposit Note.
     *
     * @param $request
     * @param $id
     *
     * @Route("/leases/{id}/deposit")
     * @Method({"PATCH"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function patchLeaseDepositAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $lease = $this->getDoctrine()->getRepository("SandboxApiBundle:Lease\Lease")->find($id);
        $this->throwNotFoundIfNull($lease, CustomErrorMessagesConstants::ERROR_LEASE_NOT_FOUND_MESSAGE);

        $leaseJson = $this->container->get('serializer')->serialize($lease, 'json');
        $patch = new Patch($leaseJson, $request->getContent());
        $leaseJson = $patch->apply();
        $form = $this->createForm(new LeasePatchType(), $lease);
        $form->submit(json_decode($leaseJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));

        return new View();
    }

    /**
     * @param $payload
     *
     * @return View
     */
    private function handleLeasePost(
        $payload
    ) {
        if (
            $payload['status'] !== Lease::LEASE_STATUS_CONFIRMING &&
            $payload['status'] !== Lease::LEASE_STATUS_DRAFTING
        ) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE);
        }

        $this->checkLeaseAttributesIsValid($payload);

        $em = $this->getDoctrine()->getManager();
        $lease = new Lease();

        if (!empty($payload['drawee'])) {
            $drawee = $this->getUserRepo()->find($payload['drawee']);
            $this->throwNotFoundIfNull($drawee, CustomErrorMessagesConstants::ERROR_DRAWEE_NOT_FOUND_MESSAGE);
            $lease->setDrawee($drawee);
        }

        if (!empty($payload['supervisor'])) {
            $supervisor = $this->getUserRepo()->find($payload['supervisor']);
            $this->throwNotFoundIfNull($supervisor, CustomErrorMessagesConstants::ERROR_SUPERVISOR_NOT_FOUND_MESSAGE);
            $lease->setSupervisor($supervisor);
        }

        $product = $this->getProductRepo()->find($payload['product']);
        $this->throwNotFoundIfNull($product, CustomErrorMessagesConstants::ERROR_PRODUCT_NOT_FOUND_MESSAGE);
        $lease->setProduct($product);

        $startDate = new \DateTime($payload['start_date']);
        $endDate = new \DateTime($payload['end_date']);
        $endDate->setTime(23, 59, 59);

        $lease->setDeposit($payload['deposit']);
        $lease->setEndDate($endDate);
        $lease->setLesseeAddress($payload['lessee_address']);
        $lease->setLesseeContact($payload['lessee_contact']);
        $lease->setLesseeEmail($payload['lessee_email']);
        $lease->setLesseeName($payload['lessee_name']);
        $lease->setLesseePhone($payload['lessee_phone']);
        $lease->setLessorAddress($payload['lessor_address']);
        $lease->setLessorName($payload['lessor_name']);
        $lease->setLessorPhone($payload['lessor_phone']);
        $lease->setLessorEmail($payload['lessor_email']);
        $lease->setLessorContact($payload['lessor_contact']);
        $lease->setMonthlyRent($payload['monthly_rent']);
        $lease->setPurpose($payload['purpose']);
        $lease->setStatus($payload['status']);
        $lease->setStartDate($startDate);
        $lease->setSerialNumber($this->generateLeaseSerialNumber());
        $lease->setTotalRent($payload['total_rent']);
        $lease->setOtherExpenses($payload['other_expenses']);
        $lease->setSupplementaryTerms($payload['supplementary_terms']);

        if ($payload['is_auto']) {
            $lease->setIsAuto($payload['is_auto']);
        }

        if ($payload['plan_day']) {
            $lease->setPlanDay($payload['plan_day']);
        }

        // If lease create from product appointment
        if (
            isset($payload['product_appointment'])
        ) {
            $productAppointment = $this->getProductAppointmentRepo()
                ->find($payload['product_appointment']);

            $this->throwNotFoundIfNull($productAppointment, CustomErrorMessagesConstants::ERROR_APPOINTMENT_NOT_FOUND_MESSAGE);

            $lease->setProductAppointment($productAppointment);
        }

        if ($payload['status'] == Lease::LEASE_STATUS_CONFIRMING) {
            $lease->setConfirmingDate(new \DateTime('now'));
        }

        $this->handleLeaseRentTypesPost($payload['lease_rent_types'], $lease);
        $this->handleLeaseBillPost($payload, $lease, $em);

        $em->persist($lease);
        $em->flush();

        $response = array(
            'id' => $lease->getId(),
        );

        $leaseId = $lease->getId();
        $urlParam = 'ptype=leasesDetail&leasesId='.$leaseId;
        $contentArray = $this->generateLeaseContentArray($urlParam);
        // send Jpush notification
        if ($payload['status'] == Lease::LEASE_STATUS_CONFIRMING) {
            $this->generateJpushNotification(
                [
                    $lease->getSupervisorId(),
                ],
                LeaseConstants::LEASE_CONFIRMING_MESSAGE,
                null,
                $contentArray
            );

            $this->setAppointmentStatusToAccepted($lease);
        }

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_CREATE,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));

        return new View($response, 201);
    }

    private function checkLeaseAttributesIsValid($payload)
    {
        if (
            !key_exists('lessee_address', $payload) ||
            !key_exists('lessee_contact', $payload) ||
            !key_exists('lessee_email', $payload) ||
            !key_exists('lessee_name', $payload) ||
            !key_exists('lessee_phone', $payload) ||
            !key_exists('lessor_address', $payload) ||
            !key_exists('lessor_name', $payload) ||
            !key_exists('lessor_phone', $payload) ||
            !key_exists('lessor_email', $payload) ||
            !key_exists('lessor_contact', $payload) ||
            !key_exists('deposit', $payload) ||
            !key_exists('monthly_rent', $payload) ||
            !key_exists('total_rent', $payload) ||
            !key_exists('status', $payload) ||
            !key_exists('purpose', $payload) ||
            !key_exists('start_date', $payload) ||
            !key_exists('end_date', $payload) ||
            !key_exists('drawee', $payload) ||
            !key_exists('supervisor', $payload) ||
            !key_exists('product', $payload) ||
            !key_exists('product', $payload) ||
            !key_exists('product', $payload) ||
            !key_exists('lease_rent_types', $payload) ||
            !key_exists('bills', $payload) ||
            gettype($payload['lease_rent_types']) != 'array'
        ) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE);
        }

        if (
            $payload['status'] !== Lease::LEASE_STATUS_DRAFTING
        ) {
            if (
                (gettype($payload['deposit']) != 'double' && gettype($payload['deposit']) != 'integer') ||
                (gettype($payload['monthly_rent']) != 'double' && gettype($payload['deposit']) != 'integer') ||
                (gettype($payload['total_rent']) != 'double' && gettype($payload['deposit']) != 'integer') ||
                is_null($payload['deposit']) ||
                is_null($payload['monthly_rent']) ||
                is_null($payload['total_rent']) ||
                empty($payload['lease_rent_types']) ||
                !preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $payload['start_date']) ||
                !preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $payload['end_date']) ||
                !filter_var($payload['lessee_address'], FILTER_DEFAULT) ||
                !filter_var($payload['lessee_contact'], FILTER_DEFAULT) ||
                !filter_var($payload['lessee_name'], FILTER_DEFAULT) ||
                !filter_var($payload['lessee_phone'], FILTER_DEFAULT) ||
                !filter_var($payload['lessee_email'], FILTER_VALIDATE_EMAIL) ||
                !filter_var($payload['lessor_email'], FILTER_VALIDATE_EMAIL) ||
                !filter_var($payload['lessor_address'], FILTER_DEFAULT) ||
                !filter_var($payload['lessor_name'], FILTER_DEFAULT) ||
                !filter_var($payload['lessor_phone'], FILTER_DEFAULT) ||
                !filter_var($payload['lessor_contact'], FILTER_DEFAULT) ||
                !filter_var($payload['purpose'], FILTER_DEFAULT) ||
                !filter_var($payload['status'], FILTER_DEFAULT) ||
                !filter_var($payload['drawee'], FILTER_VALIDATE_INT) ||
                !filter_var($payload['supervisor'], FILTER_VALIDATE_INT) ||
                !filter_var($payload['product'], FILTER_VALIDATE_INT)
            ) {
                throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE);
            }
        }

        return;
    }

    private function handleLeaseRentTypesPost(
        $leaseRentTypeIds,
        $lease
    ) {
        foreach ($leaseRentTypeIds as $leaseRentTypeId) {
            $leaseRentType = $this->getLeaseRentTypesRepo()->find($leaseRentTypeId);
            if (is_null($leaseRentType)) {
                throw new NotFoundHttpException(CustomErrorMessagesConstants::ERROR_LEASE_RENT_TYPE_NOT_FOUND_MESSAGE);
            }
            $lease->addLeaseRentTypes($leaseRentType);
        }
    }

    private function handleLeaseBillPost(
        $payload,
        $lease,
        $em
    ) {
        if (!empty($payload['bills']['add'])) {
            $this->addBills($payload, $em, $lease);
        }
    }

    /**
     * @param $payload
     * @param $leaseId
     *
     * @return View
     */
    private function handleLeasePut($payload, $leaseId)
    {
        $this->checkLeaseAttributesIsValid($payload);

        $em = $this->getDoctrine()->getManager();
        $lease = $this->getLeaseRepo()->find($leaseId);
        $this->throwNotFoundIfNull($lease, CustomErrorMessagesConstants::ERROR_LEASE_NOT_FOUND_MESSAGE);

        if (!empty($payload['drawee'])) {
            $drawee = $this->getUserRepo()->find($payload['drawee']);
            $this->throwNotFoundIfNull($drawee, CustomErrorMessagesConstants::ERROR_DRAWEE_NOT_FOUND_MESSAGE);
            $lease->setDrawee($drawee);
        }

        if (!empty($payload['supervisor'])) {
            $previousSupervisorId = $lease->getSupervisorId();
            $supervisor = $this->getUserRepo()->find($payload['supervisor']);
            $this->throwNotFoundIfNull($supervisor, CustomErrorMessagesConstants::ERROR_SUPERVISOR_NOT_FOUND_MESSAGE);

            if ($previousSupervisorId !== $payload['supervisor']) {
                if (
                    $payload['status'] == Lease::LEASE_STATUS_RECONFIRMING
                ) {
                    $base = $lease->getBuilding()->getServer();
                    $roomDoors = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Room\RoomDoors')
                        ->findBy(['room' => $lease->getRoom()]);

                    if (!is_null($base) && !empty($base) && !empty($roomDoors)) {
                        $this->setAccessActionToDelete($lease->getAccessNo());

                        $em->flush();

                        // remove the previous supervisor from door access
                        $this->callRemoveFromOrderCommand(
                            $lease->getBuilding()->getServer(),
                            $lease->getAccessNo(),
                            [$previousSupervisorId]
                        );

                        // send notification to removed users
                        $this->sendXmppLeaseNotification(
                            $lease,
                            [$previousSupervisorId],
                            ProductOrder::ACTION_INVITE_REMOVE,
                            $payload['supervisor'],
                            [],
                            ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                            ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
                        );

                        // add the new supervisor to door access
                        $this->storeDoorAccess(
                            $em,
                            $lease->getAccessNo(),
                            $payload['supervisor'],
                            $lease->getBuildingId(),
                            $lease->getRoomId(),
                            $lease->getStartDate(),
                            $lease->getEndDate()
                        );

                        $em->flush();

                        $userArray = $this->getUserArrayIfAuthed(
                            $base,
                            $payload['supervisor'],
                            []
                        );

                        // set room access
                        if (!empty($userArray)) {
                            $this->callSetRoomOrderCommand(
                                $base,
                                $userArray,
                                $roomDoors,
                                $lease->getAccessNo(),
                                $lease->getStartDate(),
                                $lease->getEndDate()
                            );
                        }

                        // send notification to the new supervisor
                        $this->sendXmppLeaseNotification(
                            $lease,
                            [$payload['supervisor']],
                            ProductOrder::ACTION_INVITE_ADD,
                            $lease->getSupervisorId(),
                            [],
                            ProductOrderMessage::APPOINT_MESSAGE_PART1,
                            ProductOrderMessage::APPOINT_MESSAGE_PART2
                        );
                    }
                }
            }

            $lease->setSupervisor($supervisor);
        }

        $product = $this->getProductRepo()->find($payload['product']);
        $this->throwNotFoundIfNull($product, CustomErrorMessagesConstants::ERROR_PRODUCT_NOT_FOUND_MESSAGE);
        $lease->setProduct($product);

        $lease->setDeposit($payload['deposit']);
        $lease->setLesseeAddress($payload['lessee_address']);
        $lease->setLesseeContact($payload['lessee_contact']);
        $lease->setLesseeEmail($payload['lessee_email']);
        $lease->setLesseeName($payload['lessee_name']);
        $lease->setLesseePhone($payload['lessee_phone']);
        $lease->setLessorAddress($payload['lessor_address']);
        $lease->setLessorName($payload['lessor_name']);
        $lease->setLessorPhone($payload['lessor_phone']);
        $lease->setLessorEmail($payload['lessor_email']);
        $lease->setLessorContact($payload['lessor_contact']);
        $lease->setMonthlyRent($payload['monthly_rent']);
        $lease->setPurpose($payload['purpose']);
        $lease->setTotalRent($payload['total_rent']);
        $lease->setModificationDate(new \DateTime('now'));
        $lease->setOtherExpenses($payload['other_expenses']);
        $lease->setSupplementaryTerms($payload['supplementary_terms']);

        if ($payload['is_auto']) {
            $lease->setIsAuto($payload['is_auto']);
        }

        if ($payload['plan_day']) {
            $lease->setPlanDay($payload['plan_day']);
        }

        // If lease created by product appointment
        if (
            isset($payload['product_appointment'])
        ) {
            $productAppointment = $this->getProductAppointmentRepo()
                ->find($payload['product_appointment']);
            $this->throwNotFoundIfNull($productAppointment, CustomErrorMessagesConstants::ERROR_APPOINTMENT_NOT_FOUND_MESSAGE);

            $lease->setProductAppointment($productAppointment);
        }

        $urlParam = 'ptype=leasesDetail&leasesId='.$leaseId;
        $contentArray = $this->generateLeaseContentArray($urlParam);
        switch ($lease->getStatus()) {
            case Lease::LEASE_STATUS_DRAFTING:
                if (
                    $payload['status'] != Lease::LEASE_STATUS_CONFIRMING &&
                    $payload['status'] != Lease::LEASE_STATUS_DRAFTING
                ) {
                    throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE);
                }

                $lease->setStatus($payload['status']);

                if ($payload['status'] == Lease::LEASE_STATUS_CONFIRMING) {
                    $lease->setConfirmingDate(new \DateTime('now'));

                    // send Jpush notification
                    $this->generateJpushNotification(
                        [
                            $lease->getSupervisorId(),
                        ],
                        LeaseConstants::LEASE_CONFIRMING_MESSAGE,
                        null,
                        $contentArray
                    );
                }

                break;
            case Lease::LEASE_STATUS_CONFIRMING:
                if (
                    $payload['status'] != Lease::LEASE_STATUS_CONFIRMING
                ) {
                    throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE);
                }

                // send Jpush notification
                $this->generateJpushNotification(
                    [
                        $lease->getSupervisorId(),
                    ],
                    LeaseConstants::LEASE_RECONFIRMING_MESSAGE,
                    null,
                    $contentArray
                );

                break;
            case Lease::LEASE_STATUS_CONFIRMED:
                if ($payload['status'] != Lease::LEASE_STATUS_RECONFIRMING) {
                    throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE);
                }
                $lease->setStatus(Lease::LEASE_STATUS_RECONFIRMING);

                // send Jpush notification
                $this->generateJpushNotification(
                    [
                        $lease->getSupervisorId(),
                    ],
                    LeaseConstants::LEASE_RECONFIRMING_MESSAGE,
                    null,
                    $contentArray
                );

                break;
            case Lease::LEASE_STATUS_RECONFIRMING:
                if ($payload['status'] != Lease::LEASE_STATUS_RECONFIRMING) {
                    throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE);
                }
                $lease->setStatus(Lease::LEASE_STATUS_RECONFIRMING);

                // send Jpush notification
                $this->generateJpushNotification(
                    [
                        $lease->getSupervisorId(),
                    ],
                    LeaseConstants::LEASE_RECONFIRMING_MESSAGE,
                    null,
                    $contentArray
                );

                break;
            case Lease::LEASE_STATUS_PERFORMING:
                if ($payload['status'] != Lease::LEASE_STATUS_RECONFIRMING) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                }
                $lease->setStatus(Lease::LEASE_STATUS_RECONFIRMING);

                // send Jpush notification
                $this->generateJpushNotification(
                    [
                        $lease->getSupervisorId(),
                    ],
                    LeaseConstants::LEASE_RECONFIRMING_MESSAGE,
                    null,
                    $contentArray
                );

                break;
            default:
                throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE);
        }

        $this->handleLeaseRentTypesPut($payload['lease_rent_types'], $lease);
        $this->handleLeaseBillPut($payload, $lease, $em);

        $startDate = new \DateTime($payload['start_date']);
        $endDate = new \DateTime($payload['end_date']);
        $endDate->setTime(23, 59, 59);

        if (
            $startDate != $lease->getStartDate() ||
            $endDate != $lease->getEndDate()
        ) {
            $base = $lease->getBuilding()->getServer();
            $roomDoors = $lease->getRoom()->getDoorControl();

            if (!is_null($base) && !empty($base) && !empty($roomDoors)) {
                $this->setAccessActionToDelete($lease->getAccessNo());

                $em->flush();

                $this->callRepealRoomOrderCommand(
                    $lease->getBuilding()->getServer(),
                    $lease->getAccessNo()
                );

                $lease->setAccessNo($this->generateAccessNumber());
                $lease->setStartDate($startDate);
                $lease->setEndDate($endDate);

                $users = $lease->getInvitedPeopleIds();
                array_push($users, $lease->getSupervisorId());
                $this->addPeople(
                    $users,
                    $lease,
                    $lease->getBuilding()->getServer()
                );
            }
        }

        $em->flush();

        if ($payload['status'] == Lease::LEASE_STATUS_CONFIRMING) {
            $this->setAppointmentStatusToAccepted($lease);
        }

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));

        return new View();
    }

    private function handleLeaseRentTypesPut(
        $leaseRentTypeIds,
        $lease
    ) {
        $leaseRentTypes = $lease->getLeaseRentTypes();
        foreach ($leaseRentTypes as $leaseRentType) {
            $lease->removeLeaseRentTypes($leaseRentType);
        }

        foreach ($leaseRentTypeIds as $leaseRentTypeId) {
            $leaseRentType = $this->getLeaseRentTypesRepo()->find($leaseRentTypeId);
            if (is_null($leaseRentType)) {
                throw new NotFoundHttpException(CustomErrorMessagesConstants::ERROR_LEASE_RENT_TYPE_NOT_FOUND_MESSAGE);
            }
            $lease->addLeaseRentTypes($leaseRentType);
        }
    }

    private function handleLeaseBillPut(
        $payload,
        $lease,
        $em
    ) {
        $payloadBills = $payload['bills'];

        if (!empty($payloadBills['add'])) {
            $this->addBills($payload, $em, $lease);
        }

        if (!empty($payloadBills['edit'])) {
            $this->editBills($payload);
        }

        $currentBillsAmount =
            count($this->getLeaseBillRepo()
                ->findBy(array(
                    'lease' => $lease,
                )
            ));
        $removeAmount = 0;

        if (!empty($payloadBills['remove'])) {
            $removeAmount = $this->removeBills($payloadBills['remove'], $lease, $em);
        }

        if ($payload['status'] != Lease::LEASE_STATUS_DRAFTING) {
            if ($currentBillsAmount == $removeAmount) {
                if (count($payloadBills['add']) > 0) {
                    return;
                }

//                throw new BadRequestHttpException(
//                    CustomErrorMessagesConstants::ERROR_LEASE_KEEP_AT_LEAST_ONE_BILL_MESSAGE
//                );
            }
        }
    }

    private function addBills(
        $payload,
        $em,
        $lease
    ) {
        $addBills = $payload['bills']['add'];
        foreach ($addBills as $addBill) {
            if ($payload['status'] !== Lease::LEASE_STATUS_DRAFTING) {
                $this->checkLeaseBillAttributesIsValid($addBill);
            }

            $bill = new LeaseBill();

            if (!empty($addBill['start_date']) || !is_null($addBill['start_date'])) {
                $startDate = new \DateTime($addBill['start_date']);
                $bill->setStartDate($startDate);
            }

            if (!empty($addBill['end_date']) || !is_null($addBill['end_date'])) {
                $endDate = new \DateTime($addBill['end_date']);
                $bill->setEndDate($endDate);
            }

            $bill->setName($addBill['name']);
            $bill->setAmount($addBill['amount']);
            $bill->setDescription($addBill['description']);
            $bill->setSerialNumber($this->generateSerialNumber(LeaseBill::LEASE_BILL_LETTER_HEAD));
            $bill->setType(LeaseBill::TYPE_LEASE);
            $bill->setStatus(LeaseBill::STATUS_PENDING);
            $bill->setLease($lease);

            $em->persist($bill);
        }
    }

    private function editBills(
        $payload
    ) {
        $editBills = $payload['bills']['edit'];
        foreach ($editBills as $editBill) {
            if (empty($editBill['id'])) {
                throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE);
            }

            if ($payload['status'] !== Lease::LEASE_STATUS_DRAFTING) {
                $this->checkLeaseBillAttributesIsValid($editBill);
            }

            $bill = $this->getLeaseBillRepo()->find($editBill['id']);
            $this->throwNotFoundIfNull($bill, CustomErrorMessagesConstants::ERROR_BILL_NOT_FOUND_MESSAGE);

            // only pending bills could be edited
            if ($bill->getStatus() !== LeaseBill::STATUS_PENDING) {
                throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_BILL_STATUS_NOT_CORRECT_MESSAGE);
            }

            $startDate = new \DateTime($editBill['start_date']);
            $endDate = new \DateTime($editBill['end_date']);

            $bill->setName($editBill['name']);
            $bill->setAmount($editBill['amount']);
            $bill->setDescription($editBill['description']);
            $bill->setStartDate($startDate);
            $bill->setEndDate($endDate);
            $bill->setType(LeaseBill::TYPE_LEASE);
            $bill->setStatus(LeaseBill::STATUS_PENDING);
        }
    }

    /**
     * @param $removedBills
     * @param $lease
     * @param $em
     *
     * @return int
     */
    private function removeBills(
        $removedBills,
        $lease,
        $em
    ) {
        $removeAmount = 0;

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBillsByIds(
                $removedBills,
                LeaseBill::STATUS_PENDING,
                LeaseBill::TYPE_LEASE,
                $lease
            );

        foreach ($bills as $bill) {
            $em->remove($bill);
            $removeAmount += 1;
        }

        return $removeAmount;
    }

    private function checkLeaseBillAttributesIsValid($billAttributes)
    {
        if (
            !key_exists('name', $billAttributes) ||
            !key_exists('amount', $billAttributes) ||
            !key_exists('description', $billAttributes) ||
            !key_exists('start_date', $billAttributes) ||
            !key_exists('end_date', $billAttributes) ||
            (gettype($billAttributes['amount']) != 'double' && gettype($billAttributes['amount']) != 'integer') ||
            is_null($billAttributes['amount']) ||
            !filter_var($billAttributes['name'], FILTER_DEFAULT) ||
            !filter_var($billAttributes['description'], FILTER_DEFAULT) ||
            !preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $billAttributes['start_date']) ||
            !preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $billAttributes['end_date'])
        ) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE);
        }
    }

    /**
     * @param $opLevel
     */
    private function checkAdminLeasePermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE],
            ],
            $opLevel
        );
    }

    /**
     * @param $users
     * @param $lease
     * @param $base
     *
     * @return array|mixed
     */
    private function addPeople(
        $users,
        $lease,
        $base
    ) {
        $em = $this->getDoctrine()->getManager();
        $roomDoors = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomDoors')
            ->findBy(['room' => $lease->getRoom()]);

        if (is_null($base) || empty($base) || empty($roomDoors)) {
            return;
        }

        $userArray = [];
        foreach ($users as $userId) {
            $this->storeDoorAccess(
                $em,
                $lease->getAccessNo(),
                $userId,
                $lease->getBuildingId(),
                $lease->getRoomId(),
                $lease->getStartDate(),
                $lease->getEndDate()
            );

            $userArray = $this->getUserArrayIfAuthed(
                $base,
                $userId,
                $userArray
            );
        }

        $em->flush();

        // set room access
        if (!empty($userArray)) {
            $this->callSetRoomOrderCommand(
                $base,
                $userArray,
                $roomDoors,
                $lease->getAccessNo(),
                $lease->getStartDate(),
                $lease->getEndDate()
            );
        }

        return;
    }

    private function setAppointmentStatusToAccepted(
        $lease
    ) {
        $em = $this->getDoctrine()->getManager();

        // set appointment status to accepted
        $appointment = $lease->getProductAppointment();
        if (!is_null($appointment)) {
            if ($appointment->getStatus() == ProductAppointment::STATUS_PENDING) {
                $appointment->setStatus(ProductAppointment::STATUS_ACCEPTED);

                $em->flush();
                $this->generateAdminLogs(array(
                    'logModule' => Log::MODULE_PRODUCT_APPOINTMENT,
                    'logAction' => Log::ACTION_AGREE,
                    'logObjectKey' => Log::OBJECT_PRODUCT_APPOINTMENT,
                    'logObjectId' => $appointment->getId(),
                ));

                $urlParam = 'ptype=rentDetail&rentId='.$appointment->getId();
                $contentArray = $this->generateLeaseContentArray($urlParam, 'longrent');
                // send Jpush notification
                $this->generateJpushNotification(
                    [
                        $appointment->getUserId(),
                    ],
                    LeaseConstants::APPLICATION_APPROVED_MESSAGE,
                    null,
                    $contentArray
                );
            }
        }

        $product = $lease->getProduct();
        if (!is_null($product)) {
            $this->generateAdminLogs(array(
                'logModule' => Log::MODULE_PRODUCT,
                'logAction' => Log::ACTION_EDIT,
                'logObjectKey' => Log::OBJECT_PRODUCT,
                'logObjectId' => $product->getId(),
            ));
        }
    }
}
