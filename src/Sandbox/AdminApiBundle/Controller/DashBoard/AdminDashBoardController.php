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
        $this->checkAdminDashboardPermission(AdminPermission::OP_LEVEL_VIEW);

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
        $this->checkAdminDashboardPermission(AdminPermission::OP_LEVEL_VIEW);

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
     * @Annotations\QueryParam(
     *    name="companyId",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="companyId"
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
        $this->checkAdminDashboardPermission(AdminPermission::OP_LEVEL_VIEW);

        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $buildingId = $paramFetcher->get('buildingId');
        $companyId = $paramFetcher->get('companyId');
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
            $building,
            $companyId
        );

        $todayRefunded = $repo->countRefundOrders(
            $now->format('Y-m-d 00:00:00'),
            $now->format('Y-m-d 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $yestCompleted = $repo->countCompletedOrders(
            $yesterday->format('Y-m-d 00:00:00'),
            $yesterday->format('Y-m-d 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $yestRefunded = $repo->countRefundOrders(
            $yesterday->format('Y-m-d 00:00:00'),
            $yesterday->format('Y-m-d 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $monthCompleted = $repo->countCompletedOrders(
            $now->format('Y-m-01 00:00:00'),
            $now->format('Y-m-31 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $monthRefunded = $repo->countRefundOrders(
            $now->format('Y-m-01 00:00:00'),
            $now->format('Y-m-31 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $monthlyCompleted = $repo->countCompletedOrders(
            $startDate.' 00:00:00',
            $endDate.' 23:59:59',
            $payChannel,
            $building,
            $companyId
        );

        $monthlyRefunded = $repo->countRefundOrders(
            $startDate.' 00:00:00',
            $endDate.' 23:59:59',
            $payChannel,
            $building,
            $companyId
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
     *    name="companyId",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="companyId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many orders to return "
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
     * @Route("/dashboard/shop/orders/list")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getShopOrdersList(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminDashboardPermission(AdminPermission::OP_LEVEL_VIEW);

        $type = $paramFetcher->get('type');
        $status = $paramFetcher->get('status');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $buildingId = $paramFetcher->get('buildingId');
        $companyId = $paramFetcher->get('companyId');
        $payChannel = $paramFetcher->get('payChannel');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

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
                $companyId,
                $limit,
                $offset
            );
            $count = $repo->countCompletedOrdersList(
                $startDate,
                $endDate,
                $payChannel,
                $building,
                $companyId
            );
        } elseif ($status == ShopOrder::STATUS_REFUNDED) {
            $orders = $repo->getRefundedOrdersList(
                $startDate,
                $endDate,
                $payChannel,
                $building,
                $companyId,
                $limit,
                $offset
            );
            $count = $repo->countRefundedOrdersList(
                $startDate,
                $endDate,
                $payChannel,
                $building,
                $companyId
            );
        } else {
            $orders = null;
            $count = 0;
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $orders,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * Get Product Orders.
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
     *    name="companyId",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="companyId"
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
     * @Route("/dashboard/product/orders")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getProductOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminDashboardPermission(AdminPermission::OP_LEVEL_VIEW);

        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $buildingId = $paramFetcher->get('buildingId');
        $companyId = $paramFetcher->get('companyId');
        $payChannel = $paramFetcher->get('payChannel');
        $now = new \DateTime('now');
        $yesterday = new \DateTime('now');
        $yesterday = $yesterday->modify('-1 day');

        $building = !is_null($buildingId) ? $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId) : null;

        $repo = $this->getDoctrine()->getRepository('SandboxApiBundle:Order\ProductOrder');
        $todayPaid = $repo->countPaidOrders(
            $now->format('Y-m-d 00:00:00'),
            $now->format('Y-m-d 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $todayCompleted = $repo->countCompletedOrders(
            $now->format('Y-m-d 00:00:00'),
            $now->format('Y-m-d 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $todayRefunded = $repo->countRefundOrders(
            $now->format('Y-m-d 00:00:00'),
            $now->format('Y-m-d 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $yestPaid = $repo->countPaidOrders(
            $yesterday->format('Y-m-d 00:00:00'),
            $yesterday->format('Y-m-d 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $yestCompleted = $repo->countCompletedOrders(
            $yesterday->format('Y-m-d 00:00:00'),
            $yesterday->format('Y-m-d 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $yestRefunded = $repo->countRefundOrders(
            $yesterday->format('Y-m-d 00:00:00'),
            $yesterday->format('Y-m-d 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $monthPaid = $repo->countPaidOrders(
            $now->format('Y-m-01 00:00:00'),
            $now->format('Y-m-31 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $monthCompleted = $repo->countCompletedOrders(
            $now->format('Y-m-01 00:00:00'),
            $now->format('Y-m-31 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $monthRefunded = $repo->countRefundOrders(
            $now->format('Y-m-01 00:00:00'),
            $now->format('Y-m-31 23:59:59'),
            $payChannel,
            $building,
            $companyId
        );

        $monthlyPaid = $repo->countPaidOrders(
            $startDate.' 00:00:00',
            $endDate.' 23:59:59',
            $payChannel,
            $building,
            $companyId
        );

        $monthlyCompleted = $repo->countCompletedOrders(
            $startDate.' 00:00:00',
            $endDate.' 23:59:59',
            $payChannel,
            $building,
            $companyId
        );

        $monthlyRefunded = $repo->countRefundOrders(
            $startDate.' 00:00:00',
            $endDate.' 23:59:59',
            $payChannel,
            $building,
            $companyId
        );

        $result = array(
            'today_paid' => $todayPaid,
            'today_completed' => $todayCompleted,
            'today_cancelled' => $todayRefunded,
            'yestday_paid' => $yestPaid,
            'yestday_completed' => $yestCompleted,
            'yestday_cancelled' => $yestRefunded,
            'month_paid' => $monthPaid,
            'month_completed' => $monthCompleted,
            'month_cancelled' => $monthRefunded,
            'monthly_paid' => $monthlyPaid,
            'monthly_completed' => $monthlyCompleted,
            'monthly_cancelled' => $monthlyRefunded,
        );

        return new View($result);
    }

    /**
     * Get Product Orders List.
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
     *    description="new|completed|refunded"
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
     *    name="companyId",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="companyId"
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
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many orders to return "
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
     * @Route("/dashboard/product/orders/list")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getProductOrdersList(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //check user permission
        $this->checkAdminDashboardPermission(AdminPermission::OP_LEVEL_VIEW);

        $type = $paramFetcher->get('type');
        $status = $paramFetcher->get('status');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $buildingId = $paramFetcher->get('buildingId');
        $companyId = $paramFetcher->get('companyId');
        $payChannel = $paramFetcher->get('payChannel');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

        $building = !is_null($buildingId) ? $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId) : null;

        $repo = $this->getDoctrine()->getRepository('SandboxApiBundle:Order\ProductOrder');

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

        if (!is_null($status)) {
            $orders = $repo->getOrdersList(
                $status,
                $startDate,
                $endDate,
                $payChannel,
                $building,
                $companyId,
                $limit,
                $offset
            );
            $count = $repo->countOrdersList(
                $status,
                $startDate,
                $endDate,
                $payChannel,
                $building,
                $companyId
            );
        } else {
            $orders = null;
            $count = 0;
        }
        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $orders,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminDashboardPermission(
        $opLevel,
        $adminId = null
    ) {
        if (is_null($adminId)) {
            $adminId = $this->getAdminId();
        }

        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_DASHBOARD],
            ],
            $opLevel
        );
    }
}
