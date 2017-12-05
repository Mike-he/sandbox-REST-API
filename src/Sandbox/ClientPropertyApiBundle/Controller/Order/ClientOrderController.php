<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Order;

use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Controller\Order\OrderController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Order\OrderOfflineTransfer;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Form\Order\OrderReserveType;
use Sandbox\ApiBundle\Form\Order\PreOrderPriceType;
use Sandbox\ApiBundle\Form\Order\PreOrderType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Product\Product;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Constants\ProductOrderExport;

class ClientOrderController extends OrderController
{
    /**
     * Order.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="search query"
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
     *    name="building",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="channel",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="payment channel"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Order Status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="order_type",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Order Type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="rent_filter",
     *    default=null,
     *    nullable=true,
     *    description="rent filter"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="end_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pay_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment start. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pay_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment start. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="pay_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment end. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="product",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by product id"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="create_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="create_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="all_order",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter get All Order Data"
     * )
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for the page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="start of the page"
     * )
     *
     * @Route("/products")
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
        //filters
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        $type = $paramFetcher->get('type');
        $channel = $paramFetcher->get('channel');
        $status = $paramFetcher->get('status');
        $orderType = $paramFetcher->get('order_type');
        $buildingIds = $paramFetcher->get('building');
        $productId = $paramFetcher->get('product');

        $rentFilter = $paramFetcher->get('rent_filter');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        $payDate = $paramFetcher->get('pay_date');
        $payStart = $paramFetcher->get('pay_start');
        $payEnd = $paramFetcher->get('pay_end');

        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');

        $allOrder= $paramFetcher->get('all_order');

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        //get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_ORDER,
            )
        );

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getSalesOrdersForAdmin(
                $allOrder,
                $channel,
                $type,
                null,
                $buildingIds,
                null,
                $rentFilter,
                $startDate,
                $endDate,
                $payDate,
                $payStart,
                $payEnd,
                $keyword,
                $keywordSearch,
                $myBuildingIds,
                null,
                $createStart,
                $createEnd,
                $status,
                $orderType,
                $productId,
                null,
                $limit,
                $offset
            );

        $receivableTypes = [
            'sales_wx' => '微信',
            'sales_alipay' => '支付宝支付',
            'sales_cash' => '现金',
            'sales_others' => '其他',
            'sales_pos' => 'POS机',
            'sales_remit' => '线下汇款',
        ];

        $orderLists = [];
        foreach ($orders as $order) {
            $orderLists[] = $this->handleOrderData(
                $order,
                $receivableTypes
            );
        }

        $view = new View();
        $view->setData($orderLists);

        return $view;
    }

    /**
     * @param ProductOrder $order
     * @param $receivableTypes
     *
     * @return array
     */
    private function handleOrderData(
        $order,
        $receivableTypes
    ) {
        $room = $order->getProduct()->getRoom();
        $building = $room->getBuilding();

        $customerData = '';
        if ($order->getCustomerId()) {
            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($order->getCustomerId());
            if ($customer) {
                $avatar = '';
                if ($customer->getAvatar()) {
                    $avatar = $customer->getAvatar();
                } elseif ($customer->getUserId()) {
                    $avatar = $this->getParameter('image_url').'/person/'.$customer->getUserId().'/avatar_small.jpg';
                }

                $customerData = [
                    'id' => $order->getCustomerId(),
                    'name' => $customer->getName(),
                    'avatar' => $avatar,
                ];
            }
        }

        $attachment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
            ->findAttachmentsByRoom($room->getId(), 1);

        $roomAttachment = [];
        if (!empty($attachment)) {
            $roomAttachment['content'] = $attachment[0]['content'];
            $roomAttachment['preview'] = $attachment[0]['preview'];
        }

        $payChannel = '';
        if ($order->getPayChannel()) {
            if (ProductOrder::CHANNEL_SALES_OFFLINE == $order->getPayChannel()) {
                $receivable = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Finance\FinanceReceivables')
                    ->findOneBy([
                        'orderNumber' => $order->getOrderNumber(),
                    ]);
                if ($receivable) {
                    $payChannel = $receivableTypes[$receivable->getPayChannel()];
                }
            } else {
                $payChannel = '创合钱包支付';
            }
        }

        $roomType = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$room->getType());
        $orderType = $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.$order->getType());
        $status = $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$order->getStatus());

        $result = array(
            'id' => $order->getId(),
            'order_number' => $order->getOrderNumber(),
            'creation_date' => $order->getCreationDate(),
            'status' => $status,
            'start_date' => $order->getStartDate(),
            'end_date' => $order->getEndDate(),
            'room_attachment' => $roomAttachment,
            'room_type_description' => $roomType,
            'room_type' => $room->getType(),
            'room_name' => $room->getName(),
            'building_name' => $building->getName(),
            'price' => (float) $order->getPrice(),
            'discount_price' => (float) $order->getDiscountPrice(),
            'order_type' => $orderType,
            'pay_channel' => $payChannel,
            'base_price' => $order->getBasePrice(),
            'unit_price' => $order->getUnitPrice(),
            'customer' => $customerData,
        );

        return $result;
    }

    /**
     * Get order  info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/products/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getOrderByIdAction(
        Request $request,
        $id
    ) {
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
     * @Route("/products/{id}/preorder")
     * @Method({"PATCH"})
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function patchPreOrderPriceAction(
        Request $request,
        $id
    ) {
        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->findOneBy([
                'id' => $id,
                'status' => ProductOrder::STATUS_UNPAID,
                'type' => ProductOrder::PREORDER_TYPE,
            ]);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        // bind data
        $orderJson = $this->container->get('serializer')->serialize($order, 'json');
        $patch = new Patch($orderJson, $request->getContent());
        $orderJson = $patch->apply();

        $form = $this->createForm(new PreOrderPriceType(), $order);
        $form->submit(json_decode($orderJson, true));

        $order->setEditAdminId($this->getAdminId());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // send message
        $this->sendXmppProductOrderNotification(
            null,
            null,
            ProductOrder::ACTION_CHANGE_PRICE,
            null,
            [$order],
            ProductOrderMessage::ORDER_CHANGE_PRICE_MESSAGE
        );

        return new View();
    }

    /**
     * Reserve order.
     *
     * @param Request $request
     *
     * @Route("/products/reserve")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
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

            $timeUnit = $form['time_unit']->getData();
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
                $type,
                $timeUnit,
                0
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
                $product,
                $timeUnit
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
     * @param Request $request
     *
     * @Route("/products/preorder")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
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

            $userId = $order->getUserId();
            $customerId = $order->getCustomerId();

            if (is_null($userId) && is_null($customerId)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            if ($userId) {
                $user = $em->getRepository('SandboxApiBundle:User\User')->find($order->getUserId());
                $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

                $productId = $order->getProductId();
                $product = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Product\Product')
                    ->find($productId);
                $salesCompanyId = $product->getRoom()->getBuilding()->getCompanyId();

                $newCustomerId = $this->get('sandbox_api.sales_customer')->createCustomer($user->getId(), $salesCompanyId);
                $order->setCustomerId($newCustomerId);
            }

            if ($customerId) {
                $user = null;
                $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')->find($order->getCustomerId());
                $this->throwNotFoundIfNull($customer, self::NOT_FOUND_MESSAGE);

                if ($customer->getUserId()) {
                    $user = $em->getRepository('SandboxApiBundle:User\User')->find($customer->getUserId());
                }
            }

            $productId = $order->getProductId();
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->find($productId);

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

            $timeUnit = $form['time_unit']->getData();
            $period = $order->getRentPeriod();

            // get endDate
            $endDate = $this->getOrderEndDate(
                $period,
                $timeUnit,
                $startDate
            );

            // check if price match
            $seatId = $order->getSeatId();
            if (!is_null($seatId)) {
                $seat = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomFixed')
                    ->findOneBy([
                        'id' => $seatId,
                        'roomId' => $product->getRoomId(),
                    ]);
                $this->throwNotFoundIfNull($seat, self::NOT_FOUND_MESSAGE);

                $basePrice = $seat->getBasePrice();
            } else {
                $leasingSet = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
                    ->findOneBy(array('product' => $product, 'unitPrice' => $timeUnit));

                if ($leasingSet) {
                    $basePrice = $leasingSet->getBasePrice();
                } else {
                    return $this->customErrorView(
                        400,
                        self::UNIT_NOT_FOUND_CODE,
                        self::UNIT_NOT_FOUND_MESSAGE
                    );
                }
            }

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
                $type,
                $timeUnit,
                $basePrice
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
            $order->setSalesInvoice(true);

            if (0 == $order->getDiscountPrice()) {
                $order->setStatus(ProductOrder::STATUS_PAID);
                $order->setPaymentDate($now);
            }

            $em->persist($order);

            // store order record
            $this->storeRoomRecord(
                $em,
                $order,
                $product,
                $timeUnit
            );

            $orders = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Order\ProductOrder')
                ->getOfficeRejected(
                    $productId,
                    $startDate,
                    $endDate
                );
            $this->rejectOrdersAction($orders, $now, $em);

            $em->flush();

            // set door access
            if (0 == $order->getDiscountPrice()) {
                $this->setDoorAccessForSingleOrder($order, $em);
            }

            // send message
            $this->sendXmppProductOrderNotification(
                null,
                null,
                ProductOrder::PREORDER_TYPE,
                null,
                [$order],
                ProductOrderMessage::ORDER_PREORDER_MESSAGE
            );

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
     * @param ProductOrder $orders
     * @param $now
     * @param $em
     */
    private function rejectOrdersAction(
        $orders,
        $now,
        $em
    ) {
        foreach ($orders as $rejectedOrder) {
            /** @var ProductOrder $rejectedOrder */
            $status = $rejectedOrder->getStatus();
            $channel = $rejectedOrder->getPayChannel();
            $userId = $rejectedOrder->getUserId();
            $price = $rejectedOrder->getDiscountPrice();

            if (ProductOrder::CHANNEL_OFFLINE == $channel && ProductOrder::STATUS_UNPAID == $status) {
                $existTransfer = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Order\OrderOfflineTransfer')
                    ->findOneByOrderId($rejectedOrder->getId());
                $this->throwNotFoundIfNull($existTransfer, self::NOT_FOUND_MESSAGE);

                $transferStatus = $existTransfer->getTransferStatus();
                if (OrderOfflineTransfer::STATUS_UNPAID == $transferStatus) {
                    $rejectedOrder->setStatus(ProductOrder::STATUS_CANCELLED);
                    $rejectedOrder->setCancelledDate(new \DateTime());
                    $rejectedOrder->setModificationDate(new \DateTime());
                } else {
                    $existTransfer->setTransferStatus(OrderOfflineTransfer::STATUS_VERIFY);
                }
            } else {
                $rejectedOrder->setStatus(ProductOrder::STATUS_CANCELLED);
                $rejectedOrder->setCancelledDate($now);
                $rejectedOrder->setModificationDate($now);
                $rejectedOrder->setCancelByUser(true);

                if ($price > 0) {
                    $rejectedOrder->setNeedToRefund(true);

                    if (ProductOrder::CHANNEL_ACCOUNT == $channel) {
                        $balance = $this->postBalanceChange(
                            $userId,
                            $price,
                            $rejectedOrder->getOrderNumber(),
                            self::PAYMENT_CHANNEL_ACCOUNT,
                            0,
                            self::ORDER_REFUND
                        );

                        $rejectedOrder->setRefundProcessed(true);
                        $rejectedOrder->setRefundProcessedDate($now);

                        if (!is_null($balance)) {
                            $rejectedOrder->setRefunded(true);
                            $rejectedOrder->setNeedToRefund(false);
                        }
                    }

                    if (ProductOrder::STATUS_UNPAID == $status) {
                        $rejectedOrder->setNeedToRefund(false);
                    }
                }
            }
        }
        $em->flush();

        if (!empty($orders)) {
            // send message
            $this->sendXmppProductOrderNotification(
                null,
                null,
                ProductOrder::ACTION_REJECTED,
                null,
                $orders,
                ProductOrderMessage::OFFICE_REJECTED_MESSAGE
            );
        }
    }
}
