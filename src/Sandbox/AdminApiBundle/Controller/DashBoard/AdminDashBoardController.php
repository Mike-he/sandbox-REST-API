<?php

namespace Sandbox\AdminApiBundle\Controller\DashBoard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;

/**
 * Class AdminDashBoardController.
 */
class AdminDashBoardController extends SandboxRestController
{
    /**
     * Get Total Number Of Users.
     *
     * @Route("/dashboard/users/total")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUsersTotalAction()
    {
        // check user permission
        $this->checkAdminDashboardPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $repo = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView');
        $count = $repo->countTotalUsers();

        return new View(array(
            'total' => $count,
        ));
    }

    /**
     * Get Registration Number Of Users.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="startDate"
     * )
     *
     * @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="endDate"
     * )
     *
     * @Route("/dashboard/users/reg")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUsersRegNumberAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminDashboardPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $now = new \DateTime('now');
        $yest = new \DateTime('now');
        $yest = $yest->modify('-1 day');

        $repo = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView');
        $today = $repo->countRegUsers($now->format('Y-m-d 00:00:00'), $now->format('Y-m-d 23:59:59'));
        $yesterday = $repo->countRegUsers($yest->format('Y-m-d 00:00:00'), $yest->format('Y-m-d 23:59:59'));

        $month = 0;
        if ($startDate && $endDate) {
            $month = $repo->countRegUsers($startDate.' 00:00:00', $endDate.' 23:59:59');
        }

        return new View(array(
            'today' => $today,
            'yesterday' => $yesterday,
            'month' => $month,
        ));
    }

    /**
     * Get Shop Orders.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="startDate"
     * )
     *
     * @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="endDate"
     * )
     *
     * @Annotations\QueryParam(
     *    name="buildingId",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="buildingId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="payChannel",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="payChannel"
     * )
     *
     * @Route("/dashboard/shop/orders")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getShopOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminDashboardPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $buildingId = $paramFetcher->get('buildingId');
        $payChannel = $paramFetcher->get('payChannel');
        $now = new \DateTime('now');
        $yesterday = new \DateTime('now');
        $yesterday = $yesterday->modify('-1 day');

        $building = !is_null($buildingId) ? $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId) : null;

        $repo = $this->getDoctrine()->getRepository('SandboxApiBundle:Shop\ShopOrder');
        $todayCompleted = $repo->countCompletedOrders(
            $now->format('Y-m-d 00:00:00'),
            $now->format('Y-m-d 23:59:59'),
            $payChannel,
            $building
        );

        $todayRefunded = $repo->countRefundOrders(
            $now->format('Y-m-d 00:00:00'),
            $now->format('Y-m-d 23:59:59'),
            $payChannel,
            $building
        );

        $yestCompleted = $repo->countCompletedOrders(
            $yesterday->format('Y-m-d 00:00:00'),
            $yesterday->format('Y-m-d 23:59:59'),
            $payChannel,
            $building
        );

        $yestRefunded = $repo->countRefundOrders(
            $yesterday->format('Y-m-d 00:00:00'),
            $yesterday->format('Y-m-d 23:59:59'),
            $payChannel,
            $building
        );

        $monthCompleted = $repo->countCompletedOrders(
            $now->format('Y-m-01 00:00:00'),
            $now->format('Y-m-31 23:59:59'),
            $payChannel,
            $building
        );

        $monthRefunded = $repo->countRefundOrders(
            $now->format('Y-m-01 00:00:00'),
            $now->format('Y-m-31 23:59:59'),
            $payChannel,
            $building
        );

        $monthlyCompleted = $repo->countCompletedOrders(
            $startDate.' 00:00:00',
            $endDate.' 23:59:59',
            $payChannel,
            $building
        );

        $monthlyRefunded = $repo->countRefundOrders(
            $startDate.' 00:00:00',
            $endDate.' 23:59:59',
            $payChannel,
            $building
        );

        $result = array(
            'today_complated' => $todayCompleted,
            'today_refunded' => $todayRefunded,
            'yestday_complated' => $yestCompleted,
            'yestday_refunded' => $yestRefunded,
            'month_complated' => $monthCompleted,
            'month_refunded' => $monthRefunded,
            'monthly_complated' => $monthlyCompleted,
            'monthly_refunded' => $monthlyRefunded,
        );

        return new View($result);
    }

    /**
     * Get Shop Orders List.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="completed|refunded"
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="today|yesterday|month"
     * )
     *
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="startDate"
     * )
     *
     * @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="endDate"
     * )
     *
     * @Annotations\QueryParam(
     *    name="buildingId",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="buildingId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="payChannel",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="payChannel"
     * )
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Offset of page"
     * )
     *
     * @Route("/dashboard/shop/orders/list")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getshopOrdersList(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminDashboardPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $type = $paramFetcher->get('type');
        $status = $paramFetcher->get('status');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $buildingId = $paramFetcher->get('buildingId');
        $payChannel = $paramFetcher->get('payChannel');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $building = !is_null($buildingId) ? $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId) : null;

        $repo = $this->getDoctrine()->getRepository('SandboxApiBundle:Shop\ShopOrder');
        $now = new \DateTime('now');
        switch ($type) {
            case 'today':
                $startDate = $now->format('Y-m-d 00:00:00');
                $endDate = $now->format('Y-m-d 23:59:59');
                break;
            case 'yesterday':
                $yesterday = new \DateTime('now');
                $yesterday = $yesterday->modify('-1 day');
                $startDate = $yesterday->format('Y-m-d 00:00:00');
                $endDate = $yesterday->format('Y-m-d 23:59:59');
                break;
            case 'month':
                $startDate = $now->format('Y-m-1 00:00:00');
                $endDate = $now->format('Y-m-31 23:59:59');
                break;
            default:
                $startDate = $startDate.' 00:00:00';
                $endDate = $endDate.' 23:59:59';

        }

        if ($status == ShopOrder::STATUS_COMPLETED) {
            $orders = $repo->getCompletedOrdersList(
                $startDate,
                $endDate,
                $payChannel,
                $building,
                $limit,
                $offset
            );
        } elseif ($status == ShopOrder::STATUS_REFUNDED) {
            $orders = $repo->getRushedOrdersList(
                $startDate,
                $endDate,
                $payChannel,
                $building,
                $limit,
                $offset
            );
        } else {
            $orders = null;
        }

        return new View($orders);
    }

    /**
     * Check user permission.
     *
     * @param int $OpLevel
     */
    private function checkAdminDashboardPermission(
        $OpLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_DASHBOARD,
            $OpLevel
        );
    }
}
