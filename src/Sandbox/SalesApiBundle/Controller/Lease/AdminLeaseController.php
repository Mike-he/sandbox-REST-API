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
use Sandbox\ApiBundle\Entity\Admin\AdminRemark;
use Sandbox\ApiBundle\Entity\Admin\AdminStatusLog;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;
use Sandbox\ApiBundle\Entity\Lease\LeaseOffer;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\User\EnterpriseCustomerContacts;
use Sandbox\ApiBundle\Entity\User\UserGroupHasUser;
use Sandbox\ApiBundle\Form\Lease\LeasePatchType;
use Sandbox\ApiBundle\Form\Lease\LeaseRequiredType;
use Sandbox\ApiBundle\Form\Lease\LeaseType;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\ApiBundle\Traits\LeaseNotificationTrait;
use Sandbox\ApiBundle\Traits\LeaseTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations;

class AdminLeaseController extends SalesRestController
{
    use GenerateSerialNumberTrait;
    use LeaseNotificationTrait;
    use LeaseTrait;

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="building",
     *     array=false,
     *     default=null,
     *     nullable=false,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="search",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Route("/leases/valid_products")
     * @Method({"GET"})
     *
     * @return View
     */
    public function searchLeaseProductsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $buildingId = $paramFetcher->get('building');
        $search = $paramFetcher->get('search');

        $products = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->searchLeasesProducts($buildingId, $search);

        foreach ($products as &$product) {
            $attachment = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
                ->findAttachmentsByRoom($product['room_id'], 1);

            if (!empty($attachment)) {
                $product['content'] = $attachment[0]['content'];
                $product['preview'] = $attachment[0]['preview'];
            }
        }

        return new View($products);
    }

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
     * Get List of Lease.
     *
     * @Route("/leases")
     * @Method({"GET"})
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default=null,
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
     *    name="customer",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by user id"
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
     *    name="sort_column",
     *    default=null,
     *    nullable=true,
     *    description="sort column"
     * )
     *
     * @Annotations\QueryParam(
     *    name="direction",
     *    default=null,
     *    nullable=true,
     *    description="sort direction"
     * )
     *
     * @return View
     */
    public function getLeasesAction(
        ParamFetcherInterface $paramFetcher,
        Request $request
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_CUSTOMER],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

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

        $customerId = $paramFetcher->get('customer');
        $buildingId = $paramFetcher->get('building');

        //sort
        $sortColumn = $paramFetcher->get('sort_column');
        $direction = $paramFetcher->get('direction');

        //get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
            )
        );

        $myBuildingIds = $buildingId ? array((int) $buildingId) : $myBuildingIds;

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
                $customerId,
                $sortColumn,
                $direction
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
                $customerId
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

        $lease = new Lease();
        $form = $this->createForm(new LeaseType(), $lease);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $bills = $form['bills']->getData();
        $leaseRentTypeIds = $form['lease_rent_types']->getData();

        return $this->handleLeasePost(
            $lease,
            $bills,
            $leaseRentTypeIds
        );
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

        $lease = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\Lease')->find($id);
        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $oldStatus = $lease->getStatus();
        $oldLesseeCustomer = $lease->getLesseeCustomer();
        $oldStartDate = $lease->getStartDate();
        $oldEndDate = $lease->getEndDate();

        $payload = json_decode($request->getContent(), true);
        if (!isset($payload['status'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        if (Lease::LEASE_STATUS_DRAFTING == $payload['status']) {
            $form = $this->createForm(
                new LeaseType(),
                $lease,
                array('method' => 'PUT')
            );
        } else {
            $form = $this->createForm(
                new LeaseRequiredType(),
                $lease,
                array('method' => 'PUT')
            );
        }

        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $bills = $form['bills']->getData();
        $leaseRentTypeIds = $form['lease_rent_types']->getData();

        return $this->handleLeasePut(
            $lease,
            $bills,
            $leaseRentTypeIds,
            $oldStatus,
            $oldLesseeCustomer,
            $oldStartDate,
            $oldEndDate
        );
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

        $em = $this->getDoctrine()->getManager();

        $payload = json_decode($request->getContent(), true);

        $lease = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\Lease')->find($id);
        $this->throwNotFoundIfNull(
            $lease,
            CustomErrorMessagesConstants::ERROR_LEASE_NOT_FOUND_MESSAGE
        );

        $status = $lease->getStatus();
        $newStatus = $payload['status'];

        $leaseId = $lease->getId();
        $urlParam = 'ptype=leasesDetail&leasesId='.$leaseId;
        $contentArray = $this->generateLeaseContentArray($urlParam);

        $userId = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->getUserIdByCustomerId($lease->getLesseeCustomer());

        $now = new \DateTime('now');
        switch ($newStatus) {
            case Lease::LEASE_STATUS_PERFORMING:
                if (Lease::LEASE_STATUS_DRAFTING != $status) {
                    return $this->customErrorView(
                        400,
                        CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_CODE,
                        CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_MESSAGE
                    );
                }
                $lease->setConfirmingDate($now);

                if ($userId) {
                    $this->addDoorAccess($lease, $userId);

                    // send Jpush notification
                    $this->generateJpushNotification(
                        [
                            $userId,
                        ],
                        LeaseConstants::LEASE_PERFORMING_MESSAGE,
                        null,
                        $contentArray
                    );
                }

                $action = Log::ACTION_PERFORMING;

                $logMessage = '生效合同';
                break;
            case Lease::LEASE_STATUS_CLOSED:
                if (Lease::LEASE_STATUS_DRAFTING != $status) {
                    return $this->customErrorView(
                        400,
                        CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_CODE,
                        CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_MESSAGE
                    );
                }

                if ($userId) {
                    // send Jpush notification
                    $this->generateJpushNotification(
                        [
                            $userId,
                        ],
                        LeaseConstants::LEASE_CLOSED_MESSAGE,
                        null,
                        $contentArray
                    );
                }

                $action = Log::ACTION_CLOSE;

                $logMessage = '关闭合同';
                break;
            case Lease::LEASE_STATUS_END:
                if (
                    Lease::LEASE_STATUS_MATURED != $status &&
                    Lease::LEASE_STATUS_PERFORMING != $status
                ) {
                    return $this->customErrorView(
                        400,
                        CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_CODE,
                        CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_MESSAGE
                    );
                }

                $unpaidBills = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                    ->findBy(array(
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

                        if ($userId) {
                            // send notification to removed users
                            $removeUsers = $lease->getInvitedPeopleIds();
                            array_push($removeUsers, $userId);
                            if (!empty($removeUsers)) {
                                $this->sendXmppLeaseNotification(
                                    $lease,
                                    $removeUsers,
                                    ProductOrder::ACTION_INVITE_REMOVE,
                                    $userId,
                                    [],
                                    ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                                    ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
                                );
                            }

                            // send Jpush notification
                            $this->generateJpushNotification(
                                [
                                    $userId,
                                ],
                                LeaseConstants::LEASE_TERMINATED_MESSAGE,
                                null,
                                $contentArray
                            );

                            // set user group end date to now
                            $this->removeUserFromUserGroup(
                                $lease->getBuildingId(),
                                $removeUsers,
                                $lease->getStartDate(),
                                $lease->getSerialNumber(),
                                UserGroupHasUser::TYPE_LEASE
                            );
                        }

                        $newStatus = Lease::LEASE_STATUS_TERMINATED;
                        $action = Log::ACTION_TERMINATE;
                        $logMessage = '终止合同';
                    } else {
                        // send Jpush notification
                        $this->generateJpushNotification(
                            [
                                $userId,
                            ],
                            LeaseConstants::LEASE_ENDED_MESSAGE,
                            null,
                            $contentArray
                        );

                        $action = Log::ACTION_END;
                        $logMessage = '结束合同';
                    }
                break;
            default:
                return $this->customErrorView(
                    400,
                    CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_CODE,
                    CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_MESSAGE
                );
        }

        $lease->setStatus($newStatus);

        $em->flush();

        $this->get('sandbox_api.admin_status_log')->autoLog(
            $this->getAdminId(),
            $newStatus,
            $logMessage,
            AdminStatusLog::OBJECT_LEASE,
            $lease->getId(),
            AdminStatusLog::TYPE_SALES_ADMIN,
            $lease->getCompanyId()
        );

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

        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->findOneBy(
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
     * @param Lease $lease
     * @param $bills
     * @param $leaseRentTypeIds
     *
     * @return View
     */
    private function handleLeasePost(
        $lease,
        $bills,
        $leaseRentTypeIds
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $platform = $adminPlatform['platform'];

        $em = $this->getDoctrine()->getManager();

        $lease = $this->checkLeaseData($lease);

        $this->handleLeaseRentTypesPost($leaseRentTypeIds, $lease);

        if (!empty($bills['add'])) {
            $this->addBills($bills['add'], $lease);
        }

        $lease->setSerialNumber($this->generateLeaseSerialNumber());
        $lease->setCompanyId($salesCompanyId);

        if (Lease::LEASE_STATUS_PERFORMING == $lease->getStatus()) {
            $lease->setConfirmingDate(new \DateTime('now'));

            // set product invisible and can't be appointed
            $product = $lease->getProduct();
            if (!is_null($product)) {
                $product->setVisible(false);
                $product->setAppointment(false);
            }
        }

        $em->persist($lease);
        $em->flush();

        $message = '创建合同';
        $this->get('sandbox_api.admin_remark')->autoRemark(
            $this->getAdminId(),
            $platform,
            $salesCompanyId,
            $message,
            AdminRemark::OBJECT_LEASE,
            $lease->getId()
        );

        $logMessage = '创建合同：'.$lease->getSerialNumber();
        $leaseClueId = $lease->getLeaseClueId();
        if ($leaseClueId) {
            $leaseClue = $em->getRepository('SandboxApiBundle:Lease\LeaseClue')->find($leaseClueId);
            $leaseClue->setStatus(LeaseClue::LEASE_CLUE_STATUS_CONTRACT);

            $clueMessage = '转为合同: '.$lease->getSerialNumber();

            $this->get('sandbox_api.admin_remark')->autoRemark(
                $this->getAdminId(),
                $platform,
                $salesCompanyId,
                $clueMessage,
                AdminRemark::OBJECT_LEASE_CLUE,
                $leaseClueId
            );

            $this->get('sandbox_api.admin_remark')->inheritRemark(
                AdminRemark::OBJECT_LEASE_CLUE,
                $leaseClueId,
                AdminRemark::OBJECT_LEASE,
                $lease->getId()
            );

            $logMessage = '从线索：'.$leaseClue->getSerialNumber().' 转为合同';
        }

        $leaseOfferId = $lease->getLeaseOfferId();
        if ($leaseOfferId) {
            $leaseOffer = $em->getRepository('SandboxApiBundle:Lease\LeaseOffer')->find($leaseOfferId);
            $leaseOffer->setStatus(LeaseOffer::LEASE_OFFER_STATUS_CONTRACT);

            $offerMessage = '转为合同: '.$lease->getSerialNumber();

            $this->get('sandbox_api.admin_remark')->autoRemark(
                $this->getAdminId(),
                $platform,
                $salesCompanyId,
                $offerMessage,
                AdminRemark::OBJECT_LEASE_OFFER,
                $leaseOfferId
            );

            $this->get('sandbox_api.admin_remark')->inheritRemark(
                AdminRemark::OBJECT_LEASE_OFFER,
                $leaseOfferId,
                AdminRemark::OBJECT_LEASE,
                $lease->getId()
            );

            $logMessage = '从报价：'.$leaseOffer->getSerialNumber().' 转为合同';
        }

        $this->get('sandbox_api.admin_status_log')->autoLog(
            $this->getAdminId(),
            Lease::LEASE_STATUS_DRAFTING,
            $logMessage,
            AdminStatusLog::OBJECT_LEASE,
            $lease->getId(),
            AdminStatusLog::TYPE_SALES_ADMIN,
            $lease->getCompanyId()
        );

        if (!empty($bills['add'])) {
            $logMessage = '从合同：'.$lease->getSerialNumber().' 创建账单';
            $leaseBills = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->findBy(array(
                    'status' => LeaseBill::STATUS_PENDING,
                    'lease' => $lease,
                ));

            $billIds = [];
            foreach ($leaseBills as $leaseBill) {
                $billIds[] = $leaseBill->getId();
            }

            if (!empty($bills['edit'])) {
                $editIds = [];
                foreach ($bills['edit'] as $edit) {
                    $editIds[] = $edit['id'];
                }

                $billIds = array_diff($billIds, $editIds);
            }

            foreach ($billIds as $billId) {
                $this->get('sandbox_api.admin_status_log')->autoLog(
                    $this->getAdminId(),
                    LeaseBill::STATUS_PENDING,
                    $logMessage,
                    AdminStatusLog::OBJECT_LEASE_BILL,
                    $billId,
                    AdminStatusLog::TYPE_SALES_ADMIN,
                    $lease->getCompanyId()
                );
            }
        }

        if (Lease::LEASE_STATUS_PERFORMING == $lease->getStatus()) {
            $userId = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->getUserIdByCustomerId($lease->getLesseeCustomer());
            if ($userId) {
                $this->addDoorAccess($lease, $userId);

                $urlParam = 'ptype=leasesDetail&leasesId='.$lease->getId();
                $contentArray = $this->generateLeaseContentArray($urlParam);
                $this->generateJpushNotification(
                    [
                        $userId,
                    ],
                    LeaseConstants::LEASE_PERFORMING_MESSAGE,
                    null,
                    $contentArray
                );
            }
        }

        $response = array(
            'id' => $lease->getId(),
        );

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_CREATE,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));

        return new View($response, 201);
    }

    /**
     * @param $leaseRentTypeIds
     * @param Lease $lease
     */
    private function handleLeaseRentTypesPost(
        $leaseRentTypeIds,
        $lease
    ) {
        foreach ($leaseRentTypeIds as $leaseRentTypeId) {
            $leaseRentType = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseRentTypes')
                ->find($leaseRentTypeId);
            if (is_null($leaseRentType)) {
                throw new NotFoundHttpException(CustomErrorMessagesConstants::ERROR_LEASE_RENT_TYPE_NOT_FOUND_MESSAGE);
            }
            $lease->addLeaseRentTypes($leaseRentType);
        }
    }

    /**
     * @param Lease $lease
     * @param $bills
     * @param $leaseRentTypeIds
     * @param $oldStatus
     * @param $oldLesseeCustomer
     * @param $oldStartDate
     * @param $oldEndDate
     *
     * @return View
     */
    private function handleLeasePut(
        $lease,
        $bills,
        $leaseRentTypeIds,
        $oldStatus,
        $oldLesseeCustomer,
        $oldStartDate,
        $oldEndDate
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $platform = $adminPlatform['platform'];

        $em = $this->getDoctrine()->getManager();

        $userId = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->getUserIdByCustomerId($lease->getLesseeCustomer());

        $lease = $this->checkLeaseData($lease);

        switch ($oldStatus) {
            case Lease::LEASE_STATUS_DRAFTING:
                if (Lease::LEASE_STATUS_PERFORMING == $lease->getStatus()) {
                    $lease->setConfirmingDate(new \DateTime('now'));
                }

                break;
            case Lease::LEASE_STATUS_PERFORMING:
                $lease->setStatus(Lease::LEASE_STATUS_PERFORMING);

                break;
            default:
                return $this->customErrorView(
                    400,
                    CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_CODE,
                    CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_MESSAGE
                );
        }

        $this->handleLeaseRentTypesPut($leaseRentTypeIds, $lease);
        $this->handleLeaseBillPut($bills, $lease);

        $em->flush();

        $message = '更新合同';
        $this->get('sandbox_api.admin_remark')->autoRemark(
            $this->getAdminId(),
            $platform,
            $salesCompanyId,
            $message,
            AdminRemark::OBJECT_LEASE,
            $lease->getId()
        );

        if (Lease::LEASE_STATUS_DRAFTING == $oldStatus &&
            Lease::LEASE_STATUS_PERFORMING == $lease->getStatus()
        ) {
            $logMessage = '生效合同';
            $this->get('sandbox_api.admin_status_log')->autoLog(
                $this->getAdminId(),
                Lease::LEASE_STATUS_PERFORMING,
                $logMessage,
                AdminStatusLog::OBJECT_LEASE,
                $lease->getId(),
                AdminStatusLog::TYPE_SALES_ADMIN,
                $lease->getCompanyId()
            );

            if ($userId) {
                $this->addDoorAccess($lease, $userId);

                $urlParam = 'ptype=leasesDetail&leasesId='.$lease->getId();
                $contentArray = $this->generateLeaseContentArray($urlParam);
                // send Jpush notification
                if ($userId) {
                    $this->generateJpushNotification(
                        [
                            $userId,
                        ],
                        LeaseConstants::LEASE_PERFORMING_MESSAGE,
                        null,
                        $contentArray
                    );
                }
            }
        }

        if (Lease::LEASE_STATUS_PERFORMING == $oldStatus &&
            Lease::LEASE_STATUS_PERFORMING == $lease->getStatus()
        ) {
            if ($oldLesseeCustomer != $lease->getLesseeCustomer() ||
                $oldStartDate != $lease->getStartDate() ||
                $oldEndDate != $lease->getEndDate()
            ) {
                if ($userId) {
                    $this->editDoorAccess($lease, $userId);
                }
            }
        }

        if (!empty($bills['add'])) {
            $logMessage = '从合同：'.$lease->getSerialNumber().' 创建账单';
            $leaseBills = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->findBy(array(
                    'status' => LeaseBill::STATUS_PENDING,
                    'lease' => $lease,
                ));

            $billIds = [];
            foreach ($leaseBills as $leaseBill) {
                $billIds[] = $leaseBill->getId();
            }

            if (!empty($bills['edit'])) {
                $editIds = [];
                foreach ($bills['edit'] as $edit) {
                    $editIds[] = $edit['id'];
                }

                $billIds = array_diff($billIds, $editIds);
            }

            foreach ($billIds as $billId) {
                $this->get('sandbox_api.admin_status_log')->autoLog(
                    $this->getAdminId(),
                    LeaseBill::STATUS_PENDING,
                    $logMessage,
                    AdminStatusLog::OBJECT_LEASE_BILL,
                    $billId,
                    AdminStatusLog::TYPE_SALES_ADMIN,
                    $lease->getCompanyId()
                );
            }
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

    /**
     * @param Lease $lease
     * @param $userId
     */
    private function addDoorAccess(
        $lease,
        $userId
    ) {
        $em = $this->getDoctrine()->getManager();

        $lease->setAccessNo($this->generateAccessNumber());

        $base = $lease->getBuilding()->getServer();
        $roomDoors = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomDoors')
                ->findBy(['room' => $lease->getRoom()]);

        if (!is_null($base) && !empty($base) && !empty($roomDoors)) {
            $this->storeDoorAccess(
                    $em,
                    $lease->getAccessNo(),
                    $userId,
                    $lease->getBuildingId(),
                    $lease->getRoomId(),
                    $lease->getStartDate(),
                    $lease->getEndDate()
                );

            $em->flush();

            $userArray = $this->getUserArrayIfAuthed(
                    $base,
                    $userId,
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
        }

        $this->setDoorAccessForMembershipCard(
                $lease->getBuildingId(),
                [$userId],
                $lease->getStartDate(),
                $lease->getEndDate(),
                $lease->getSerialNumber(),
                UserGroupHasUser::TYPE_LEASE
            );
    }

    /**
     * @param Lease $lease
     * @param $userId
     */
    private function editDoorAccess(
        $lease,
        $userId
    ) {
        $em = $this->getDoctrine()->getManager();

        $base = $lease->getBuilding()->getServer();
        $roomDoors = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomDoors')
            ->findBy(['room' => $lease->getRoom()]);
        if (!is_null($base) && !empty($base) && !empty($roomDoors)) {
            $this->setAccessActionToDelete($lease->getAccessNo());

            $em->flush();

            $this->callRepealRoomOrderCommand(
                $lease->getBuilding()->getServer(),
                $lease->getAccessNo()
            );

            $lease->setAccessNo($this->generateAccessNumber());

            $users = $lease->getInvitedPeopleIds();
            array_push($users, $userId);
            $this->addPeople(
                $users,
                $lease,
                $lease->getBuilding()->getServer()
            );
        }

        // Remove old supervisor to User Group
        $door = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupDoors')
            ->getGroupsByBuilding(
                $lease->getBuildingId(),
                true
            );

        if ($door) {
            $card = $door->getCard();

            $this->addUserToUserGroup(
                $em,
                [$userId],
                $card,
                $lease->getStartDate(),
                new \DateTime('now'),
                $lease->getSerialNumber(),
                UserGroupHasUser::TYPE_LEASE
            );

            // Add new supervisor to User Group
            $this->setDoorAccessForMembershipCard(
                $lease->getBuildingId(),
                [$userId],
                $lease->getStartDate(),
                $lease->getEndDate(),
                $lease->getSerialNumber(),
                UserGroupHasUser::TYPE_LEASE
            );
        }
    }

    /**
     * @param Lease $lease
     *
     * @return array
     */
    private function checkLeaseData(
        $lease
    ) {
        $em = $this->getDoctrine()->getManager();

        if (
            Lease::LEASE_STATUS_PERFORMING != $lease->getStatus() &&
            Lease::LEASE_STATUS_DRAFTING != $lease->getStatus()
        ) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_CODE,
                CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_MESSAGE
            );
        }

        $customerId = $lease->getLesseeCustomer();
        if (is_null($customerId)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        } else {
            $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')->find($customerId);
            $this->throwNotFoundIfNull($customer, self::NOT_FOUND_MESSAGE);
        }

        if (Lease::LEASE_LESSEE_TYPE_ENTERPRISE == $lease->getLesseeType()) {
            $enterpriseId = $lease->getLesseeEnterprise();
            if (is_null($enterpriseId)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            } else {
                // check salse enterprise
                $enterprise = $em->getRepository('SandboxApiBundle:User\EnterpriseCustomer')->find($enterpriseId);
                $this->throwNotFoundIfNull($enterprise, self::NOT_FOUND_MESSAGE);

                $enterpriseContacts = $em->getRepository('SandboxApiBundle:User\EnterpriseCustomerContacts')
                    ->findOneBy(array('enterpriseCustomerId' => $enterpriseId, 'customerId' => $customerId));
                if (!$enterpriseContacts) {
                    $enterpriseContacts = new EnterpriseCustomerContacts();
                    $enterpriseContacts->setCustomerId($customerId);
                    $enterpriseContacts->setEnterpriseCustomerId($enterpriseId);

                    $em->persist($enterpriseContacts);
                }
            }
        }
        $buildingId = $lease->getBuildingId();
        if ($buildingId) {
            $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);
            $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);
        }

        $productId = $lease->getProductId();
        if ($productId) {
            $product = $em->getRepository('SandboxApiBundle:Product\Product')->find($productId);
            $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

            $lease->setProduct($product);
        }

        $leaseClueId = $lease->getLeaseClueId();
        if ($leaseClueId) {
            $leaseClue = $em->getRepository('SandboxApiBundle:Lease\LeaseClue')->find($leaseClueId);
            $this->throwNotFoundIfNull($leaseClue, self::NOT_FOUND_MESSAGE);
        }

        $leaseOfferId = $lease->getLeaseOfferId();
        if ($leaseOfferId) {
            $leaseOffer = $em->getRepository('SandboxApiBundle:Lease\LeaseOffer')->find($leaseOfferId);
            $this->throwNotFoundIfNull($leaseOffer, self::NOT_FOUND_MESSAGE);
        }

        $startDate = $lease->getStartDate();
        if ($startDate) {
            $lease->setStartDate(new \DateTime($startDate));
        }

        $endDate = $lease->getEndDate();
        if ($endDate) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);
            $lease->setEndDate($endDate);
        }

        return $lease;
    }

    /**
     * @param $leaseRentTypeIds
     * @param Lease $lease
     */
    private function handleLeaseRentTypesPut(
        $leaseRentTypeIds,
        $lease
    ) {
        $leaseRentTypes = $lease->getLeaseRentTypes();
        foreach ($leaseRentTypes as $leaseRentType) {
            $lease->removeLeaseRentTypes($leaseRentType);
        }

        foreach ($leaseRentTypeIds as $leaseRentTypeId) {
            $leaseRentType = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseRentTypes')
                ->find($leaseRentTypeId);
            if ($leaseRentType) {
                $lease->addLeaseRentTypes($leaseRentType);
            }
        }
    }

    private function handleLeaseBillPut(
        $bills,
        $lease
    ) {
        if (empty($bills['add']) && empty($bills['edit']) && empty($bills['remove'])) {
            return;
        }

        $amount = 0;
        if (!empty($bills['add'])) {
            $addAmount = $this->addBills($bills['add'], $lease);

            $amount = $amount + $addAmount;
        }

        if (!empty($bills['edit'])) {
            $editAmount = $this->editBills($bills['edit'], $lease);

            $amount = $amount + $editAmount;
        }

        if (!empty($bills['remove'])) {
            $removeAmount = $this->removeBills($bills['remove'], $lease);

            $amount = $amount - $removeAmount;
        }

        if (Lease::LEASE_STATUS_DRAFTING !== $lease->getStatus() && $amount <= 0) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_KEEP_AT_LEAST_ONE_BILL_MESSAGE);
        }
    }

    /**
     * @param $addBills
     * @param Lease $lease
     *
     * @return int
     */
    private function addBills(
        $addBills,
        $lease
    ) {
        $em = $this->getDoctrine()->getManager();
        $addAmount = 0;
        foreach ($addBills as $addBill) {
            if (Lease::LEASE_STATUS_DRAFTING !== $lease->getStatus()) {
                $this->checkLeaseBillAttributesIsValid($addBill);
            }

            $bill = new LeaseBill();

            if ($addBill['start_date']) {
                $startDate = new \DateTime($addBill['start_date']);
                $bill->setStartDate($startDate);
            }

            if ($addBill['end_date']) {
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

            $addAmount += 1;
        }

        return $addAmount;
    }

    /**
     * @param  $editBills
     * @param Lease $lease
     *
     * @return int
     */
    private function editBills(
        $editBills,
        $lease
    ) {
        $editAmount = 0;
        foreach ($editBills as $editBill) {
            if (empty($editBill['id'])) {
                throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE);
            }

            if (Lease::LEASE_STATUS_DRAFTING !== $lease->getStatus()) {
                $this->checkLeaseBillAttributesIsValid($editBill);
            }

            $bill = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\LeaseBill')->find($editBill['id']);
            $this->throwNotFoundIfNull($bill, CustomErrorMessagesConstants::ERROR_BILL_NOT_FOUND_MESSAGE);

            // only pending bills could be edited
            if (LeaseBill::STATUS_PENDING == $bill->getStatus()) {
                if ($editBill['start_date']) {
                    $startDate = new \DateTime($editBill['start_date']);
                    $bill->setStartDate($startDate);
                }

                if ($editBill['end_date']) {
                    $endDate = new \DateTime($editBill['end_date']);
                    $bill->setEndDate($endDate);
                }

                $bill->setName($editBill['name']);
                $bill->setAmount($editBill['amount']);
                $bill->setDescription($editBill['description']);
                $bill->setType(LeaseBill::TYPE_LEASE);
                $bill->setStatus(LeaseBill::STATUS_PENDING);
            }

            $editAmount += 1;
        }

        return $editAmount;
    }

    /**
     * @param $removedBills
     * @param $lease
     *
     * @return int
     */
    private function removeBills(
        $removedBills,
        $lease
    ) {
        $em = $this->getDoctrine()->getManager();
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
            ('double' != gettype($billAttributes['amount']) && 'integer' != gettype($billAttributes['amount'])) ||
            is_null($billAttributes['amount']) ||
            !filter_var($billAttributes['name'], FILTER_DEFAULT) ||
            !filter_var($billAttributes['description'], FILTER_DEFAULT) ||
            !preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $billAttributes['start_date']) ||
            !preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $billAttributes['end_date'])
        ) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE);
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
}
