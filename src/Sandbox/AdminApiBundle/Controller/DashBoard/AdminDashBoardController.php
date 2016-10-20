<?php

namespace Sandbox\AdminApiBundle\Controller\DashBoard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="year",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="filter for order start point. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="month",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="filter for order end point. Must be YYYY-mm-dd"
     * )
     *
     * @Route("/dashboard/orders/export")
     * @Method({"GET"})
     *
     * @return View
     */
    public function exportOrderSumAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //authenticate with web browser cookie
        //$admin = $this->authenticateAdminCookie();

        $channels = array(
            'wx',
            'alipay',
            'upacp',
            'account',
            'offline',
        );

        $year = $paramFetcher->get('year');
        $month = $paramFetcher->get('month');

        if (is_null($year) ||
            is_null($month) ||
            empty($year) ||
            empty($month)
        ) {
            return new View();
        }

        $startString = $year.'-'.$month.'-01';
        $startDate = new \DateTime($startString);
        $startDate->setTime(0, 0, 0);

        //$endDate = clone $startDate;
        $endString = $startDate->format('Y-m-t');
        $endDate = new \DateTime($endString);
        $endDate->setTime(23, 59, 59);

        $roomTypes = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomTypes')
            ->findAll();

        $sales = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findAll();

        $data = array();

        foreach ($channels as $channel) {
            $companyArray = array();
            $shopArray = array();

            foreach ($sales as $sale) {
                $salesName = $sale->getName();
                $salesId = $sale->getId();

                $typeArray = array();

                foreach ($roomTypes as $roomType) {
                    $typeName = $roomType->getName();

                    $completedSum = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Order\ProductOrder')
                        ->sumOrdersByType(
                            $channel,
                            $salesId,
                            $typeName,
                            $startDate,
                            $endDate,
                            ProductOrder::STATUS_COMPLETED
                        );

                    if (is_null($completedSum)) {
                        $completedSum = '0.00';
                    }

                    $paidSum = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Order\ProductOrder')
                        ->sumOrdersByType(
                            $channel,
                            $salesId,
                            $typeName,
                            $startDate,
                            $endDate,
                            ProductOrder::STATUS_PAID
                        );

                    if (is_null($paidSum)) {
                        $paidSum = '0.00';
                    }

                    $sumArray = array(
                        'type_name' => $typeName,
                        'completed' => $completedSum,
                        'paid' => $paidSum,
                    );

                    array_push($typeArray, $sumArray);
                }

                $companies = array(
                    'company_name' => $salesName,
                    'room_type' => $typeArray,
                );

                array_push($companyArray, $companies);
            }

            $shops = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Shop\Shop')
                ->findAll();

            foreach ($shops as $shop) {
                $paid = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Shop\ShopOrder')
                    ->getOrderPaidSums(
                        $shop,
                        $channel,
                        $startDate,
                        $endDate
                    );

                $refund = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Shop\ShopOrder')
                    ->getOrderRefundSums(
                        $shop,
                        $channel,
                        $startDate,
                        $endDate
                    );

                $sums = array(
                    'shop_name' => $shop->getName(),
                    'completed' => $paid,
                    'refund' => $refund,
                );

                array_push($shopArray, $sums);
            }

            $channelArray = array(
                'channel_name' => $channel,
                'company' => $companyArray,
                'shop' => $shopArray,
            );

            array_push($data, $channelArray);
        }

        return $this->getOrderSumExport(
            $data,
            $startString,
            $endString
        );
    }

    /**
     * @param $url
     *
     * @return mixed|void
     */
    private function getBalanceInfo(
        $url
    ) {
        // init curl
        $ch = curl_init($url);

        $response = $this->callAPI(
            $ch,
            'GET'
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $dataArray
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws \PHPExcel_Exception
     */
    private function getOrderSumExport(
        $dataArray,
        $startString,
        $endString
    ) {
        $title = $startString.'_'.$endString.'_Sandbox3_Financial_Report';

        $phpExcelObject = new \PHPExcel();
        $x = 0;

        $globals = $this->getGlobals();
        $url = $globals['crm_api_url']."/admin/dashboard/balance/export?startDate=$startString&endDate=$endString&channel=";

        foreach ($dataArray as $data) {
            if ($x > 0) {
                $phpExcelObject->createSheet($x);
                $phpExcelObject->setActiveSheetIndex($x);
            }
            ++$x;

            $channel = $this->get('translator')
                ->trans(
                    ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$data['channel_name'],
                    array(),
                    null,
                    'zh'
                );

            $phpExcelObject->getActiveSheet()->setTitle($channel);
            $phpExcelObject->getActiveSheet()->setCellValue('A1', '支付渠道');
            $phpExcelObject->getActiveSheet()->setCellValue('B1', $channel);
            $phpExcelObject->getActiveSheet()
                ->getStyle('A1:B1')
                ->getFill()
                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('ADD8E6');

            $roomCompleted = $this->setRoomTables(
                $phpExcelObject,
                $data,
                '已完成订单',
                'FFC0CB',
                'completed'
            );

            $roomPaid = $this->setRoomTables(
                $phpExcelObject,
                $data,
                '已付款订单',
                '90EE90',
                'paid'
            );

            $shopSum = $this->setShopTables(
                $phpExcelObject,
                $data,
                '店铺订单',
                'FFFF00'
            );

            $result = $this->getBalanceInfo($url.$data['channel_name']);

            $this->setTotalTables(
                $phpExcelObject,
                $result,
                $roomCompleted,
                $roomPaid,
                $shopSum
            );

            $phpExcelObject->getActiveSheet()->getSheetView()->setZoomScale(120);
            $phpExcelObject->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        }

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $title.'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @param \PHPExcel() $phpExcelObject
     * @param $data
     */
    private function setTotalTables(
        $phpExcelObject,
        $data,
        $roomCompleted,
        $roomPaid,
        $shopSum
    ) {
        $currentRow = $phpExcelObject->getActiveSheet()->getHighestRow();
        $firstRow = $currentRow + 3;
        $nextRow = $firstRow + 1;
        $lastRow = $nextRow + 1;

        $roomCompletedTaxFree = round($roomCompleted / 1.06, 2);
        $roomPaidTaxFree = round($roomPaid / 1.06, 2);
        $shopSumTaxFree = round($shopSum / 1.06, 2);
        $topUp = round($data['top_up'], 2);
        $topUpTaxFree = round($topUp / 1.06, 2);
        $sum = $roomCompleted + $roomPaid + $shopSum + $topUp;
        $sumTaxFree = $roomCompletedTaxFree + $roomPaidTaxFree + $shopSumTaxFree + $topUpTaxFree;

        $phpExcelObject->getActiveSheet()->setCellValue("B$firstRow", '已完成房间订单');
        $phpExcelObject->getActiveSheet()->setCellValue("B$nextRow", $roomCompleted);
        $phpExcelObject->getActiveSheet()->setCellValue("B$lastRow", $roomCompletedTaxFree);

        $phpExcelObject->getActiveSheet()->setCellValue("C$firstRow", '已付款房间订单');
        $phpExcelObject->getActiveSheet()->setCellValue("C$nextRow", $roomPaid);
        $phpExcelObject->getActiveSheet()->setCellValue("C$lastRow", $roomPaidTaxFree);

        $phpExcelObject->getActiveSheet()->setCellValue("D$firstRow", '店铺订单');
        $phpExcelObject->getActiveSheet()->setCellValue("D$nextRow", $shopSum);
        $phpExcelObject->getActiveSheet()->setCellValue("D$lastRow", $shopSumTaxFree);

        $phpExcelObject->getActiveSheet()->setCellValue("E$firstRow", '余额充值');
        $phpExcelObject->getActiveSheet()->setCellValue("E$nextRow", $topUp);
        $phpExcelObject->getActiveSheet()->setCellValue("E$lastRow", $topUpTaxFree);

        $phpExcelObject->getActiveSheet()->setCellValue("F$firstRow", '上月余额');
        $phpExcelObject->getActiveSheet()->setCellValue("F$nextRow", round($data['previous_total_balance'], 2));

        $phpExcelObject->getActiveSheet()->setCellValue("G$firstRow", '本月余额');
        $phpExcelObject->getActiveSheet()->setCellValue("G$nextRow", round($data['latest_total_balance'], 2));

        $phpExcelObject->getActiveSheet()->setCellValue("A$nextRow", "合计金额(含税)= $sum");
        $phpExcelObject->getActiveSheet()->setCellValue("A$lastRow", "合计金额(未税)= $sumTaxFree");

        $phpExcelObject->getActiveSheet()
            ->getStyle("B$firstRow:G$firstRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpExcelObject->getActiveSheet()
            ->getStyle("B$nextRow:G$nextRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpExcelObject->getActiveSheet()
            ->getStyle("B$lastRow:G$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$nextRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:G$firstRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$nextRow:G$nextRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$lastRow:G$lastRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
    }

    /**
     * @param \PHPExcel() $phpExcelObject
     * @param $data
     * @param $header
     * @param $color
     */
    private function setShopTables(
        $phpExcelObject,
        $data,
        $header,
        $color
    ) {
        $currentRow = $phpExcelObject->getActiveSheet()->getHighestRow();
        $firstRow = $currentRow + 3;
        $startRow = $firstRow + 1;

        $phpExcelObject->getActiveSheet()->setCellValue("A$firstRow", $header);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow")
            ->getFill()
            ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB($color);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow")
            ->getBorders()
            ->getRight()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $phpExcelObject->getActiveSheet()->setCellValue("B$firstRow", '订单价格');
        $phpExcelObject->getActiveSheet()->setCellValue("C$firstRow", '退款金额');
        $phpExcelObject->getActiveSheet()->setCellValue("D$firstRow", '最终收入');
        $phpExcelObject->getActiveSheet()->setCellValue("E$firstRow", '未税金额');

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:E$firstRow")
            ->getBorders()
            ->getTop()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:E$firstRow")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:E$firstRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:E$firstRow")
            ->getBorders()
            ->getRight()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $paidAmountSum = 0;
        $refundAmountSum = 0;
        $actualAmountSum = 0;
        $amountTaxFreeSum = 0;

        foreach ($data['shop'] as $shopItem) {
            $column = 'A';

            $name = $shopItem['shop_name'];
            $paidAmount = round($shopItem['completed'], 2);
            $paidAmountSum += $paidAmount;

            $refundAmount = round($shopItem['refund'], 2);
            $refundAmountSum += $refundAmount;

            $actualAmount = $paidAmount - $refundAmount;
            $actualAmountSum += $actualAmount;

            $amountTaxFree = round($actualAmount / 1.06, 2);
            $amountTaxFreeSum += $amountTaxFree;

            $phpExcelObject->getActiveSheet()->setCellValue($column."$startRow", $name);
            $phpExcelObject->getActiveSheet()
                ->getStyle($column."$startRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $phpExcelObject->getActiveSheet()
                ->getStyle($column."$startRow")
                ->getBorders()
                ->getRight()
                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

            ++$column;
            $phpExcelObject->getActiveSheet()->setCellValue($column."$startRow", $paidAmount);
            $phpExcelObject->getActiveSheet()
                ->getStyle($column."$startRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            ++$column;
            $phpExcelObject->getActiveSheet()->setCellValue($column."$startRow", $refundAmount);
            $phpExcelObject->getActiveSheet()
                ->getStyle($column."$startRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            ++$column;
            $phpExcelObject->getActiveSheet()->setCellValue($column."$startRow", $actualAmount);
            $phpExcelObject->getActiveSheet()
                ->getStyle($column."$startRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            ++$column;
            $phpExcelObject->getActiveSheet()->setCellValue($column."$startRow", $amountTaxFree);
            $phpExcelObject->getActiveSheet()
                ->getStyle($column."$startRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $phpExcelObject->getActiveSheet()
                ->getStyle("A$startRow:".$column."$startRow")
                ->getBorders()
                ->getRight()
                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        }

        $column = 'A';

        $lastRow = $phpExcelObject->getActiveSheet()->getHighestRow() + 1;
        $phpExcelObject->getActiveSheet()->setCellValue($column."$lastRow", '总计');
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getFont()
            ->setBold(true);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getBorders()
            ->getRight()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        ++$column;
        $phpExcelObject->getActiveSheet()->setCellValue($column."$lastRow", $paidAmountSum);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        ++$column;
        $phpExcelObject->getActiveSheet()->setCellValue($column."$lastRow", $refundAmountSum);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        ++$column;
        $phpExcelObject->getActiveSheet()->setCellValue($column."$lastRow", $actualAmountSum);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        ++$column;
        $phpExcelObject->getActiveSheet()->setCellValue($column."$lastRow", $amountTaxFreeSum);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getBorders()
            ->getRight()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$lastRow:".$column."$lastRow")
            ->getBorders()
            ->getTop()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$lastRow:".$column."$lastRow")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        return $actualAmountSum;
    }

    /**
     * @param \PHPExcel() $phpExcelObject
     * @param $data
     * @param $header
     * @param $color
     * @param $payStatus
     */
    private function setRoomTables(
        $phpExcelObject,
        $data,
        $header,
        $color,
        $payStatus
    ) {
        $currentRow = $phpExcelObject->getActiveSheet()->getHighestRow();
        $firstRow = $currentRow + 3;
        $secondRow = $firstRow + 1;
        $thirdRow = $secondRow + 1;
        $fourthRow = $thirdRow + 1;
        $startRow = $fourthRow;
        $total = 0;

        $phpExcelObject->getActiveSheet()->setCellValue("A$firstRow", $header);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow")
            ->getFill()
            ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB($color);

        $phpExcelObject->getActiveSheet()->setCellValue("A$thirdRow", '社区名称');
        $phpExcelObject->getActiveSheet()->mergeCells("A$firstRow:A$secondRow");

        $y = 0;

        foreach ($data['company'] as $companyItem) {
            $column = 'A';
            $companySum = 0;
            $companySumTaxFree = 0;

            foreach ($companyItem['room_type'] as $roomType) {
                ++$column;
                $nextColumn = $column;
                ++$nextColumn;

                if ($y == 0) {
                    $typeText = $this->get('translator')->trans(
                        ProductOrderExport::TRANS_ROOM_TYPE.$roomType['type_name'],
                        array(),
                        null,
                        'zh'
                    );

                    $phpExcelObject->getActiveSheet()->setCellValue($column."$secondRow", $typeText);
                    $phpExcelObject->getActiveSheet()->mergeCells($column."$secondRow:".$nextColumn."$secondRow");

                    $phpExcelObject->getActiveSheet()->setCellValue($column."$thirdRow", '实收款');
                    $phpExcelObject->getActiveSheet()->setCellValue($nextColumn."$thirdRow", '未税金额');
                }

                $amount = round($roomType[$payStatus], 2);
                $companySum += $amount;
                $amountTaxFree = round($roomType[$payStatus] / 1.06, 2);
                $companySumTaxFree += $amountTaxFree;

                $phpExcelObject->getActiveSheet()->setCellValue($column."$startRow", $amount);
                $phpExcelObject->getActiveSheet()
                    ->getStyle($column."$startRow")
                    ->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $phpExcelObject->getActiveSheet()
                    ->getStyle($column."$startRow")
                    ->getBorders()
                    ->getLeft()
                    ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

                $phpExcelObject->getActiveSheet()->setCellValue($nextColumn."$startRow", $amountTaxFree);
                $phpExcelObject->getActiveSheet()
                    ->getStyle($nextColumn."$startRow")
                    ->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                ++$column;
            }

            $y = 1;

            $currentRow = $phpExcelObject->getActiveSheet()->getHighestRow();
            $currentColumn = $phpExcelObject->getActiveSheet()->getHighestColumn($currentRow);
            ++$currentColumn;
            $afterColumn = $currentColumn;
            ++$afterColumn;

            $phpExcelObject->getActiveSheet()->setCellValue($currentColumn."$currentRow", $companySum);
            $phpExcelObject->getActiveSheet()
                ->getStyle($currentColumn."$currentRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $phpExcelObject->getActiveSheet()
                ->getStyle($currentColumn."$currentRow")
                ->getBorders()
                ->getLeft()
                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $phpExcelObject->getActiveSheet()
                ->getStyle($currentColumn."$currentRow")
                ->getBorders()
                ->getRight()
                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

            $phpExcelObject->getActiveSheet()->setCellValue($afterColumn."$currentRow", $companySumTaxFree);
            $phpExcelObject->getActiveSheet()
                ->getStyle($afterColumn."$currentRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $phpExcelObject->getActiveSheet()
                ->getStyle($afterColumn."$currentRow")
                ->getBorders()
                ->getRight()
                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

            $phpExcelObject->getActiveSheet()->setCellValue("A$currentRow", $companyItem['company_name']);
            $phpExcelObject->getActiveSheet()
                ->getStyle("A$currentRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            ++$startRow;
        }

        $phpExcelObject->getActiveSheet()->setCellValue("B$firstRow", '房间类型');
        $toColumn = $phpExcelObject->getActiveSheet()->getHighestColumn($secondRow);
        $phpExcelObject->getActiveSheet()->mergeCells("B$firstRow:".$toColumn."$firstRow");

        ++$toColumn;
        $nextColumn = $toColumn;
        ++$nextColumn;

        $phpExcelObject->getActiveSheet()->setCellValue($toColumn."$firstRow", '合计');
        $phpExcelObject->getActiveSheet()->mergeCells($toColumn."$firstRow:".$nextColumn."$firstRow");

        $phpExcelObject->getActiveSheet()->setCellValue($toColumn."$secondRow", '实收款总汇');
        $phpExcelObject->getActiveSheet()->mergeCells($toColumn."$secondRow:".$toColumn."$thirdRow");

        $phpExcelObject->getActiveSheet()->setCellValue($nextColumn."$secondRow", '未税金额总汇');
        $phpExcelObject->getActiveSheet()->mergeCells($nextColumn."$secondRow:".$nextColumn."$thirdRow");

        $currentRow = $phpExcelObject->getActiveSheet()->getHighestRow() + 1;
        $phpExcelObject->getActiveSheet()->setCellValue("A$currentRow", '总计');
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$currentRow")
            ->getFont()
            ->setBold(true);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$currentRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$currentRow")
            ->getBorders()
            ->getRight()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:".$nextColumn."$firstRow")
            ->getFont()
            ->setBold(true);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:".$nextColumn."$firstRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$secondRow:".$nextColumn."$secondRow")
            ->getFont()
            ->setBold(true);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$secondRow:".$nextColumn."$secondRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$thirdRow:".$nextColumn."$thirdRow")
            ->getFont()
            ->setBold(true);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$thirdRow:".$nextColumn."$thirdRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $count = 1;

        for ($startColumn = 'B'; $startColumn <= $nextColumn; ++$startColumn) {
            $sum = 0;

            for ($i = $fourthRow; $i < $currentRow; ++$i) {
                $value = $phpExcelObject->getActiveSheet()->getCell($startColumn."$i")->getValue();

                $sum += $value;
            }

            $phpExcelObject->getActiveSheet()->setCellValue($startColumn."$currentRow", $sum);

            if ($count % 2 == 0) {
                $phpExcelObject->getActiveSheet()
                    ->getStyle($startColumn."$currentRow")
                    ->getBorders()
                    ->getRight()
                    ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            }

            ++$count;

            if ($startColumn < $nextColumn) {
                $total = $sum;
            }
        }

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:".$nextColumn."$firstRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$secondRow:".$nextColumn."$secondRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$thirdRow:".$nextColumn."$thirdRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$currentRow:".$nextColumn."$currentRow")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$currentRow:".$nextColumn."$currentRow")
            ->getBorders()
            ->getTop()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle($nextColumn."$currentRow")
            ->getBorders()
            ->getLeft()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        return $total;
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
        $adminToken = $this->getRepo('User\UserToken')->findOneByToken($token);
        if (is_null($adminToken)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        return $adminToken->getUser();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminDashboardPermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_DASHBOARD],
            ],
            $opLevel
        );
    }
}