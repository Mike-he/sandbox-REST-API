<?php

namespace Sandbox\SalesApiBundle\Controller\Dashboard;

use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\Lease;
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

        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $roomType = $paramFetcher->get('room_type');
        $startString = $paramFetcher->get('start');
        $endString = $paramFetcher->get('end');
        $building = $paramFetcher->get('building');
        $query = $paramFetcher->get('query');

        $start = new \DateTime($startString);
        $start->setTime(0, 0, 0);
        $end = new \DateTime($endString);
        $end->setTime(23, 59, 59);

        $products = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->findProductIdsByRoomType(
                $salesCompanyId,
                $roomType,
                $building,
                $query
            );

        $usages = array();
        foreach ($products as $product) {
            $usages[] = $this->generateOrders(
                $product,
                $roomType,
                $start,
                $end
            );
        }

        $view = new View();
        $view->setData($usages);

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
        switch ($roomType) {
            case Room::TYPE_FLEXIBLE:
                $orders = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Order\ProductOrder')
                    ->getRoomUsersUsage(
                        $product['id'],
                        $start,
                        $end
                    );

                $orderList = $this->handleFlexibleOrder($orders);
                break;
            case Room::TYPE_LONG_TERM:
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

                $orderList = $this->handleLease($leases);
                break;
            default:
                $orders = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Order\ProductOrder')
                    ->getRoomUsersUsage(
                        $product['id'],
                        $start,
                        $end
                    );

                $orderList = $this->handleOrders($orders);
        }

        $attachment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
            ->findAttachmentsByRoom($product['room_id'], 1);

        $product['attachment'] = $attachment;

        if ($product['room_type'] == Room::TYPE_FIXED) {
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

        $result = array(
            'product' => $product,
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_PLATFORM_DASHBOARD],
            ],
            $opLevel
        );
    }
}
