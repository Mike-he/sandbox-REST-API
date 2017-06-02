<?php

namespace Sandbox\SalesApiBundle\Controller\Dashboard;

use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\Lease;
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
                    $usages[] = $this->generateMembershipCardOrders(
                        $membershipCard,
                        $start,
                        $end
                    );
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
            Lease::LEASE_STATUS_CONFIRMED,
            Lease::LEASE_STATUS_RECONFIRMING,
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

        if ($roomType == Room::TYPE_DESK && $product['type_tag'] == 'hot_desk') {
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

        if ($product['room_type'] == Room::TYPE_DESK) {
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
     * @param $orders
     *
     * @return array
     */
    private function handleOrders(
        $orders
    ) {
        $result = array();
        foreach ($orders as $order) {
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
                );
            }
        }

        return $result;
    }

    /**
     * @param $leases
     *
     * @return array
     */
    private function handleLease(
        $leases
    ) {
        $result = array();
        foreach ($leases as $lease) {
            $result[] = array(
                'lease_id' => $lease->getId(),
                'start_date' => $lease->getStartDate(),
                'end_date' => $lease->getEndDate(),
                'user' => $lease->getSupervisorId(),
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
}
