<?php

namespace Sandbox\AdminApiBundle\Controller\Order;

use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Entity\User\User;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Controller\Order\OrderController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Form\Order\OrderRefundFeePatch;
use Sandbox\ApiBundle\Form\Order\OrderRefundPatch;
use Sandbox\ApiBundle\Form\Order\OrderReserveType;
use Sandbox\ApiBundle\Form\Order\PreOrderType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sandbox\ApiBundle\Entity\Room\Room;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin order controller.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminOrderController extends OrderController
{
    /**
     * patch order refund status.
     *
     * @param Request $request
     * @param $id
     *
     * @Method({"PATCH"})
     * @Route("/orders/{id}/refund")
     *
     * @return View
     */
    public function patchOrderRefundAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_EDIT);

        $order = $this->getRepo('Order\ProductOrder')->findOneBy(
            [
                'id' => $id,
                'status' => ProductOrder::STATUS_CANCELLED,
                'needToRefund' => true,
                'refunded' => false,
                'refundProcessed' => true,
                'payChannel' => ProductOrder::CHANNEL_UNIONPAY,
            ]
        );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        // bind data
        $orderJson = $this->get('serializer')->serialize($order, 'json');
        $patch = new Patch($orderJson, $request->getContent());
        $orderJson = $patch->apply();

        $form = $this->createForm(new OrderRefundPatch(), $order);
        $form->submit(json_decode($orderJson, true));

        $refunded = $order->isRefunded();
        $view = new View();

        if (!$refunded) {
            return $view;
        }

        $ssn = $order->getRefundSSN();

        if (is_null($ssn) || empty($ssn)) {
            return $this->customErrorView(
                400,
                self::REFUND_SSN_NOT_FOUND_CODE,
                self::REFUND_SSN_NOT_FOUND_MESSAGE
            );
        }

        $order->setNeedToRefund(false);
        $order->setModificationDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $view;
    }

    /**
     * @Route("/orders/{id}/fee")
     * @Method({"GET"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getOrderRefundFeeAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_EDIT);

        $order = $this->getRepo('Order\ProductOrder')->findOneBy(
            [
                'id' => $id,
                'status' => ProductOrder::STATUS_CANCELLED,
                'needToRefund' => true,
                'refunded' => false,
                'refundProcessed' => false,
            ]
        );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $channel = $order->getPayChannel();
        $refund = (double) $order->getDiscountPrice();

        $multiplier = $this->getRefundFeeMultiplier($channel);

        $fee = $refund * $multiplier;
        $actualRefund = $refund - $fee;

        $view = new View();
        $view->setData([
            'full_refund' => $refund,
            'channel' => $channel,
            'process_fee' => $fee,
            'actual_refund' => $actualRefund,
        ]);

        return $view;
    }

    /**
     * @Route("/orders/{id}/fee")
     * @Method({"PATCH"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function storeOrderRefundFeeAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_EDIT);

        $order = $this->getRepo('Order\ProductOrder')->findOneBy(
            [
                'id' => $id,
                'status' => ProductOrder::STATUS_CANCELLED,
                'needToRefund' => true,
                'refunded' => false,
                'refundProcessed' => false,
            ]
        );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        // bind data
        $orderJson = $this->get('serializer')->serialize($order, 'json');
        $patch = new Patch($orderJson, $request->getContent());
        $orderJson = $patch->apply();

        $form = $this->createForm(new OrderRefundFeePatch(), $order);
        $form->submit(json_decode($orderJson, true));

        $price = $order->getDiscountPrice();
        $refund = $order->getActualRefundAmount();

        if ($refund > $price) {
            return $this->customErrorView(
                400,
                self::WRONG_REFUND_AMOUNT_CODE,
                self::WRONG_REFUND_AMOUNT_MESSAGE
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @Route("/orders/{id}/refund")
     * @Method({"GET"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getOrderRefundLinkAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_EDIT);

        $order = $this->getRepo('Order\ProductOrder')->findOneBy(
            [
                'id' => $id,
                'status' => ProductOrder::STATUS_CANCELLED,
                'needToRefund' => true,
                'refunded' => false,
            ]
        );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $refund = $order->getActualRefundAmount();

        if (is_null($refund) || empty($refund)) {
            return $this->customErrorView(
                400,
                self::REFUND_AMOUNT_NOT_FOUND_CODE,
                self::REFUND_AMOUNT_NOT_FOUND_MESSAGE
            );
        }

        $link = $this->checkForRefund(
            $order,
            $refund,
            ProductOrder::PRODUCT_MAP
        );

        $view = new View();
        $view->setData(['refund_link' => $link]);

        return $view;
    }

    /**
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/orders/refund")
     * @Method({"GET"})
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
     */
    public function getRefundOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_EDIT);

        $orders = $this->getRepo('Order\ProductOrder')->findBy(
            [
                'needToRefund' => true,
                'status' => ProductOrder::STATUS_CANCELLED,
                'refunded' => false,
            ],
            [
                'modificationDate' => 'ASC',
            ]
        );

        $orders = $this->get('serializer')->serialize(
            $orders,
            'json',
            SerializationContext::create()->setGroups(['admin_detail'])
        );
        $orders = json_decode($orders, true);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $orders,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @Route("/orders/maps/set")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function syncOrderMapAction(
        Request $request
    ) {
        $maps = $this->getRepo('Order\OrderMap')->findOrderMaps();

        foreach ($maps as $map) {
            $type = $map->getType();
            $orderId = $map->getOrderId();

            if (ProductOrder::PRODUCT_MAP == $type) {
                $path = ProductOrder::ENTITY_PATH;
            } elseif (ShopOrder::SHOP_MAP == $type) {
                $path = ShopOrder::ENTITY_PATH;
            } elseif (EventOrder::EVENT_MAP == $type) {
                $path = EventOrder::ENTITY_PATH;
            }

            $order = $this->getRepo($path)->find($orderId);

            $map->setOrderNumber($order->getOrderNumber());
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @Route("/orders/{id}/sync")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function syncAccessByOrderAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_VIEW);

        // check if order exists
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }

        // check if order expired
        $now = new \DateTime();
        if ($order->getEndDate() <= $now) {
            return $this->customErrorView(
                400,
                self::WRONG_ORDER_STATUS_CODE,
                self::WRONG_ORDER_STATUS_MESSAGE
            );
        }

        $base = $order->getProduct()->getRoom()->getBuilding()->getServer();
        $this->syncAccessByOrder($base, $order);

        return new Response();
    }

    /**
     * Order.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="(office|meeting|flexible|fixed)",
     *    strict=true,
     *    description="Filter by room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by city id"
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
     *    name="user",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by user id"
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
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="channel",
     *    default=null,
     *    nullable=true,
     *    description="payment channel"
     * )
     *
     * @Annotations\QueryParam(
     *    name="payStart",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment start. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="payEnd",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment end. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="orderStartPoint",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for order start point. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="orderEndPoint",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for order end point. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="refundStatus",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="refunded",
     *    strict=true,
     *    description="refund status filter for order "
     * )
     *
     * @Route("/orders")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminId = $this->getAdminId();
        $userId = $paramFetcher->get('user');

        // check user permission
        if (!is_null($userId) || !empty($userId)) {
            $this->throwAccessDeniedIfAdminNotAllowed(
                $adminId,
                AdminType::KEY_PLATFORM,
                array(
                    AdminPermission::KEY_PLATFORM_ORDER,
                    AdminPermission::KEY_PLATFORM_USER,
                ),
                AdminPermissionMap::OP_LEVEL_VIEW
            );
        } else {
            $this->checkAdminOrderPermission($adminId, AdminPermissionMap::OP_LEVEL_VIEW);
        }

        //filters
        $channel = $paramFetcher->get('channel');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $type = $paramFetcher->get('type');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $payStart = $paramFetcher->get('payStart');
        $payEnd = $paramFetcher->get('payEnd');
        $orderStartPoint = $paramFetcher->get('orderStartPoint');
        $orderEndPoint = $paramFetcher->get('orderEndPoint');
        $refundStatus = $paramFetcher->get('refundStatus');

        //search by name and number
        $search = $paramFetcher->get('query');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        $query = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersForAdmin(
                $channel,
                $type,
                $city,
                $building,
                $userId,
                $startDate,
                $endDate,
                $payStart,
                $payEnd,
                $search,
                $orderStartPoint,
                $orderEndPoint,
                $refundStatus
            );

        $orders = $this->get('serializer')->serialize(
            $query,
            'json',
            SerializationContext::create()->setGroups(['admin_detail'])
        );
        $orders = json_decode($orders, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $orders,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Export orders to excel.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="(office|meeting|flexible|fixed)",
     *    strict=true,
     *    description="Filter by room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by city id"
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
     *    name="user",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by user id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="channel",
     *    default=null,
     *    nullable=true,
     *    description="payment channel"
     * )
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    default="zh",
     *    nullable=true,
     *    requirements="(zh|en)",
     *    strict=true,
     *    description="export language"
     * )
     *
     * @Annotations\QueryParam(
     *    name="payStart",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment start. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="payEnd",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment end. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="orderStartPoint",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for order start point. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="orderEndPoint",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for order end point. Must be YYYY-mm-dd"
     * )
     *
     * @Route("/orders/export")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getExcelOrders(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //authenticate with web browser cookie
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();

        // check user permission
        $this->checkAdminOrderPermission($adminId, AdminPermissionMap::OP_LEVEL_VIEW);

        $language = $paramFetcher->get('language');
        $channel = $paramFetcher->get('channel');
        $type = $paramFetcher->get('type');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $userId = $paramFetcher->get('user');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $payStart = $paramFetcher->get('payStart');
        $payEnd = $paramFetcher->get('payEnd');
        $orderStartPoint = $paramFetcher->get('orderStartPoint');
        $orderEndPoint = $paramFetcher->get('orderEndPoint');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        //get array of orders
        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersToExport(
                $channel,
                $type,
                $city,
                $building,
                $userId,
                $startDate,
                $endDate,
                $payStart,
                $payEnd,
                $orderStartPoint,
                $orderEndPoint
            );

        return $this->getProductOrderExport($orders, $language);
    }

    /**
     * Get member order renter info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/orders/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getOrderByIdAction(
        Request $request,
        $id
    ) {
        $adminId = $this->getAdminId();

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            AdminType::KEY_PLATFORM,
            array(
                AdminPermission::KEY_PLATFORM_ORDER,
                AdminPermission::KEY_PLATFORM_USER,
                AdminPermission::KEY_PLATFORM_FINANCE,
            ),
            AdminPermissionMap::OP_LEVEL_VIEW
        );

        $order = $this->getRepo('Order\ProductOrder')->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['admin_detail'])
        );
        $view->setData($order);

        return $view;
    }

    /**
     * Reserve order.
     *
     * @Route("/orders/reserve")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws \Exception
     */
    public function reserveRoomAction(
        Request $request
    ) {
        $adminId = $this->getAdminId();

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            AdminType::KEY_PLATFORM,
            array(
                AdminPermission::KEY_PLATFORM_ORDER_RESERVE,
            ),
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        $now = new \DateTime();
        $adminId = $this->getAdminId();
        $orderCheck = null;

        $em = $this->getDoctrine()->getManager();

        try {
            $order = new ProductOrder();

            $form = $this->createForm(new OrderReserveType(), $order);
            $form->handleRequest($request);

            if (!$form->isValid()) {
                return $this->customErrorView(
                    400,
                    self::INVALID_FORM_CODE,
                    self::INVALID_FORM_MESSAGE
                );
            }

            $user = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->findOneBy(array(
                    'xmppUsername' => User::XMPP_SERVICE,
                ));
            $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

            $productId = $order->getProductId();
            $product = $this->getRepo('Product\Product')->find($productId);

            $startDate = new \DateTime($order->getStartDate());

            // check product
            $error = $this->checkIfProductAvailable(
                $product,
                $now,
                $startDate
            );

            if (!empty($error)) {
                return $this->customErrorView(
                    400,
                    $error['code'],
                    $error['message']
                );
            }

            $timeUnit = $product->getUnitPrice();
            $period = $order->getRentPeriod();

            // get endDate
            $endDate = $this->getOrderEndDate(
                $period,
                $timeUnit,
                $startDate
            );

            // check booking dates and order duplication
            $type = $product->getRoom()->getType();
            $error = $this->checkIfOrderAllowed(
                $em,
                $order,
                $product,
                $productId,
                $now,
                $startDate,
                $endDate,
                $user,
                $type
            );

            if (!empty($error)) {
                return $this->customErrorView(
                    400,
                    $error['code'],
                    $error['message']
                );
            }

            $order->setStatus(ProductOrder::STATUS_PAID);
            $order->setAdminId($adminId);
            $order->setPaymentDate($now);
            $order->setType(ProductOrder::RESERVE_TYPE);
            $order->setPrice(0);
            $order->setDiscountPrice(0);
            $order->setUser($user);

            $em->persist($order);

            // store order record
            $this->storeRoomRecord(
                $em,
                $order,
                $product
            );

            $em->flush();

            $view = new View();
            $view->setData(
                ['order_id' => $order->getId()]
            );

            return $view;
        } catch (\Exception $exception) {
            if (!is_null($orderCheck)) {
                $em->remove($orderCheck);
                $em->flush();
            }

            throw $exception;
        }
    }

    /**
     * pre-order room.
     *
     * @Route("/orders/preorder")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws \Exception
     */
    public function preorderRoomAction(
        Request $request
    ) {
        $adminId = $this->getAdminId();

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            AdminType::KEY_PLATFORM,
            array(
                AdminPermission::KEY_PLATFORM_ORDER_PREORDER,
            ),
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        $now = new \DateTime();
        $adminId = $this->getAdminId();
        $orderCheck = null;

        $em = $this->getDoctrine()->getManager();

        try {
            $order = new ProductOrder();

            $form = $this->createForm(new PreOrderType(), $order);
            $form->handleRequest($request);

            if (!$form->isValid()) {
                return $this->customErrorView(
                    400,
                    self::INVALID_FORM_CODE,
                    self::INVALID_FORM_MESSAGE
                );
            }

            $user = $this->getRepo('User\User')->find($order->getUserId());
            $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

            $productId = $order->getProductId();
            $product = $this->getRepo('Product\Product')->find($productId);

            $startDate = new \DateTime($order->getStartDate());

            // check product
            $error = $this->checkIfProductAvailable(
                $product,
                $now,
                $startDate
            );

            if (!empty($error)) {
                return $this->customErrorView(
                    400,
                    $error['code'],
                    $error['message']
                );
            }

            $timeUnit = $product->getUnitPrice();
            $period = $order->getRentPeriod();

            // get endDate
            $endDate = $this->getOrderEndDate(
                $period,
                $timeUnit,
                $startDate
            );

            // check if price match
            $basePrice = $product->getBasePrice();
            $calculatedPrice = $basePrice * $period;

            if ($order->getPrice() != $calculatedPrice) {
                return $this->customErrorView(
                    400,
                    self::PRICE_MISMATCH_CODE,
                    self::PRICE_MISMATCH_MESSAGE
                );
            }

            // check booking dates and order duplication
            $type = $product->getRoom()->getType();
            $error = $this->checkIfOrderAllowed(
                $em,
                $order,
                $product,
                $productId,
                $now,
                $startDate,
                $endDate,
                $user,
                $type
            );

            if (!empty($error)) {
                return $this->customErrorView(
                    400,
                    $error['code'],
                    $error['message']
                );
            }

            // check for discount rule and price
            $ruleId = $order->getRuleId();

            if (!is_null($ruleId) && !empty($ruleId)) {
                $result = $this->getSalesPriceRuleForOrder($ruleId);

                if (is_null($result)) {
                    return $this->customErrorView(
                        400,
                        self::PRICE_RULE_DOES_NOT_EXIST_CODE,
                        self::PRICE_RULE_DOES_NOT_EXIST_MESSAGE
                    );
                }

                if (array_key_exists('rule_name', $result)) {
                    $order->setRuleName($result['rule_name']);
                }

                if (array_key_exists('rule_description', $result)) {
                    $order->setRuleDescription($result['rule_description']);
                }
            }

            $order->setAdminId($adminId);
            $order->setType(ProductOrder::PREORDER_TYPE);

            if (0 == $order->getDiscountPrice()) {
                $order->setStatus(ProductOrder::STATUS_PAID);
                $order->setPaymentDate($now);
            }

            $em->persist($order);

            // store order record
            $this->storeRoomRecord(
                $em,
                $order,
                $product
            );

            // set sales user
            $this->setSalesUser(
                $em,
                $user->getId(),
                $product
            );

            $em->flush();

            // set door access
            if (0 == $order->getDiscountPrice()) {
                $this->setDoorAccessForSingleOrder($order);
            }

            $view = new View();
            $view->setData(
                ['order_id' => $order->getId()]
            );

            return $view;
        } catch (\Exception $exception) {
            if (!is_null($orderCheck)) {
                $em->remove($orderCheck);
                $em->flush();
            }

            throw $exception;
        }
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
        $adminToken = $this->getRepo('Admin\AdminToken')->findOneByToken($token);
        if (is_null($adminToken)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        return $adminToken->getAdmin();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     * @param int $adminId
     */
    private function checkAdminOrderPermission(
        $adminId,
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_ORDER,
            $opLevel
        );
    }
}
