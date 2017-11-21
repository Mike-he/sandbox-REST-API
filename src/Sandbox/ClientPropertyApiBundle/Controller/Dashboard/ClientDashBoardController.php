<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Dashboard;

use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;
use Sandbox\ApiBundle\Entity\MembershipCard\MembershipOrder;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Reservation\Reservation;
use Sandbox\ApiBundle\Entity\Room\Room;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ClientDashBoardController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/dashboard/statistical")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getStatisticalAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];
        $adminId = $this->getAdminId();

        $myLatestReservation = $this->countMyLatestReservation($companyId, $adminId);

        $now = new \DateTime();
        $waitingReservation = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->countCompanyUngrabedReservation(
                $companyId,
                $now
            );

        $unpaidOrders = $this->countUnpaidOrders($adminId);

        $BillsCount = $this->countBills($adminId);

        $expiringContract = $this->countExpiringContract($adminId);

        $result = [
            'my_latest_reservation' => $myLatestReservation,
            'waiting_reservation' => $waitingReservation,
            'unpaid_orders' => $unpaidOrders,
            'unpaid_bills' => $BillsCount['unpaid_bills'],
            'not_pushed_month_bills' => $BillsCount['not_pushed_month_bills'],
            'expiring_contract' => $expiringContract,
        ];

        $view = new View();
        $view->setData($result);

        return $view;
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
     * @param $companyId
     * @param $adminId
     *
     * @return int
     */
    private function countMyLatestReservation(
        $companyId,
        $adminId
    ) {
        $grabStart = new \DateTime();
        $interval = new \DateInterval('P15D');
        $grabStart = $grabStart->sub($interval);

        $grabEnd = new \DateTime();

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->countReservationByAdminId(
                $companyId,
                $adminId,
                Reservation::GRABED,
                $grabStart,
                $grabEnd
            );

        return $count;
    }

    /**
     * @param $adminId
     *
     * @return int
     */
    private function countUnpaidOrders(
        $adminId
    ) {
        $myBuildingIdsForOrder = $this->get('sandbox_api.admin_permission_check_service')
            ->getMySalesBuildingIds(
                $adminId,
                array(
                    AdminPermission::KEY_SALES_BUILDING_ORDER,
                )
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countOrders(
                $myBuildingIdsForOrder,
                ProductOrder::STATUS_UNPAID
            );

        return $count;
    }

    /**
     * @param $adminId
     *
     * @return array
     */
    private function countBills(
        $adminId
    ) {
        $myBuildingIds = $this->get('sandbox_api.admin_permission_check_service')
            ->getMySalesBuildingIds(
                $adminId,
                array(
                    AdminPermission::KEY_SALES_BUILDING_LEASE_BILL,
                )
            );

        $leaseStatus = array(
            Lease::LEASE_STATUS_PERFORMING,
            Lease::LEASE_STATUS_TERMINATED,
            Lease::LEASE_STATUS_MATURED,
            Lease::LEASE_STATUS_END,
            Lease::LEASE_STATUS_CLOSED,
        );

        $unpaidBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBillsForClientProperty(
                $leaseStatus,
                $myBuildingIds,
                LeaseBill::STATUS_UNPAID
            );

        $now = new \DateTime();
        $startDate = $now->format('Y-m-01 00:00:00');
        $endDate = $now->format('Y-m-t 23:59:59');
        $notPushedBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBillsForClientProperty(
                $leaseStatus,
                $myBuildingIds,
                LeaseBill::STATUS_PENDING,
                $startDate,
                $endDate
            );

        $result = [
            'unpaid_bills' => $unpaidBills,
            'not_pushed_month_bills' => $notPushedBills,
        ];

        return $result;
    }

    /**
     * @param $adminId
     *
     * @return int
     */
    private function countExpiringContract($adminId)
    {
        $myBuildingIds = $this->get('sandbox_api.admin_permission_check_service')
            ->getMySalesBuildingIds(
                $adminId,
                array(
                    AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
                )
            );

        $expiringStart = new \DateTime();
        $expiringEnd = new \DateTime();
        $interval = new \DateInterval('P30D');
        $expiringEnd = $expiringEnd->add($interval);

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->countExpiringContract(
                $myBuildingIds,
                Lease::LEASE_STATUS_PERFORMING,
                $expiringStart,
                $expiringEnd
            );

        return $count;
    }

    /**
     * @param $createStart
     * @param $createEnd
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    private function getTodayLeaseClue(
        $createStart,
        $createEnd,
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
                $createStart,
                $createEnd,
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
                $createStart,
                $createEnd
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
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
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
            'room_name' => $room->getName(),
            'attachment' => $roomAttachment,
            'building_name' => $building->getName(),
            'start_date' => $clue->getStartDate(),
            'cycle' => $clue->getCycle(),
            'source' => $source,
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
