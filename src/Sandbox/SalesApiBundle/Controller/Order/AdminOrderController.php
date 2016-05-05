<?php

namespace Sandbox\SalesApiBundle\Controller\Order;

use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Controller\Order\OrderController;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermission;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermissionMap;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminType;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Form\Order\OrderReserveType;
use Sandbox\ApiBundle\Form\Order\PatchOrderRejectedType;
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
use Sandbox\ApiBundle\Entity\Product\Product;
use Symfony\Component\HttpFoundation\Response;
use Sandbox\ApiBundle\Traits\ProductOrderNotification;

/**
 * Admin order controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminOrderController extends OrderController
{
    use ProductOrderNotification;

    /**
     * set rejected.
     *
     * @Route("/orders/{id}/rejected")
     * @Method({"PATCH"})
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function patchRejectedAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->getOrderByIdAndStatus($id);

        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }

        $buildingId = $order->getProduct()->getRoom()->getBuildingId();

        // check user permission
        $this->checkAdminOrderPermission(
            SalesAdminPermissionMap::OP_LEVEL_EDIT,
            $buildingId
        );

        $oldRejected = $order->isRejected();

        if (!$oldRejected) {
            return new View();
        }

        // bind data
        $orderJson = $this->get('serializer')->serialize($order, 'json');
        $patch = new Patch($orderJson, $request->getContent());
        $orderJson = $patch->apply();

        $form = $this->createForm(new PatchOrderRejectedType(), $order);
        $form->submit(json_decode($orderJson, true));

        $now = new \DateTime();
        $newRejected = $order->isRejected();
        $price = $order->getDiscountPrice();
        $userId = $order->getUserId();
        $channel = $order->getPayChannel();

        if ($newRejected) {
            $order->setStatus(ProductOrder::STATUS_CANCELLED);
            $order->setCancelledDate($now);
            $order->setModificationDate($now);
            $order->setNeedToRefund(true);

            if ($price > 0) {
                if (ProductOrder::CHANNEL_ACCOUNT == $channel) {
                    $balance = $this->postBalanceChange(
                        $userId,
                        $price,
                        $order->getOrderNumber(),
                        self::PAYMENT_CHANNEL_ACCOUNT,
                        0,
                        self::ORDER_REFUND
                    );

                    if (!is_null($balance)) {
                        $order->setRefunded(true);
                    }
                } elseif (ProductOrder::CHANNEL_ALIPAY != $channel) {
                    $this->refundToPayChannel(
                        $order,
                        $price,
                        ProductOrder::PRODUCT_MAP
                    );
                }
            }

            // send message
            $this->sendXmppProductOrderNotification(
                null,
                null,
                ProductOrder::ACTION_REJECTED,
                null,
                [$order],
                ProductOrderMessage::OFFICE_REJECTED_MESSAGE
            );
        } else {
            // set door access
            $this->setDoorAccessForSingleOrder($order);

            // set invoice amount
            if (ProductOrder::STATUS_COMPLETED == $order->getStatus()
                && $price > 0
                && $channel != ProductOrder::CHANNEL_ACCOUNT
            ) {
                $amount = $this->postConsumeBalance(
                    $userId,
                    $price,
                    $order->getOrderNumber()
                );
            }

            // send message
            $this->sendXmppProductOrderNotification(
                null,
                null,
                ProductOrder::ACTION_ACCEPTED,
                null,
                [$order],
                ProductOrderMessage::OFFICE_ACCEPTED_MESSAGE
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
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
        // check if order exists
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }

        $buildingId = $order->getProduct()->getRoom()->getBuildingId();

        // check user permission
        $this->checkAdminOrderPermission(
            SalesAdminPermissionMap::OP_LEVEL_VIEW,
            $buildingId
        );

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
            $this->throwAccessDeniedIfSalesAdminNotAllowed(
                $adminId,
                SalesAdminType::KEY_PLATFORM,
                array(
                    SalesAdminPermission::KEY_BUILDING_ORDER,
                    SalesAdminPermission::KEY_BUILDING_USER,
                ),
                SalesAdminPermissionMap::OP_LEVEL_VIEW
            );
        } else {
            $this->checkAdminOrderPermission(SalesAdminPermissionMap::OP_LEVEL_VIEW);
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

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                SalesAdminPermission::KEY_BUILDING_ORDER,
            )
        );

        if (!is_null($buildingId) && !in_array((int) $buildingId, $myBuildingIds)) {
            return new View(array());
        }

        //search by name and number
        $search = $paramFetcher->get('query');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        $query = $this->getRepo('Order\ProductOrder')->getSalesOrdersForAdmin(
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
            $myBuildingIds
        );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $query,
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
        $this->checkAdminOrderPermission(
            SalesAdminPermissionMap::OP_LEVEL_VIEW,
            null,
            $adminId
        );

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

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $adminId,
            array(
                SalesAdminPermission::KEY_BUILDING_ORDER,
            )
        );

        if (!is_null($buildingId) && !in_array((int) $buildingId, $myBuildingIds)) {
            return new View(array());
        }

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        //get array of orders
        $orders = $this->getRepo('Order\ProductOrder')->getSalesOrdersToExport(
            $channel,
            $type,
            $city,
            $building,
            $userId,
            $startDate,
            $endDate,
            $payStart,
            $payEnd,
            $myBuildingIds
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

        $order = $this->getRepo('Order\ProductOrder')->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $buildingId = $order->getProduct()->getRoom()->getBuildingId();

        // check user permission
        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $adminId,
            SalesAdminType::KEY_PLATFORM,
            array(
                SalesAdminPermission::KEY_BUILDING_ORDER,
                SalesAdminPermission::KEY_BUILDING_USER,
            ),
            SalesAdminPermissionMap::OP_LEVEL_VIEW,
            $buildingId
        );

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
     */
    public function reserveRoomAction(
        Request $request
    ) {
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

            $user = $this->getRepo('User\User')->findOneByXmppUsername(User::XMPP_SERVICE);
            $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

            $productId = $order->getProductId();
            $product = $this->getRepo('Product\Product')->find($productId);
            $buildingId = $product->getRoom()->getBuildingId();

            // check user permission
            $this->throwAccessDeniedIfSalesAdminNotAllowed(
                $adminId,
                SalesAdminType::KEY_PLATFORM,
                array(
                    SalesAdminPermission::KEY_BUILDING_ORDER_RESERVE,
                ),
                SalesAdminPermissionMap::OP_LEVEL_EDIT,
                $buildingId
            );

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
     * @Route("/orders/{id}/cancel")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param $id
     */
    public function cancelAdminOrderAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }

        $buildingId = $order->getProduct()->getRoom()->getBuildingId();
        $adminId = $this->getAdminId();

        if ($adminId != $order->getAdminId()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $type = $order->getType();

        if (ProductOrder::RESERVE_TYPE == $type) {
            $permission = SalesAdminPermission::KEY_BUILDING_ORDER_RESERVE;
        } elseif (ProductOrder::PREORDER_TYPE) {
            $permission = SalesAdminPermission::KEY_BUILDING_ORDER_PREORDER;
        } else {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // check user permission
        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $adminId,
            SalesAdminType::KEY_PLATFORM,
            array(
                $permission,
            ),
            SalesAdminPermissionMap::OP_LEVEL_EDIT,
            $buildingId
        );

        $now = new \DateTime();
        $status = $order->getStatus();

        if (ProductOrder::STATUS_CANCELLED == $status || $order->getEndDate() <= $now) {
            return $this->customErrorView(
                400,
                self::WRONG_PAYMENT_STATUS_CODE,
                self::WRONG_PAYMENT_STATUS_MESSAGE
            );
        }

        if (ProductOrder::PREORDER_TYPE == $type) {
            $price = $order->getDiscountPrice();
            $channel = $order->getPayChannel();
            $userId = $order->getUserId();
            $order->setNeedToRefund(true);

            if ($price > 0) {
                if (ProductOrder::CHANNEL_ACCOUNT == $channel) {
                    $balance = $this->postBalanceChange(
                        $userId,
                        $price,
                        $order->getOrderNumber(),
                        self::PAYMENT_CHANNEL_ACCOUNT,
                        0,
                        self::ORDER_REFUND
                    );

                    if (!is_null($balance)) {
                        $order->setRefunded(true);
                    }
                } elseif (ProductOrder::CHANNEL_ALIPAY != $channel) {
                    $this->refundToPayChannel(
                        $order,
                        $price,
                        ProductOrder::PRODUCT_MAP
                    );
                }
            }

            $this->removeAccessByOrder($order);
        } else {
            $order->setStatus(ProductOrder::STATUS_CANCELLED);
            $order->setCancelledDate($now);
            $order->setModificationDate($now);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        return new View();
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
     */
    public function preorderRoomAction(
        Request $request
    ) {
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
            $buildingId = $product->getRoom()->getBuildingId();

            // check user permission
            $this->throwAccessDeniedIfSalesAdminNotAllowed(
                $adminId,
                SalesAdminType::KEY_PLATFORM,
                array(
                    SalesAdminPermission::KEY_BUILDING_ORDER_PREORDER,
                ),
                SalesAdminPermissionMap::OP_LEVEL_EDIT,
                $buildingId
            );

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
    private function authenticateAdminCookie()
    {
        $cookie_name = self::SALES_COOKIE_NAME;
        if (!isset($_COOKIE[$cookie_name])) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $token = $_COOKIE[$cookie_name];
        $adminToken = $this->getRepo('SalesAdmin\SalesAdminToken')->findOneByToken($token);
        if (is_null($adminToken)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        return $adminToken->getAdmin();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     * @param int $buildingId
     * @param int $adminId
     */
    private function checkAdminOrderPermission(
        $opLevel,
        $buildingId = null,
        $adminId = null
    ) {
        if (is_null($adminId)) {
            $adminId = $this->getAdminId();
        }

        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $adminId,
            SalesAdminType::KEY_PLATFORM,
            array(
                SalesAdminPermission::KEY_BUILDING_ORDER,
            ),
            $opLevel,
            $buildingId
        );
    }
}
