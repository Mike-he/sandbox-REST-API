<?php

namespace Sandbox\ClientApiBundle\Controller\Order;

use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\Order\OrderController;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Order\OrderOfflineTransfer;
use Sandbox\ApiBundle\Entity\Order\TransferAttachment;
use Sandbox\ApiBundle\Form\Order\OrderOfflineTransferPost;
use Sandbox\ApiBundle\Form\Order\TransferAttachmentType;
use Sandbox\ApiBundle\Traits\SetStatusTrait;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;
use Symfony\Component\HttpFoundation\Response;
use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Entity\Order\InvitedPeople;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Form\Order\OrderType;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Rest controller for Client Orders.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientOrderController extends OrderController
{
    use SetStatusTrait;

    /**
     * Get all orders for current user.
     *
     * @Get("/orders/my")
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    default=null,
     *    nullable=true,
     *    description="
     *        maximum allowed people
     *    "
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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getUserOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $status = $paramFetcher->get('status');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $language = $request->getPreferredLanguage();

        if (!is_null($status) && !empty($status)) {
            $orders = $this->getRepo('Order\ProductOrder')->findBy(
                [
                    'userId' => $userId,
                    'status' => $status,
                ],
                ['modificationDate' => 'DESC'],
                $limit,
                $offset
            );
        } else {
            $orders = $this->getRepo('Order\ProductOrder')->findBy(
                ['userId' => $userId],
                ['modificationDate' => 'DESC'],
                $limit,
                $offset
            );
        }

        foreach ($orders as $order) {
            $room = $order->getProduct()->getRoom();
            $type = $room->getType();

            $description = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$type,
                array(),
                null,
                $language
            );

            $room->setTypeDescription($description);
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($orders);

        return $view;
    }

    /**
     * Get all orders for current user.
     *
     * @Get("/orders/mylist")
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    default=null,
     *    nullable=true,
     *    description="
     *        order status
     *    "
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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getUserOrderListAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $status = $paramFetcher->get('status');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $language = $request->getPreferredLanguage();
        $orders = [];

        switch ($status) {
            case ProductOrder::COMBINE_STATUS_PENDING:
                $orders = $this->getRepo('Order\ProductOrder')->getUserPendingOrders(
                    $userId,
                    $limit,
                    $offset
                );

                break;
            case ProductOrder::STATUS_COMPLETED:
                $orders = $this->getRepo('Order\ProductOrder')->getUserCompletedOrders(
                    $userId,
                    $limit,
                    $offset
                );

                break;
            case ProductOrder::COMBINE_STATUS_REFUND:
                $orders = $this->getRepo('Order\ProductOrder')->getUserRefundOrders(
                    $userId,
                    $limit,
                    $offset
                );

                break;
        }

        foreach ($orders as $order) {
            $room = $order->getProduct()->getRoom();
            $type = $room->getType();
            $appointed = $order->getAppointed();

            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneByUserId($appointed);

            if (!is_null($profile)) {
                $order->setAppointedName($profile->getName());
            }

            $description = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$type,
                array(),
                null,
                $language
            );

            $room->setTypeDescription($description);
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($orders);

        return $view;
    }

    /**
     * Get sales invoice orders amount.
     *
     * @Get("/orders/my/sales/invoice/amount")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getUserSalesInvoiceAmountAction(
        Request $request
    ) {
        $userId = $this->getUserId();

        $productAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getInvoiceOrdersAmount($userId);

        if (is_null($productAmount)) {
            $productAmount = 0;
        }

        $billAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->sumInvoiceBillsFees($userId);

        if (is_null($billAmount)) {
            $billAmount = 0;
        }

        $amount = $productAmount + $billAmount;

        return new View(['amount' => (float) $amount]);
    }

    /**
     * Get sales invoice orders for current user.
     *
     * @Get("/orders/my/sales/invoice")
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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getUserSalesInvoiceOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $tradeNumbers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Invoice\TradeInvoiceView')
            ->getNeedToInvoiceTradeNumbers(
                $userId,
                $limit,
                $offset
            );

        $response = array();
        foreach ($tradeNumbers as $number) {
            switch (substr($number, 0, 1)) {
                case ProductOrder::LETTER_HEAD:
                    $responseArray = $this->getProductOrderResponse($number);
                    break;
                case LeaseBill::LEASE_BILL_LETTER_HEAD:
                    $responseArray = $this->getLeaseBillResponse($number);
                    break;
                default:
                    break;
            }

            array_push($response, $responseArray);
        }

        $view = new View($response);

        return $view;
    }

    /**
     * @GET("/orders/my/sales/invoice/selected")
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="number",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="ids of orders"
     * )
     *
     * @return View
     */
    public function getUserSalesInvoiceOrdersByOrderIdsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $tradeNumbers = $paramFetcher->get('number');

        $response = array();
        foreach ($tradeNumbers as $number) {
            switch (substr($number, 0, 1)) {
                case ProductOrder::LETTER_HEAD:
                    $responseArray = $this->getProductOrderResponse($number);
                    break;
                case LeaseBill::LEASE_BILL_LETTER_HEAD:
                    $responseArray = $this->getLeaseBillResponse($number);
                    break;
                default:
                    break;
            }

            array_push($response, $responseArray);
        }

        $view = new View($response);

        return $view;
    }

    /**
     * post sales invoice order.
     *
     * @Post("/orders/{id}/sales/invoice")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function postUserOrderInvoicedAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        $order = $this->getRepo('Order\ProductOrder')->getInvoiceOrdersForInvoiced(
            $id,
            $userId
        );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $order->setInvoiced(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Post("/orders/{id}/sales/invoice/cancel")
     *
     * @return View
     */
    public function postUserOrderInvoicedCancelAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->findOneBy(array(
                'id' => $id,
                'userId' => $userId,
            ));
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $order->setInvoiced(false);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Get user's current available rooms.
     *
     * @Get("/orders/current")
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
     * @Annotations\QueryParam(
     *    name="search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getUserCurrentRoomsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $search = $paramFetcher->get('search');

        $orders = $this->getRepo('Order\ProductOrder')->getUserCurrentOrders(
            $userId,
            $limit,
            $offset,
            $search
        );

        foreach ($orders as $order) {
            $room = $order->getProduct()->getRoom();
            $roomType = $room->getType();

            if ($roomType == Room::TYPE_LONG_TERM) {
                $roomType = Room::TYPE_OFFICE;
            }

            $type = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$roomType);
            $room->setTypeDescription($type);
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['current_order']));
        $view->setData($orders);

        return $view;
    }

    /**
     * Create orders.
     *
     * @Post("/orders")
     *
     * @param Request $request
     *
     * @return View
     */
    public function createOrdersAction(
        Request $request
    ) {
        $em = $this->getDoctrine()->getManager();
        $orderCheck = null;
        $now = new \DateTime();

        try {
            $userId = $this->getUserId();
            $user = $this->getRepo('User\User')->find($userId);
            $order = new ProductOrder();

            $form = $this->createForm(new OrderType(), $order);
            $form->handleRequest($request);

            if (!$form->isValid()) {
                return $this->customErrorView(
                    400,
                    self::INVALID_FORM_CODE,
                    self::INVALID_FORM_MESSAGE
                );
            }

            //check if product exists
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

            $period = $form['rent_period']->getData();
            $timeUnit = $form['time_unit']->getData();

            $type = $product->getRoom()->getType();

            if ($type === Room::TYPE_OFFICE && $order->getIsRenew()) {
                $myEnd = $now->modify('+ 7 days');
                $myOrder = $this->getRepo('Order\ProductOrder')->getRenewOrder(
                    $userId,
                    $productId,
                    $myEnd
                );

                if (is_null($myOrder)) {
                    return $this->customErrorView(
                        400,
                        self::CAN_NOT_RENEW_CODE,
                        self::CAN_NOT_RENEW_MESSAGE
                    );
                }

                $startDate = $myOrder->getEndDate();
                $startDate->modify('+ 1 day');

                $endDate = clone $startDate;
                $endDate->modify('+ 30 days');

                $startDate->setTime(00, 00, 00);
            } else {
                $roomType = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomTypes')
                    ->findOneBy(['name' => $type]);
                $this->throwNotFoundIfNull($roomType, self::NOT_FOUND_MESSAGE);

                $diff = $startDate->diff($now)->days;
                $range = $roomType->getRange();

                if ($diff > $range) {
                    return $this->customErrorView(
                        400,
                        self::NOT_WITHIN_DATE_RANGE_CODE,
                        self::NOT_WITHIN_DATE_RANGE_MESSAGE
                    );
                }

                $endDate = $this->getOrderEndDate(
                    $period,
                    $timeUnit,
                    $startDate
                );
            }

            // check if it's same order from the same user
            // return orderId if so
            if ($type !== Room::TYPE_FLEXIBLE && $type !== Room::TYPE_FIXED) {
                $sameOrder = $this->getRepo('Order\ProductOrder')->getOrderFromSameUser(
                    $productId,
                    $userId,
                    $startDate,
                    $endDate
                );

                if (!is_null($sameOrder)) {
                    return new View(
                        ['order_id' => $sameOrder->getId()]
                    );
                }
            }

            $seatId = $order->getSeatId();
            $basePrice = $product->getBasePrice();
            if (!is_null($seatId)) {
                $seat = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomFixed')
                    ->findOneBy([
                        'id' => $seatId,
                        'roomId' => $product->getRoomId(),
                    ]);
                $this->throwNotFoundIfNull($seat, self::NOT_FOUND_MESSAGE);

                $basePrice = $seat->getBasePrice();
            }

            // check if price match
            $error = $this->checkIfPriceMatch(
                $order,
                $productId,
                $product,
                $period,
                $startDate,
                $endDate,
                $basePrice
            );

            if (!empty($error)) {
                return $this->customErrorView(
                    400,
                    $error['code'],
                    $error['message']
                );
            }

            // check booking dates and order duplication
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

            if (Room::TYPE_OFFICE == $type) {
                $order->setRejected(true);
            }

            // set order drawer
            $this->setOrderDrawer(
                $product,
                $order
            );

            // set service fee
            $company = $product->getRoom()->getBuilding()->getCompany();
            $serviceInfo = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
                ->findOneBy([
                    'company' => $company,
                    'tradeTypes' => $type,
                ]);

            if (!is_null($serviceInfo)) {
                $order->setServiceFee($serviceInfo->getServiceFee());
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
     * @param $order
     *
     * @return View
     */
    private function payByAccount(
        $order,
        $channel
    ) {
        $price = $order->getDiscountPrice();
        $orderNumber = $order->getOrderNumber();
        $balance = $this->postBalanceChange(
            $order->getUserId(),
            (-1) * $price,
            $orderNumber,
            self::PAYMENT_CHANNEL_ACCOUNT,
            $price
        );
        if (is_null($balance)) {
            return $this->customErrorView(
                400,
                self::INSUFFICIENT_FUNDS_CODE,
                self::INSUFFICIENT_FUNDS_MESSAGE
            );
        }

        $order->setStatus(self::STATUS_PAID);
        $order->setPaymentDate(new \DateTime());
        $order->setModificationDate(new \DateTime());

        // store payment channel
        $this->storePayChannel(
            $order,
            $channel
        );

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        //send message
        $type = $order->getProduct()->getRoom()->getType();

        if (Room::TYPE_OFFICE == $type && is_null($order->getType())) {
            $this->sendXmppProductOrderNotification(
                null,
                null,
                ProductOrder::ACTION_OFFICE_ORDER,
                null,
                [$order],
                ProductOrderMessage::OFFICE_ORDER_MESSAGE
            );
        }

        // set door access
        if (!$order->isRejected()) {
            $this->setDoorAccessForSingleOrder($order, $em);
        }

        $view = new View();

        return $view->setData(
            array(
                'balance' => $balance,
                'channel' => self::PAYMENT_CHANNEL_ACCOUNT,
            )
        );
    }

    /**
     * @Post("/orders/{id}/pay")
     *
     * @param Request $request
     * @param $id
     */
    public function payAction(
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

        // check if request user is the same as order user
        $this->throwAccessDeniedIfNotSameUser($order->getUserId());

        if ($order->getStatus() !== 'unpaid') {
            return $this->customErrorView(
                400,
                self::WRONG_PAYMENT_STATUS_CODE,
                self::WRONG_PAYMENT_STATUS_MESSAGE
            );
        }
        $requestContent = json_decode($request->getContent(), true);
        $channel = '';
        $token = '';
        $smsId = '';
        $smsCode = '';
        $openId = null;

        if (array_key_exists('channel', $requestContent)) {
            $channel = $requestContent['channel'];
        }

        if ($channel == self::PAYMENT_CHANNEL_ACCOUNT) {
            return $this->payByAccount(
                $order,
                $channel
            );
        } elseif ($channel == ProductOrder::CHANNEL_OFFLINE) {
            return $this->setOfflineChannel(
                $order,
                $channel
            );
        } elseif ($channel == ProductOrder::CHANNEL_WECHAT_PUB) {
            $wechat = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:ThirdParty\WeChat')
                ->findOneBy(
                    [
                        'userId' => $order->getUserId(),
                        'loginFrom' => ThirdPartyOAuthWeChatData::DATA_FROM_WEBSITE,
                    ]
                );
            $this->throwNotFoundIfNull($wechat, self::NOT_FOUND_MESSAGE);

            $openId = $wechat->getOpenId();
        }

        $orderNumber = $order->getOrderNumber();
        $charge = $this->payForOrder(
            $token,
            $smsId,
            $smsCode,
            $orderNumber,
            $order->getDiscountPrice(),
            $channel,
            ProductOrder::PAYMENT_SUBJECT,
            ProductOrder::PAYMENT_BODY,
            $openId
        );
        $charge = json_decode($charge, true);

        return new View($charge);
    }

    /**
     * @Put("/orders/{id}/transfer")
     *
     * @param Request $request
     * @param $id
     */
    public function updateTransferAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        $order = $this->getRepo('Order\ProductOrder')->findOneBy(
            [
                'id' => $id,
                'status' => ProductOrder::STATUS_UNPAID,
                'userId' => $userId,
                'payChannel' => ProductOrder::CHANNEL_OFFLINE,
            ]
        );

        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }

        $transfer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\OrderOfflineTransfer')
            ->findOneByOrderId($id);

        if (is_null($transfer)) {
            return new View();
        }

        $transferStatus = $transfer->getTransferStatus();
        if ($transferStatus != OrderOfflineTransfer::STATUS_UNPAID &&
            $transferStatus != OrderOfflineTransfer::STATUS_RETURNED
        ) {
            return $this->customErrorView(
                400,
                self::WRONG_ORDER_STATUS_CODE,
                self::WRONG_ORDER_STATUS_MESSAGE
            );
        }

        $form = $this->createForm(new OrderOfflineTransferPost(), $transfer);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return $this->customErrorView(
                400,
                self::INVALID_FORM_CODE,
                self::INVALID_FORM_MESSAGE
            );
        }

        $attachmentArray = $transfer->getAttachments();
        if (empty($attachmentArray)) {
            return new View();
        }

        $em = $this->getDoctrine()->getManager();

        $transferAttachments = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\TransferAttachment')
            ->findByTransfer($transfer);

        foreach ($transferAttachments as $transferAttachment) {
            $em->remove($transferAttachment);
        }

        $attachment = new TransferAttachment();

        $form = $this->createForm(new TransferAttachmentType(), $attachment);
        $form->submit($attachmentArray[0]);

        $transfer->setTransferStatus(OrderOfflineTransfer::STATUS_PENDING);
        $attachment->setTransfer($transfer);
        $em->persist($attachment);

        $em->flush();

        return new View();
    }

    /**
     * @Post("/orders/{id}/refund")
     *
     * @param Request $request
     * @param $id
     */
    public function refundAction(
        Request $request,
        $id
    ) {
        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->findOneBy(
                [
                    'id' => $id,
                    'refunded' => false,
                    'refundProcessed' => false,
                ]
            );
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }

        $refundChannel = null;
        $content = json_decode($request->getContent(), true);
        if (!is_null($content) &&
            !empty($content) &&
            array_key_exists('refund_channel', $content) &&
            !empty($content['refund_channel'])
        ) {
            $refundChannel = $content['refund_channel'];
        }

        $price = $order->getDiscountPrice();
        $userId = $order->getUserId();
        $status = $order->getStatus();

        // check if request user is the same as order user
        $this->throwAccessDeniedIfNotSameUser($userId);

        $now = new \DateTime();
        if (ProductOrder::STATUS_CANCELLED == $status ||
            ProductOrder::STATUS_COMPLETED == $status
        ) {
            return $this->customErrorView(
                400,
                self::WRONG_PAYMENT_STATUS_CODE,
                self::WRONG_PAYMENT_STATUS_MESSAGE
            );
        }

        $order->setCancelByUser(true);
        $channel = $order->getPayChannel();

        if ($status == ProductOrder::STATUS_UNPAID) {
            if ($channel == ProductOrder::CHANNEL_OFFLINE) {
                $existTransfer = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Order\OrderOfflineTransfer')
                    ->findOneByOrderId($id);
                $this->throwNotFoundIfNull($existTransfer, self::NOT_FOUND_MESSAGE);

                $transferStatus = $existTransfer->getTransferStatus();
                if ($transferStatus == OrderOfflineTransfer::STATUS_UNPAID) {
                    $order->setStatus(ProductOrder::STATUS_CANCELLED);
                    $order->setCancelledDate(new \DateTime());
                    $order->setModificationDate(new \DateTime());
                } else {
                    $existTransfer->setTransferStatus(OrderOfflineTransfer::STATUS_VERIFY);

                    if ($refundChannel == ProductOrder::CHANNEL_ACCOUNT) {
                        $order->setRefundTo(ProductOrder::REFUND_TO_ACCOUNT);
                    }
                }
            } else {
                $order->setStatus(ProductOrder::STATUS_CANCELLED);
                $order->setCancelledDate(new \DateTime());
                $order->setModificationDate(new \DateTime());
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return new View();
        }

        $order->setModificationDate(new \DateTime());

        if ($price > 0) {
            $order->setNeedToRefund(true);

            if (ProductOrder::CHANNEL_ACCOUNT == $channel ||
                ProductOrder::CHANNEL_ACCOUNT == $refundChannel
            ) {
                $balance = $this->postBalanceChange(
                    $userId,
                    $price,
                    $order->getOrderNumber(),
                    self::PAYMENT_CHANNEL_ACCOUNT,
                    0,
                    self::ORDER_REFUND
                );

                $order->setRefundProcessed(true);
                $order->setRefundProcessedDate($now);
                $order->setActualRefundAmount($price);

                if (!is_null($balance)) {
                    $order->setRefunded(true);
                    $order->setNeedToRefund(false);

                    if ($refundChannel == ProductOrder::CHANNEL_ACCOUNT &&
                        $channel != ProductOrder::CHANNEL_ACCOUNT
                    ) {
                        $amount = $this->postConsumeBalance(
                            $userId,
                            $price,
                            $order->getOrderNumber()
                        );

                        $order->setRefundTo(ProductOrder::REFUND_TO_ACCOUNT);

                        $orderNumber = $this->getOrderNumber(self::TOPUP_ORDER_LETTER_HEAD);
                        $this->setTopUpOrder(
                            $userId,
                            $price,
                            $orderNumber,
                            $channel
                        );
                    }
                }
            }
        }

        $this->removeAccessByOrder($order);

        return new View();
    }

    /**
     * @Post("/orders/{id}/people")
     *
     * @param Request $request
     * @param $id
     */
    public function addPeopleAction(
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

        // check if request user is the same as order user
        $this->throwAccessDeniedIfNotSameUser($order->getUserId());

        $status = $order->getStatus();
        $endDate = $order->getEndDate();
        $now = new \DateTime();
        if (
            ($status !== ProductOrder::STATUS_PAID &&
            $status !== ProductOrder::STATUS_COMPLETED) ||
            $now >= $endDate ||
            $order->isRejected()
        ) {
            return $this->customErrorView(
                400,
                self::WRONG_ORDER_STATUS_CODE,
                self::WRONG_ORDER_STATUS_MESSAGE
            );
        }

        $people = json_decode($request->getContent(), true);
        $this->setDoorAccessForInvite(
            $order,
            $people['add'],
            $people['remove']
        );

        return new Response();
    }

    /**
     * @param Request $request
     * @param $id
     * @param ParamFetcherInterface $paramFetcher
     * @param array
     */
    private function deletePeople(
        $removeUsers,
        $orderId,
        $base
    ) {
        $em = $this->getDoctrine()->getManager();
        $userArray = [];
        $recvUsers = [];
        foreach ($removeUsers as $removeUser) {
            $userId = $removeUser['user_id'];
            $person = $this->getRepo('Order\InvitedPeople')->findOneBy(
                [
                    'orderId' => $orderId,
                    'userId' => $userId,
                ]
            );
            if (!is_null($person)) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($person);
                $em->flush();

                // set user array for message
                array_push($recvUsers, $userId);
            }

            if (is_null($base) || empty($base)) {
                continue;
            }

            // set controller status to delete
            $this->setControlToDelete(
                $orderId,
                $userId
            );

            $result = $this->getCardNoByUser($userId);
            if ($result['status'] !== DoorController::STATUS_UNAUTHED) {
                $empUser = ['empid' => $userId];
                array_push($userArray, $empUser);
            }
        }
        $em->flush();

        // remove room access
        if (!empty($userArray)) {
            $this->callRemoveFromOrderCommand(
                $base,
                $orderId,
                $userArray
            );
        }

        return $recvUsers;
    }

    /**
     * @Get("/orders/{id}/invited")
     *
     * @param Request $request
     * @param $id
     */
    public function getInvitedPeopleAction(
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
        $people = $this->getRepo('Order\InvitedPeople')->findBy(
            ['orderId' => $id]
        );

        $users = [];
        foreach ($people as $person) {
            $userId = $person->getUserId();
            $user = $this->getRepo('User\UserProfile')->findOneBy(['userId' => $userId]);
            array_push($users, $user);
        }

        return new View($users);
    }

    /**
     * @Post("/orders/{id}/person/appoint")
     *
     * @param Request $request
     * @param $id
     */
    public function appointPersonAction(
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

        // check if request user is the same as order user
        $this->throwAccessDeniedIfNotSameUser($order->getUserId());

        $status = $order->getStatus();
        $endDate = $order->getEndDate();
        $now = new \DateTime();
        if ($status !== 'paid' && $status !== 'completed' || $now >= $endDate) {
            return $this->customErrorView(
                400,
                self::WRONG_ORDER_STATUS_CODE,
                self::WRONG_ORDER_STATUS_MESSAGE
            );
        }

        $requestContent = json_decode($request->getContent(), true);
        $newUser = $requestContent['user_id'];
        $currentUser = $order->getAppointed();
        $orderUser = $order->getUserId();

        if (!is_null($newUser) && !empty($newUser)) {
            $this->setDoorAccessForAppoint(
                $order,
                $newUser,
                $currentUser,
                $orderUser
            );
        }

        return new Response();
    }

    /**
     * @Delete("/orders/{id}/person/appoint")
     *
     * @param Request $request
     * @param $id
     */
    public function removePersonAction(
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

        // check if request user is the same as order user
        $this->throwAccessDeniedIfNotSameUser($order->getUserId());

        $status = $order->getStatus();
        $endDate = $order->getEndDate();
        $now = new \DateTime();
        if ($status !== 'paid' && $status !== 'completed' || $now >= $endDate) {
            return $this->customErrorView(
                400,
                self::WRONG_ORDER_STATUS_CODE,
                self::WRONG_ORDER_STATUS_MESSAGE
            );
        }

        $currentUser = $order->getAppointed();
        $orderUser = $order->getUserId();

        if (!is_null($orderUser) && !empty($orderUser)) {
            $this->removeAppointed(
                $order,
                $currentUser,
                $orderUser
            );
        }
    }

    /**
     * @Get("/orders/number/{orderNumber}")
     *
     * @param Request $request
     * @param int     $orderNumber
     *
     * @return View
     */
    public function getOrderByOrderNumberAction(
        Request $request,
        $orderNumber
    ) {
        $order = $this->getRepo('Order\ProductOrder')->findOneBy(
            ['orderNumber' => $orderNumber]
        );
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }
        $view = $this->getOrderDetail($request, $order);

        return $view;
    }

    /**
     * @Get("/orders/{id}")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getOneOrderAction(
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
        $view = $this->getOrderDetail($request, $order);

        return $view;
    }

    /**
     * @param Request $request
     * @param $order
     *
     * @return View
     */
    private function getOrderDetail(
        $request,
        $order
    ) {
        $appointed = $order->getAppointed();
        $appointedPerson = [];
        $users = [];

        if (!is_null($appointed) && !empty($appointed)) {
            array_push($users, $appointed);
            $appointedPerson = $this->getRepo('User\UserView')->find($appointed);
        }

        $now = new \DateTime();
        $userId = $order->getUserId();
        $this->throwNotFoundIfNull($userId, self::NOT_FOUND_MESSAGE);
        array_push($users, $userId);

        $currentUserId = $this->getUserId();

        // find all invited
        $invited = $this->getRepo('Order\InvitedPeople')->findOneBy(
            [
                'orderId' => $order->getId(),
                'userId' => $currentUserId,
            ]
        );

        if (is_null($invited)) {
            if (!in_array($currentUserId, $users)) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }
        }

        $room = $order->getProduct()->getRoom();
        $type = $room->getType();
        $language = $request->getPreferredLanguage();

        $description = $this->get('translator')->trans(
            ProductOrderExport::TRANS_ROOM_TYPE.$type,
            array(),
            null,
            $language
        );

        $room->setTypeDescription($description);
        $productId = $order->getProductId();
        $status = $order->getStatus();
        $startDate = $order->getStartDate();
        $endDate = $order->getEndDate();

        $renewButton = false;

        if ($type == Room::TYPE_OFFICE && $status == ProductOrder::STATUS_COMPLETED) {
            $renewOrder = $this->getRepo('Order\ProductOrder')->getAlreadyRenewedOrder($userId, $productId);
            if (is_null($renewOrder) || empty($renewOrder)) {
                $endDate = $order->getEndDate();
                $days = $endDate->diff($now)->days;
                if ($days >= 7 && $now >= $startDate) {
                    $renewButton = true;
                }
            }
        }

        if ($status == ProductOrder::STATUS_PAID && $now >= $startDate && !$order->isRejected()) {
            $this->setProductOrderStatusCompleted($order);

            if ($order->getDiscountPrice() > 0
                && ProductOrder::CHANNEL_ACCOUNT != $order->getPayChannel()
                && !$order->isSalesInvoice()
            ) {
                $this->setProductOrderInvoice($order);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        $alertArray = $this->setPopUpMessage(
            $order,
            $now,
            $startDate,
            $endDate,
            $status,
            $type,
            $language
        );

        $viewArray = [
            'renewButton' => $renewButton,
            'order' => $order,
            'appointedPerson' => $appointedPerson,
        ];

        if (!empty($alertArray)) {
            $viewArray = array_merge($viewArray, $alertArray);
        }

        // add evaluation tag
        $this->setOrderEvaluationTag(
            $order,
            $currentUserId
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($viewArray);

        return $view;
    }

    /**
     * @param ProductOrder $order
     * @param $currentUserId
     */
    private function setOrderEvaluationTag(
        $order,
        $currentUserId
    ) {
        $evaluation = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Evaluation\Evaluation')
            ->findOneBy(array(
                'productOrderId' => $order->getId(),
                'userId' => $currentUserId,
            ));

        if (!is_null($evaluation)) {
            $order->setHasEvaluated(true);
        }
    }

    /**
     * @Get("/orders/{id}/remaining")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getOrderRemainingTimeAction(
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

        //$modifyTime = $this->getGlobal('time_for_preorder_cancel');
        $status = $order->getStatus();
        $now = new \DateTime();
        $hours = 0;
        $minutes = 0;
        $seconds = 0;
        $channel = $order->getPayChannel();

        if ($status == ProductOrder::STATUS_UNPAID && $channel != ProductOrder::CHANNEL_OFFLINE) {
            $creationTime = $order->getCreationDate();

            if (ProductOrder::PREORDER_TYPE == $order->getType()) {
                return new View();

                // removed for preorder
//                $start = $order->getStartDate();

//                if ($start > $now) {
//                    $remainingTime = $start->diff($creationTime);
//                    $days = $remainingTime->d;

//                    if ($days > 0) {
//                        $endTime = clone $creationTime;
//                        $endTime->modify($modifyTime);

//                        $remainingTime = $endTime->diff($now);
//                        $hours = $remainingTime->h;
//                        $minutes = $remainingTime->i;
//                        $seconds = $remainingTime->s;

//                        if ($now >= $endTime) {
//                            $hours = 0;
//                            $minutes = 0;
//                            $seconds = 0;

//                            $this->setOrderStatusCancelled($order, $now);
//                        }
//                    } else {
//                        $remainingTime = $start->diff($now);
//                        $hours = $remainingTime->h;
//                        $minutes = $remainingTime->i;
//                        $seconds = $remainingTime->s;
//                    }
//                } else {
//                    $remainingTime = $now->diff($creationTime);
//                    $minutes = $remainingTime->i;
//                    $seconds = $remainingTime->s;

//                    $minutes = 4 - $minutes;
//                    $seconds = 59 - $seconds;

//                    if ($minutes < 0) {
//                        $minutes = 0;
//                        $seconds = 0;

//                        $this->setOrderStatusCancelled($order, $now);
//                    }
//                }
            } else {
                $remainingTime = $now->diff($creationTime);
                $minutes = $remainingTime->i;
                $seconds = $remainingTime->s;

                $minutes = 4 - $minutes;
                $seconds = 59 - $seconds;

                if ($minutes < 0) {
                    $minutes = 0;
                    $seconds = 0;

                    $this->setOrderStatusCancelled($order, $now);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        $view = new View();
        $view->setData(
            [
                'remainingHours' => $hours,
                'remainingMinutes' => $minutes,
                'remainingSeconds' => $seconds,
            ]
        );

        return $view;
    }

    /**
     * @param ProductOrder $order
     * @param \DateTime    $now
     */
    private function setOrderStatusCancelled(
        $order,
        $now
    ) {
        $order->setStatus(ProductOrder::STATUS_CANCELLED);
        $order->setCancelledDate($now);
        $order->setModificationDate($now);
    }

    /**
     * @param $order
     * @param $users
     * @param $removeUsers
     */
    private function setDoorAccessForInvite(
        $order,
        $users,
        $removeUsers
    ) {
        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        if (is_null($building)) {
            return;
        }
        $base = $building->getServer();
        $roomId = $order->getProduct()->getRoom()->getId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findBy(['room' => $roomId]);

        $userArray = [];
        $recvUsers = [];
        $em = $this->getDoctrine()->getManager();
        if (!empty($users) && !is_null($users)) {
            foreach ($users as $user) {
                $userId = $user['user_id'];

                // find user
                $user = $this->getRepo('User\User')->find($userId);
                $this->throwNotFoundIfNull($user, User::ERROR_NOT_FOUND);

                // find user in invitedPeople
                $person = $this->getRepo('Order\InvitedPeople')->findOneBy(
                    [
                        'orderId' => $order->getId(),
                        'userId' => $userId,
                    ]
                );
                if (is_null($person)) {
                    $people = new InvitedPeople();
                    $people->setOrderId($order);
                    $people->setUserId($userId);
                    $people->setCreationDate(new \DateTime());
                    $em->persist($people);

                    // set user array for message
                    array_push($recvUsers, $userId);
                }

                if (is_null($base) || empty($base) || empty($roomDoors)) {
                    continue;
                }

                $this->storeDoorAccess(
                    $em,
                    $order->getId(),
                    $userId,
                    $buildingId,
                    $roomId,
                    $order->getStartDate(),
                    $order->getEndDate()
                );

                $userArray = $this->getUserArrayIfAuthed(
                    $base,
                    $userId,
                    $userArray
                );
            }
            $em->flush();
        }

        $removedUserArray = [];
        if (!empty($removeUsers) && !is_null($removeUsers)) {
            // remove user
            $removedUserArray = $this->deletePeople(
                $removeUsers,
                $order->getId(),
                $base
            );
        }

        // set room access
        if (!empty($userArray)) {
            $this->callSetRoomOrderCommand(
                $base,
                $userArray,
                $roomDoors,
                $order->getId(),
                $order->getStartDate(),
                $order->getEndDate()
            );
        }

        // send notification to invited users
        if (!empty($recvUsers)) {
            $this->sendXmppProductOrderNotification(
                $order,
                $recvUsers,
                ProductOrder::ACTION_INVITE_ADD,
                $order->getUserId(),
                [],
                ProductOrderMessage::APPOINT_MESSAGE_PART1,
                ProductOrderMessage::APPOINT_MESSAGE_PART2
            );
        }

        // send notification to invited users
        if (!empty($removedUserArray)) {
            $this->sendXmppProductOrderNotification(
                $order,
                $removedUserArray,
                ProductOrder::ACTION_INVITE_REMOVE,
                $order->getUserId(),
                [],
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
            );
        }
    }

    /**
     * @param $order
     * @param $newUser
     * @param $currentUser
     * @param $orderUser
     */
    private function setDoorAccessForAppoint(
        $order,
        $newUser,
        $currentUser,
        $orderUser
    ) {
        // find user
        $user = $this->getRepo('User\User')->find($newUser);
        $this->throwNotFoundIfNull($user, User::ERROR_NOT_FOUND);

        $userArray = [];
        $order->setAppointed($newUser);
        $order->setModificationDate(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // send notification to new user
        $this->sendXmppProductOrderNotification(
            $order,
            [$newUser],
            ProductOrder::ACTION_APPOINT_ADD,
            $orderUser,
            [],
            ProductOrderMessage::APPOINT_MESSAGE_PART1,
            ProductOrderMessage::APPOINT_MESSAGE_PART2
        );

        if (!is_null($currentUser) && !empty($currentUser) && $currentUser != 0) {
            // send notification to old appointed user
            $this->sendXmppProductOrderNotification(
                $order,
                [$currentUser],
                ProductOrder::ACTION_APPOINT_REMOVE,
                $orderUser,
                [],
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
            );
        }

        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        if (is_null($building)) {
            return;
        }
        $base = $building->getServer();
        if (is_null($base) || empty($base)) {
            return;
        }
        $roomId = $order->getProduct()->getRoom()->getId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findBy(['room' => $roomId]);
        if (empty($roomDoors)) {
            return;
        }

        // set controller status to delete
        $this->setControlToDelete($order->getId());

        // add new door access
        $this->storeDoorAccess(
            $em,
            $order->getId(),
            $newUser,
            $buildingId,
            $roomId,
            $order->getStartDate(),
            $order->getEndDate()
        );
        $em->flush();

        $userArray = $this->getUserArrayIfAuthed(
            $base,
            $newUser,
            $userArray
        );

        // set room access
        if (!empty($userArray)) {
            $this->callSetRoomOrderCommand(
                $base,
                $userArray,
                $roomDoors,
                $order->getId(),
                $order->getStartDate(),
                $order->getEndDate()
            );
        }

        // remove all user access with method delete
        $this->removeUserAccess(
            $order->getId(),
            $base
        );
    }

    /**
     * @param $order
     * @param $currentUser
     * @param $orderUser
     */
    private function removeAppointed(
        $order,
        $currentUser,
        $orderUser
    ) {
        $em = $this->getDoctrine()->getManager();
        $order->setAppointed(null);
        $order->setModificationDate(new \DateTime());
        $em->flush();

        if (!is_null($currentUser) && !empty($currentUser) && $currentUser != 0) {
            // send notification to appointed user
            $this->sendXmppProductOrderNotification(
                $order,
                [$currentUser],
                ProductOrder::ACTION_APPOINT_REMOVE,
                $orderUser,
                [],
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
            );
        }

        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        if (is_null($building)) {
            return;
        }
        $base = $building->getServer();
        if (is_null($base) || empty($base)) {
            return;
        }

        $roomId = $order->getProduct()->getRoom()->getId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findBy(['room' => $roomId]);
        if (empty($roomDoors)) {
            return;
        }

        $orderId = $order->getId();
        // set controller status to delete
        $this->setControlToDelete($orderId);

        $this->storeDoorAccess(
            $em,
            $order->getId(),
            $orderUser,
            $buildingId,
            $roomId,
            $order->getStartDate(),
            $order->getEndDate()
        );
        $em->flush();

        $userArray = [];
        $userArray = $this->getUserArrayIfAuthed(
            $base,
            $orderUser,
            $userArray
        );

        if (!empty($userArray)) {
            $this->setRoomOrderAccessIfUserArray(
                $base,
                $userArray,
                $roomDoors,
                $order->getId(),
                $order->getStartDate(),
                $order->getEndDate()
            );
        }

        // remove all user access with method delete
        $this->removeUserAccess(
            $orderId,
            $base
        );
    }

    /**
     * @param $order
     * @param $now
     * @param $startDate
     * @param $endDate
     * @param $status
     * @param $type
     * @param $language
     *
     * @return array
     */
    private function setPopUpMessage(
        $order,
        $now,
        $startDate,
        $endDate,
        $status,
        $type,
        $language
    ) {
        $keyStart = null;
        $keyEnd = null;
        $number = 0;
        $alertArray = [];

        if ($status == ProductOrder::STATUS_PAID && !$order->isRejected()) {
            if ($type == Room::TYPE_MEETING || $type == Room::TYPE_STUDIO || $type == Room::TYPE_SPACE) {
                $time = clone $now;
                $time->modify('+10 minutes');

                if ($time >= $startDate) {
                    $diff = $startDate->diff($now);
                    $number = $diff->i + 1;

                    if ($type == Room::TYPE_MEETING) {
                        $keyStart = ProductOrderMessage::MEETING_START_MESSAGE;
                    } elseif ($type == Room::TYPE_STUDIO) {
                        $keyStart = ProductOrderMessage::STUDIO_START_MESSAGE;
                    } else {
                        $keyStart = ProductOrderMessage::SPACE_START_MESSAGE;
                    }
                }
            } else {
                $time = clone $now;
                $time->modify('+8 hours');

                if ($time >= $startDate) {
                    if ($type == Room::TYPE_OFFICE) {
                        $keyStart = ProductOrderMessage::OFFICE_START_MESSAGE;
                    } elseif ($type == Room::TYPE_FLEXIBLE) {
                        $keyStart = ProductOrderMessage::FLEXIBLE_START_MESSAGE;
                    } elseif ($type == Room::TYPE_FIXED) {
                        $keyStart = ProductOrderMessage::FIXED_START_MESSAGE;
                    }
                }
            }
        } elseif ($status == ProductOrder::STATUS_COMPLETED &&
            !$order->isRejected() &&
            $endDate > $now
        ) {
            if ($type == Room::TYPE_MEETING || $type == Room::TYPE_STUDIO || $type == Room::TYPE_SPACE) {
                $time = clone $now;
                $time->modify('+10 minutes');

                if ($time >= $endDate) {
                    $diff = $endDate->diff($now);
                    $number = $diff->i + 1;

                    if ($type == Room::TYPE_MEETING) {
                        $keyEnd = ProductOrderMessage::MEETING_END_MESSAGE;
                    } elseif ($type == Room::TYPE_STUDIO) {
                        $keyEnd = ProductOrderMessage::STUDIO_END_MESSAGE;
                    } else {
                        $keyEnd = ProductOrderMessage::SPACE_END_MESSAGE;
                    }
                }
            } elseif ($type == Room::TYPE_OFFICE) {
                $time = clone $now;
                $time->modify('+8 hours');
                $time->modify('+7 days');

                if ($time >= $endDate) {
                    $diff = $endDate->diff($now);
                    $number = $diff->d;

                    if ($number == 0) {
                        $number = 1;
                    }

                    $keyEnd = ProductOrderMessage::OFFICE_END_MESSAGE;
                }
            } else {
                $time = clone $now;
                $time->modify('+8 hours');

                if ($time >= $endDate) {
                    if ($type == Room::TYPE_FLEXIBLE) {
                        $keyEnd = ProductOrderMessage::FLEXIBLE_END_MESSAGE;
                    } elseif ($type == Room::TYPE_FIXED) {
                        $keyEnd = ProductOrderMessage::FIXED_END_MESSAGE;
                    }
                }
            }
        }

        if (!is_null($keyStart)) {
            $message = $this->get('translator')->trans(
                $keyStart,
                array(),
                null,
                $language
            );

            if ($number !== 0) {
                $message = preg_replace('/[0-9]+/', "$number", $message);
            }

            $alertArray = ['start_alert' => $message];
        } elseif (!is_null($keyEnd)) {
            $message = $this->get('translator')->trans(
                $keyEnd,
                array(),
                null,
                $language
            );

            if ($number !== 0) {
                $message = preg_replace('/[0-9]+/', "$number", $message);
            }

            $alertArray = ['end_alert' => $message];
        }

        return $alertArray;
    }
}
