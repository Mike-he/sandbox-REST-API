<?php

namespace Sandbox\SalesApiBundle\Controller\Dashboard;

use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sandbox\ApiBundle\Entity\Room\Room;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Class AdminDashBoardController.
 */
class AdminDashBoardController extends SalesRestController
{
    const TYPE_MEMBERSHIP_CARD = 'membership_card';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="room_type",
     *    array=false,
     *    nullable=false,
     *    description="Filter by room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start",
     *    nullable=false,
     *    description=""
     * )
     *
     *  @Annotations\QueryParam(
     *    name="end",
     *    nullable=false,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="Filter by building"
     * )
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="visible",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by visibility"
     * )
     *
     * @Route("/dashboard/rooms/usage")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getRoomUsageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminDashboardPermissions(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $roomType = $paramFetcher->get('room_type');
        $startString = $paramFetcher->get('start');
        $endString = $paramFetcher->get('end');
        $building = $paramFetcher->get('building');
        $query = $paramFetcher->get('query');
        $visible = $paramFetcher->get('visible');

        $start = new \DateTime($startString);
        $start->setTime(0, 0, 0);
        $end = new \DateTime($endString);
        $end->setTime(23, 59, 59);

        $usages = array();
        switch ($roomType) {
            case self::TYPE_MEMBERSHIP_CARD:
                $cardIds = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:User\UserGroupDoors')
                    ->getMembershipCard(
                        $building
                    );

                $membershipCards = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')
                    ->getCards(
                        $salesCompanyId,
                        $cardIds,
                        $visible,
                        $query
                    );

                foreach ($membershipCards as $membershipCard) {
                    $specification = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:MembershipCard\MembershipCardSpecification')
                        ->findBy(array('card' => $membershipCard));
                    if ($specification) {
                        $usages[] = $this->generateMembershipCardOrders(
                            $membershipCard,
                            $start,
                            $end
                        );
                    }
                }

                break;
            default:
                $products = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Product\Product')
                    ->findProductIdsByRoomType(
                        $salesCompanyId,
                        $roomType,
                        $building,
                        $query,
                        $visible
                    );

                foreach ($products as $product) {
                    $usages[] = $this->generateOrders(
                        $product,
                        $roomType,
                        $start,
                        $end
                    );
                }
        }

        $view = new View();
        $view->setData($usages);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/dashboard/buildings")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBuildingsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->checkAdminDashboardPermissions(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $buildings = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->getLocationRoomBuildings(
                    null,
                    null,
                    $salesCompanyId,
                    RoomBuilding::STATUS_ACCEPT,
                    true
                );

        $result = array();
        foreach ($buildings as $building) {
            $result[] = array(
                'id' => $building->getId(),
                'name' => $building->getName(),
            );
        }

        $view = new View();
        $view->setData($result);

        return $view;
    }

    /**
     * @param $product
     * @param $roomType
     * @param $start
     * @param $end
     *
     * @return array
     */
    private function generateOrders(
        $product,
        $roomType,
        $start,
        $end
    ) {
        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRoomUsersUsage(
                $product['id'],
                $start,
                $end
            );

        $orderList = $this->handleOrders($orders);

        $status = array(
            Lease::LEASE_STATUS_PERFORMING,
            Lease::LEASE_STATUS_END,
            Lease::LEASE_STATUS_MATURED,
            Lease::LEASE_STATUS_TERMINATED,
            Lease::LEASE_STATUS_CLOSED,
        );
        $leases = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->getRoomUsersUsage(
                $product['id'],
                $start,
                $end,
                $status
            );

        $leaseList = $this->handleLease($leases);

        $orderList = array_merge($orderList, $leaseList);

        if (Room::TYPE_DESK == $roomType && 'hot_desk' == $product['type_tag']) {
            $orders = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Order\ProductOrder')
                ->getRoomUsersUsage(
                    $product['id'],
                    $start,
                    $end
                );

            $orderList = $this->handleFlexibleOrder($orders);
        }

        $attachment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
            ->findAttachmentsByRoom($product['room_id'], 1);

        $product['attachment'] = $attachment;

        if (Room::TYPE_DESK == $product['room_type']) {
            $seats = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomFixed')
                ->findBy(array(
                    'room' => $product['room_id'],
                ));

            $productSeats = array();
            foreach ($seats as $seat) {
                $productSeats[] = array(
                    'id' => $seat->getId(),
                    'seat_number' => $seat->getSeatNumber(),
                    'base_price' => (float) $seat->getBasePrice(),
                );
            }

            $product['seats'] = $productSeats;
        }

        $productLeasingSets = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
            ->findBy(array('product' => $product['id']));

        foreach ($productLeasingSets as $productLeasingSet) {
            $product['leasing_sets'][] = array(
                'base_price' => $productLeasingSet->getBasePrice(),
                'unit_price' => $productLeasingSet->getUnitPrice(),
                'amount' => $productLeasingSet->getAmount(),
            );
        }

        if (Room::TYPE_OFFICE == $roomType) {
            $productRentSet = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\ProductRentSet')
                ->findOneBy(array('product' => $product['id'], 'status' => true));
            if ($productRentSet) {
                $product['rent_set'] = array(
                    'base_price' => $productRentSet->getBasePrice(),
                    'unit_price' => $productRentSet->getUnitPrice(),
                );
            }
        }

        $result = array(
            'product' => $product,
            'orders' => $orderList,
        );

        return $result;
    }

    /**
     * @param $card
     * @param $start
     * @param $end
     *
     * @return array
     */
    private function generateMembershipCardOrders(
        $card,
        $start,
        $end
    ) {
        $months = new \DatePeriod(
            $start,
            new \DateInterval('P1M'),
            $end
        );

        $orderList = array();
        $max = 0;
        foreach ($months as $month) {
            $startDate = $month;
            $endDate = clone $month;
            $endDate = $endDate->modify('last day of this month');
            $endDate->setTime(23, 59, 59);

            $count = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
                ->countMembershipOrdersByDate(
                    $card,
                    $startDate,
                    $endDate
                );

            if ($count > $max) {
                $max = $count;
            }

            $orderList[] = array(
                'month' => $month,
                'count' => $count,
            );
        }

        $specification = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCardSpecification')
            ->findBy(array('card' => $card));

        $card->setSpecification($specification);

        $result = array(
            'card' => $card,
            'max' => $max,
            'orders' => $orderList,
        );

        return $result;
    }

    /**
     * @param ProductOrder $orders
     *
     * @return array
     */
    private function handleOrders(
        $orders
    ) {
        $result = array();
        foreach ($orders as $order) {
            /** @var ProductOrder $order */
            $invitedPeoples = $order->getInvitedPeople();
            $invited = array();
            foreach ($invitedPeoples as $invitedPeople) {
                $invited[] = array(
                    'user_id' => $invitedPeople->getUserId(),
                );
            }

            $result[] = array(
                'order_id' => $order->getId(),
                'start_date' => $order->getStartDate(),
                'end_date' => $order->getEndDate(),
                'user' => $order->getUserId(),
                'appointed_user' => $order->getAppointed(),
                'invited_people' => $invited,
                'seat_id' => $order->getSeatId(),
                'type' => $order->getType(),
                'status' => $order->getStatus(),
                'pay_channel' => $order->getPayChannel(),
                'customer_id' => $order->getCustomerId(),
            );
        }

        return $result;
    }

    /**
     * @param $orders
     *
     * @return array
     */
    private function handleFlexibleOrder(
        $orders
    ) {
        $result = array();
        foreach ($orders as $order) {
            /** @var ProductOrder $order */
            $invitedPeoples = $order->getInvitedPeople();
            $invited = array();
            foreach ($invitedPeoples as $invitedPeople) {
                $invited[] = array(
                    'user_id' => $invitedPeople->getUserId(),
                );
            }

            $startDate = $order->getStartDate();
            $endDate = $order->getEndDate();
            $user = $order->getUserId();
            $appointed = $order->getAppointed();
            $days = new \DatePeriod(
                $startDate,
                new \DateInterval('P1D'),
                $endDate
            );

            foreach ($days as $day) {
                $result[] = array(
                    'order_id' => $order->getId(),
                    'date' => $day->format('Y-m-d'),
                    'user' => $user,
                    'appointed_user' => $appointed,
                    'invited_people' => $invited,
                    'type' => $order->getType(),
                    'status' => $order->getStatus(),
                    'pay_channel' => $order->getPayChannel(),
                    'customer_id' => $order->getCustomerId(),
                );
            }
        }

        return $result;
    }

    /**
     * @param Lease $leases
     *
     * @return array
     */
    private function handleLease(
        $leases
    ) {
        $result = array();
        foreach ($leases as $lease) {
            /* @var Lease $lease */
            $result[] = array(
                'lease_id' => $lease->getId(),
                'start_date' => $lease->getStartDate(),
                'end_date' => $lease->getEndDate(),
                'customer_id' => $lease->getLesseeCustomer(),
                'invited_people' => $lease->degenerateInvitedPeople(),
                'status' => $lease->getStatus(),
            );
        }

        return $result;
    }

    /**
     * @param $opLevel
     */
    private function checkAdminDashboardPermissions(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_PLATFORM_DASHBOARD],
            ],
            $opLevel
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/dashboard/today/events")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getTodayEventsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $startDate = new \DateTime();
        $startDate->setTime(0, 0, 0);

        $endDate = new \DateTime();
        $endDate->setTime(23, 59, 59);

        $adminId = $this->getAdminId();

        $result = array();

        $cluePermission = $this->get('sandbox_api.admin_permission_check_service')->checkAdminHasPermissions(
            $adminId,
            [AdminPermission::KEY_SALES_BUILDING_LEASE_CLUE]
        );

        if ($cluePermission) {
            $leaseClue = $this->getTodayLeaseClue($startDate, $endDate);
            $result['lease_clue'] = $leaseClue;
        }

        $orderPermission = $this->get('sandbox_api.admin_permission_check_service')->checkAdminHasPermissions(
            $adminId,
            [AdminPermission::KEY_SALES_BUILDING_ORDER]
        );

        if ($orderPermission) {
            $productOrders = $this->getTodayProductOrders($startDate, $endDate);
            $result['product_order'] = $productOrders;
        }

        $eventPermission = $this->get('sandbox_api.admin_permission_check_service')->checkAdminHasPermissions(
            $adminId,
            [AdminPermission::KEY_SALES_PLATFORM_EVENT_ORDER]
        );

        if ($eventPermission) {
            $eventOrders = $this->getTodayEventOrders($startDate, $endDate);
            $result['event_order'] = $eventOrders;
        }

        $cardPermission = $this->get('sandbox_api.admin_permission_check_service')->checkAdminHasPermissions(
            $adminId,
            [AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_ORDER]
        );

        if ($cardPermission) {
            $membershipCardOrder = $this->getTodayMembershipCardOrders($startDate, $endDate);
            $result['membership_card_order'] = $membershipCardOrder;
        }

        $view = new View();
        $view->setData($result);

        return $view;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    private function getTodayLeaseClue(
        $startDate,
        $endDate,
        $limit = 3,
        $offset = 0
    ) {
        $myBuildingIds = $this->get('sandbox_api.admin_permission_check_service')
            ->getMySalesBuildingIds(
                $this->getAdminId(),
                array(
                    AdminPermission::KEY_SALES_BUILDING_LEASE_CLUE,
                )
            );

        $clueLists = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseClue')
            ->findClues(
                $myBuildingIds,
                null,
                null,
                null,
                null,
                $startDate,
                $endDate,
                null,
                null,
                null,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseClue')
            ->countClues(
                $myBuildingIds,
                null,
                null,
                null,
                null,
                $startDate,
                $endDate
            );

        $clueData = [];
        foreach ($clueLists as $clueList) {
            $clueData[] = $this->handleClueData($clueList);
        }

        $result = array(
            'lists' => $clueData,
            'count' => $count,
        );

        return  $result;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    private function getTodayProductOrders(
        $startDate,
        $endDate,
        $limit = 3,
        $offset = 0
    ) {
        $em = $this->getDoctrine()->getManager();

        $myBuildingIds = $this->get('sandbox_api.admin_permission_check_service')
            ->getMySalesBuildingIds(
                $this->getAdminId(),
                array(
                    AdminPermission::KEY_SALES_BUILDING_ORDER,
                )
            );

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrderLists(
                $myBuildingIds,
                null,
                $startDate,
                $endDate,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countOrders(
                $myBuildingIds,
                null,
                $startDate,
                $endDate
            );

        $receivableTypes = [
            'sales_wx' => '微信',
            'sales_alipay' => '支付宝支付',
            'sales_cash' => '现金',
            'sales_others' => '其他',
            'sales_pos' => 'POS机',
            'sales_remit' => '线下汇款',
        ];

        $orderData = [];
        foreach ($orders as $order) {
            $orderData[] = $this->handleProductOrderData(
                $order,
                $receivableTypes
            );
        }

        $result = array(
            'lists' => $orderData,
            'count' => $count,
        );

        return  $result;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    private function getTodayEventOrders(
        $startDate,
        $endDate,
        $limit = 3,
        $offset = 0
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getEventOrdersForPropertyClient(
                $startDate,
                $endDate,
                $companyId,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->countEventOrdersForPropertyClient(
                $startDate,
                $endDate,
                $companyId
            );

        $orderData = [];
        foreach ($orders as $order) {
            $orderData[] = $this->handleEventOrderData($order);
        }

        $result = array(
            'lists' => $orderData,
            'count' => $count,
        );

        return  $result;
    }

    private function getTodayMembershipCardOrders(
        $startDate,
        $endDate,
        $limit = 3,
        $offset = 0
    ) {
        $platform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $platform['sales_company_id'];

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getOrdersByPropertyClient(
                $companyId,
                $startDate,
                $endDate,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->countOrdersByPropertyClient(
                $companyId,
                $startDate,
                $endDate
            );

        $orderData = [];
        foreach ($orders as $order) {
            $orderData[] = $this->handleMembershipCardOrderData($order);
        }

        $result = array(
            'lists' => $orderData,
            'count' => $count,
        );

        return  $result;
    }

    /**
     * @param LeaseClue $clue
     *
     * @return array
     */
    private function handleClueData(
        $clue
    ) {
        $em = $this->getDoctrine()->getManager();

        $buildingId = $clue->getBuildingId();
        $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);

        $productId = $clue->getProductId();
        $product = $em->getRepository('SandboxApiBundle:Product\Product')->find($productId);
        /** @var Room $room */
        $room = $product->getRoom();

        $roomAttachmentBinding = $em->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
            ->findOneBy(array('room' => $room->getId()));

        $roomAttachment = $roomAttachmentBinding ? $roomAttachmentBinding->getAttachmentId()->getContent() : null;

        if ($clue->getProductAppointmentId()) {
            $source = '客户申请';
        } else {
            $source = '管理员创建';
        }

        $result = array(
            'id' => $clue->getId(),
            'serial_number' => $clue->getSerialNumber(),
            'lessee_customer' => $clue->getLesseeCustomer(),
            'lessee_address' => $clue->getLesseeAddress(),
            'lessee_email' => $clue->getLesseeEmail(),
            'lessee_name' => $clue->getLesseeName(),
            'lessee_phone' => $clue->getLesseePhone(),
            'room_name' => $room->getName(),
            'attachment' => $roomAttachment,
            'building_name' => $building->getName(),
            'start_date' => $clue->getStartDate(),
            'cycle' => $clue->getCycle(),
            'source' => $source,
            'monthly_rent' => (float) $clue->getMonthlyRent(),
            'number' => $clue->getNumber(),
        );

        return $result;
    }

    /**
     * @param ProductOrder $order
     * @param $receivableTypes
     *
     * @return array
     */
    private function handleProductOrderData(
        $order,
        $receivableTypes
    ) {
        $em = $this->getDoctrine()->getManager();

        $product = $order->getProduct();
        /** @var Room $room */
        $room = $product->getRoom();
        $building = $room->getBuilding();

        $roomAttachmentBinding = $em->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
            ->findOneBy(array('room' => $room->getId()));

        $roomAttachment = $roomAttachmentBinding ? $roomAttachmentBinding->getAttachmentId()->getContent() : null;

        $roomType = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$room->getType());

        $payChannel = '';
        if ($order->getPayChannel()) {
            if (ProductOrder::CHANNEL_SALES_OFFLINE == $order->getPayChannel()) {
                $receivable = $em->getRepository('SandboxApiBundle:Finance\FinanceReceivables')
                    ->findOneBy([
                        'orderNumber' => $order->getOrderNumber(),
                    ]);
                $payChannel = $receivableTypes[$receivable->getPayChannel()];
            } else {
                $payChannel = '创合钱包支付';
            }
        }

        $orderType = $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.$order->getType());
        $status = $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$order->getStatus());

        $result = array(
            'id' => $order->getId(),
            'room_name' => $room->getName(),
            'attachment' => $roomAttachment,
            'building_name' => $building->getName(),
            'start_date' => $order->getStartDate(),
            'end_date' => $order->getEndDate(),
            'room_type' => $roomType,
            'order_type' => $orderType,
            'pay_channel' => $payChannel,
            'status' => $status,
            'price' => (float) $order->getPrice(),
            'discount_price' => (float) $order->getDiscountPrice(),
        );

        return $result;
    }

    /**
     * @param EventOrder $order
     *
     * @return array
     */
    private function handleEventOrderData(
        $order
    ) {
        /** @var Event $event */
        $event = $order->getEvent();

        $eventAttachment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventAttachment')
            ->findOneBy(array('eventId' => $event->getId()));

        $status = $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$order->getStatus());

        $result = array(
            'id' => $order->getId(),
            'event_name' => $event->getName(),
            'event_start_date' => $event->getEventStartDate(),
            'event_end_date' => $event->getEventEndDate(),
            'event_status' => $event->getStatus(),
            'address' => $event->getAddress(),
            'price' => (float) $event->getPrice(),
            'status' => $status,
            'pay_channel' => $order->getPayChannel() ? '创合钱包支付' : '',
            'attachment' => $eventAttachment ? $eventAttachment->getContent() : '',
        );

        return $result;
    }

    /**
     * @param MembershipOrder $order
     *
     * @return array
     */
    private function handleMembershipCardOrderData(
        $order
    ) {
        $card = $order->getCard();

        $result = array(
            'id' => $order->getId(),
            'name' => $card->getName(),
            'background' => $card->getBackground(),
            'specification' => $order->getSpecification(),
            'start_date' => $order->getStartDate(),
            'end_date' => $order->getEndDate(),
            'price' => (float) $order->getPrice(),
            'status' => '已付款',
            'pay_channel' => '创合钱包支付',
        );

        return $result;
    }
}
