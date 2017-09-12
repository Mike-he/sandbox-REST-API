<?php

namespace Sandbox\AdminApiBundle\Controller\Order;

use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentServiceBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Order\OrderOfflineTransfer;
use Sandbox\ApiBundle\Entity\User\User;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Controller\Order\OrderController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Form\Order\OrderOfflineTransferPatch;
use Sandbox\ApiBundle\Form\Order\OrderRefundFeePatch;
use Sandbox\ApiBundle\Form\Order\OrderRefundPatch;
use Sandbox\ApiBundle\Form\Order\OrderReserveType;
use Sandbox\ApiBundle\Form\Order\PreOrderType;
use Sandbox\ApiBundle\Traits\FinanceTrait;
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
    use FinanceTrait;
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="order_number",
     *     array=false
     * )
     *
     * @Route("/orders/ids")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getOrderByOrderNumberAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $orderNumber = $paramFetcher->get('order_number');

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrderIdsByOrderNumber($orderNumber);

        return new View($order);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="ids",
     *     array=true
     * )
     *
     * @Route("/orders/numbers")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getOrdersNumbersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $ids = $paramFetcher->get('ids');

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersNumbers(
                $ids
            );

        $response = array();
        foreach ($orders as $order) {
            array_push($response, array(
                'id' => $order->getId(),
                'order_number' => $order->getOrderNumber(),
                'company_name' => $order->getProduct()->getRoom()->getBuilding()->getCompany()->getName(),
            ));
        }

        return new View($response);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit number"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number"
     * )
     *
     * @Annotations\QueryParam(
     *     name="sales_invoice",
     *     array=false,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by sales company id"
     * )
     *
     * @Annotations\QueryParam(
     *     name="trade_number",
     *     array=false,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="amount_min",
     *     array=false,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="amount_max",
     *     array=false,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Route("/orders/sales/notinvoiced")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesInvoiceOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_INVOICE],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        // filters
        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');
        $salesInvoice = $paramFetcher->get('sales_invoice');
        $salesCompanyId = $paramFetcher->get('company');
        $tradeNumber = $paramFetcher->get('trade_number');
        $amountMin = $paramFetcher->get('amount_min');
        $amountMax = $paramFetcher->get('amount_max');

        $tradeNumbers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Invoice\TradeInvoiceView')
            ->getAdminTradeNumbers(
                $tradeNumber,
                $salesInvoice
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
                    $responseArray = array();
                    break;
            }

            // filters
            if (!is_null($salesCompanyId) && $responseArray['company_id'] != $salesCompanyId) {
                continue;
            }

            if (!is_null($amountMin) && $responseArray['amount'] < (float) $amountMin) {
                continue;
            }

            if (!is_null($amountMax) && $responseArray['amount'] > (float) $amountMax) {
                continue;
            }

            array_push($response, $responseArray);
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $response,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

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
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermission::OP_LEVEL_EDIT);

        $order = $this->getDoctrine()->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->findOneBy(
                [
                    'id' => $id,
                    'status' => ProductOrder::STATUS_CANCELLED,
                    'needToRefund' => true,
                    'refunded' => false,
                    'refundProcessed' => true,
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
        $channel = $order->getPayChannel();
        $view = new View();

        if (!$refunded) {
            return $view;
        }

        if ($channel == ProductOrder::CHANNEL_UNIONPAY) {
            $ssn = $order->getRefundSSN();

            if (is_null($ssn) || empty($ssn)) {
                return $this->customErrorView(
                    400,
                    self::REFUND_SSN_NOT_FOUND_CODE,
                    self::REFUND_SSN_NOT_FOUND_MESSAGE
                );
            }
        }

        $order->setNeedToRefund(false);
        $order->setModificationDate(new \DateTime());

        if ($order->getType() == ProductOrder::PREORDER_TYPE) {
            $this->generateRefundOrderWalletFlow(
                $order->getOrderNumber(),
                $order->getProduct()->getRoom()->getBuilding()->getCompanyId(),
                $order->getDiscountPrice(),
                $order->getActualRefundAmount(),
                $channel,
                FinanceLongRentServiceBill::TYPE_BILL_POUNDAGE
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $view;
    }

    /**
     * @Route("/orders/{id}/transfer")
     * @Method({"PATCH"})
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function patchTransferStatusAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_REFUND],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_USER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_TRANSFER_CONFIRM],
            ],
            AdminPermission::OP_LEVEL_EDIT
        );

        $order = $this->getRepo('Order\ProductOrder')->findOneBy(
            [
                'id' => $id,
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

        $existTransfer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\OrderOfflineTransfer')
            ->findOneByOrderId($id);
        $this->throwNotFoundIfNull($existTransfer, self::NOT_FOUND_MESSAGE);

        $oldStatus = $existTransfer->getTransferStatus();

        // bind data
        $transferJson = $this->container->get('serializer')->serialize($existTransfer, 'json');
        $patch = new Patch($transferJson, $request->getContent());
        $transferJson = $patch->apply();

        $form = $this->createForm(new OrderOfflineTransferPatch(), $existTransfer);
        $form->submit(json_decode($transferJson, true));

        $status = $existTransfer->getTransferStatus();
        $userId = $order->getUserId();
        $channel = $order->getPayChannel();
        $orderNumber = $order->getOrderNumber();
        $price = $order->getDiscountPrice();
        $now = new \DateTime();

        switch ($status) {
            case OrderOfflineTransfer::STATUS_PAID:
                if ($oldStatus != OrderOfflineTransfer::STATUS_PENDING) {
                    return $this->customErrorView(
                        400,
                        self::WRONG_ORDER_STATUS_CODE,
                        self::WRONG_ORDER_STATUS_MESSAGE
                    );
                }

                $order->setStatus(ProductOrder::STATUS_PAID);
                $order->setPaymentDate($now);
                $order->setModificationDate($now);

                $balance = $this->postBalanceChange(
                    $userId,
                    0,
                    $orderNumber,
                    $channel,
                    $price
                );

                break;
            case OrderOfflineTransfer::STATUS_RETURNED:
                if ($oldStatus != OrderOfflineTransfer::STATUS_PENDING) {
                    return $this->customErrorView(
                        400,
                        self::WRONG_ORDER_STATUS_CODE,
                        self::WRONG_ORDER_STATUS_MESSAGE
                    );
                }

                // send message
                $this->sendXmppProductOrderNotification(
                    null,
                    null,
                    ProductOrder::ACTION_RETURNED,
                    null,
                    [$order],
                    ProductOrderMessage::ORDER_TRANSFER_RETURNED_MESSAGE
                );

                break;
            case OrderOfflineTransfer::STATUS_REJECT_REFUND:
                if ($oldStatus != OrderOfflineTransfer::STATUS_VERIFY) {
                    return $this->customErrorView(
                        400,
                        self::WRONG_ORDER_STATUS_CODE,
                        self::WRONG_ORDER_STATUS_MESSAGE
                    );
                }

                $order->setStatus(ProductOrder::STATUS_CANCELLED);
                $order->setCancelledDate($now);
                $order->setModificationDate($now);

                break;
            case OrderOfflineTransfer::STATUS_ACCEPT_REFUND:
                if ($oldStatus != OrderOfflineTransfer::STATUS_VERIFY) {
                    return $this->customErrorView(
                        400,
                        self::WRONG_ORDER_STATUS_CODE,
                        self::WRONG_ORDER_STATUS_MESSAGE
                    );
                }

                $order->setStatus(ProductOrder::STATUS_CANCELLED);
                $order->setCancelledDate($now);
                $order->setModificationDate($now);
                $refundChannel = $order->getRefundTo();

                if ($price > 0) {
                    $order->setNeedToRefund(true);

                    $this->postBalanceChange(
                        $userId,
                        0,
                        $orderNumber,
                        $channel,
                        $price
                    );

                    if ($refundChannel == ProductOrder::CHANNEL_ACCOUNT) {
                        $balance = $this->postBalanceChange(
                            $userId,
                            $price,
                            $orderNumber,
                            self::PAYMENT_CHANNEL_ACCOUNT,
                            0,
                            self::ORDER_REFUND
                        );

                        if (!is_null($balance)) {
                            $order->setRefunded(true);
                            $order->setNeedToRefund(false);

                            $amount = $this->postConsumeBalance(
                                $userId,
                                $price,
                                $orderNumber
                            );

                            $TopUpOrderNumber = $this->getOrderNumber(self::TOPUP_ORDER_LETTER_HEAD);
                            $this->setTopUpOrder(
                                $userId,
                                $price,
                                $TopUpOrderNumber,
                                $channel,
                                true,
                                $orderNumber
                            );
                        }
                    }
                }

                break;
        }

        $existTransfer->setModificationDate($now);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
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
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermission::OP_LEVEL_EDIT);

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
        $refund = (float) $order->getDiscountPrice();

        $multiplier = $this->getRefundFeeMultiplier($channel);

        $fee = round($refund * $multiplier, 2);
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
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermission::OP_LEVEL_EDIT);

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
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermission::OP_LEVEL_EDIT);

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
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermission::OP_LEVEL_EDIT);

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
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

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
        if (is_null($base) || empty($base)) {
            return;
        }

        $orderControl = $this->getRepo('Door\DoorAccess')->findOneByAccessNo($id);

        $this->syncAccessByOrder($base, $orderControl);

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
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="channel",
     *    default=null,
     *    nullable=true,
     *    array=true,
     *    description="payment channel"
     * )
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
     *    name="create_date_range",
     *    default=null,
     *    nullable=true,
     *    description="create_date_range"
     * )
     *
     * @Annotations\QueryParam(
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
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Order Status"
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
     *    name="refundStatus",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="{|refunded|needToRefund|all|}",
     *    strict=true,
     *    description="refund status filter for order "
     * )
     *
     * @Annotations\QueryParam(
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by sales company id"
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
     *    name="room",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by room id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="refund_amount_low",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="refund amount filter"
     * )
     *
     * @Annotations\QueryParam(
     *    name="refund_amount_high",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="refund amount filter"
     * )
     *
     * @Annotations\QueryParam(
     *    name="refund_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="refund_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
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

        $this->checkAdminOrderPermission($adminId, AdminPermission::OP_LEVEL_VIEW);

        //filters
        $type = $paramFetcher->get('type');
        $channel = $paramFetcher->get('channel');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $createDateRange = $paramFetcher->get('create_date_range');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $status = $paramFetcher->get('status');
        $rentFilter = $paramFetcher->get('rent_filter');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');
        $payDate = $paramFetcher->get('pay_date');
        $payStart = $paramFetcher->get('pay_start');
        $payEnd = $paramFetcher->get('pay_end');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $refundStatus = $paramFetcher->get('refundStatus');
        $companyId = $paramFetcher->get('company');
        $buildingId = $paramFetcher->get('building');
        $roomId = $paramFetcher->get('room');
        $refundLow = $paramFetcher->get('refund_amount_low');
        $refundHigh = $paramFetcher->get('refund_amount_high');
        $refundStart = $paramFetcher->get('refund_start');
        $refundEnd = $paramFetcher->get('refund_end');
        $userId = $paramFetcher->get('user');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $company = !is_null($companyId) ? $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->find($companyId) : null;
        $building = !is_null($buildingId) ? $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId) : null;

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersForAdmin(
                $channel,
                $type,
                null,
                $company,
                $building,
                $roomId,
                $userId,
                $rentFilter,
                $startDate,
                $endDate,
                $payDate,
                $payStart,
                $payEnd,
                $keyword,
                $keywordSearch,
                $createDateRange,
                $createStart,
                $createEnd,
                $status,
                $refundStatus,
                $refundLow,
                $refundHigh,
                $refundStart,
                $refundEnd,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countOrdersForAdmin(
                $channel,
                $type,
                null,
                $company,
                $building,
                $roomId,
                $userId,
                $rentFilter,
                $startDate,
                $endDate,
                $payDate,
                $payStart,
                $payEnd,
                $keyword,
                $keywordSearch,
                $createDateRange,
                $createStart,
                $createEnd,
                $status,
                $refundStatus,
                $refundLow,
                $refundHigh,
                $refundStart,
                $refundEnd
            );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_detail']));
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
     * Export orders to excel.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     *    default=null,
     *    nullable=true,
     *    array=true,
     *    description="payment channel"
     * )
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
     *    name="create_date_range",
     *    default=null,
     *    nullable=true,
     *    description="create_date_range"
     * )
     *
     * @Annotations\QueryParam(
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
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Order Status"
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
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by sales company id"
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
            $adminId,
            AdminPermission::OP_LEVEL_VIEW,
            AdminPermission::PERMISSION_PLATFORM_OFFICIAL
        );

        $language = $paramFetcher->get('language');
        $type = $paramFetcher->get('type');
        $channel = $paramFetcher->get('channel');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $createDateRange = $paramFetcher->get('create_date_range');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $status = $paramFetcher->get('status');
        $rentFilter = $paramFetcher->get('rent_filter');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');
        $payDate = $paramFetcher->get('pay_date');
        $payStart = $paramFetcher->get('pay_start');
        $payEnd = $paramFetcher->get('pay_end');
        $companyId = $paramFetcher->get('company');
        $buildingId = $paramFetcher->get('building');

        $company = !is_null($companyId) ? $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->find($companyId) : null;
        $building = !is_null($buildingId) ? $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId) : null;

        //get array of orders
        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersToExport(
                $channel,
                $type,
                null,
                $company,
                $building,
                null,
                $rentFilter,
                $startDate,
                $endDate,
                $payDate,
                $payStart,
                $payEnd,
                $keyword,
                $keywordSearch,
                $createDateRange,
                $createStart,
                $createEnd,
                $status
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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_USER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_INVOICE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_PREORDER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_RESERVE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT_APPOINTMENT_VERIFY],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_DASHBOARD],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_REFUND],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_TRANSFER_CONFIRM],
            ],
            AdminPermission::OP_LEVEL_VIEW
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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_RESERVE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT_APPOINTMENT_VERIFY],
            ],
            AdminPermission::OP_LEVEL_EDIT
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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_PREORDER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT_APPOINTMENT_VERIFY],
            ],
            AdminPermission::OP_LEVEL_EDIT
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
            $order->setType(ProductOrder::OFFICIAL_PREORDER_TYPE);

            if (0 == $order->getDiscountPrice()) {
                $order->setStatus(ProductOrder::STATUS_PAID);
                $order->setPaymentDate($now);
            }

            // set order drawer
            $order->setSalesInvoice(false);

            $em->persist($order);

            // store order record
            $this->storeRoomRecord(
                $em,
                $order,
                $product,
                $timeUnit
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
     * @Route("/orders/{id}/cancel")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param $id
     *
     * @return View
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

        $adminId = $this->getAdminId();

        $type = $order->getType();

        // check user permission
        $permissions[]['key'] = AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER;

        if (ProductOrder::RESERVE_TYPE == $type) {
            $permissions[]['key'] = AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_RESERVE;
        } elseif (ProductOrder::PREORDER_TYPE) {
            $permissions[]['key'] = AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_PREORDER;
        } else {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            $permissions,
            AdminPermission::OP_LEVEL_EDIT
        );

        $now = new \DateTime();
        $status = $order->getStatus();

        if (ProductOrder::STATUS_CANCELLED == $status
            || $order->getEndDate() <= $now
            || $order->isInvoiced()
        ) {
            return $this->customErrorView(
                400,
                self::WRONG_PAYMENT_STATUS_CODE,
                self::WRONG_PAYMENT_STATUS_MESSAGE
            );
        }

        $order->setCancelByUser(true);

        if (ProductOrder::PREORDER_TYPE == $type && $status != ProductOrder::STATUS_UNPAID) {
            if (ProductOrder::STATUS_COMPLETED == $status) {
                return $this->customErrorView(
                    400,
                    self::WRONG_PAYMENT_STATUS_CODE,
                    self::WRONG_PAYMENT_STATUS_MESSAGE
                );
            }

            $price = $order->getDiscountPrice();
            $channel = $order->getPayChannel();
            $userId = $order->getUserId();
            $order->setModificationDate($now);

            if ($price > 0) {
                $order->setNeedToRefund(true);

                if (ProductOrder::CHANNEL_ACCOUNT == $channel) {
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
                    }
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

        // send message
        $this->sendXmppProductOrderNotification(
            null,
            null,
            ProductOrder::ACTION_CANCELLED,
            null,
            [$order],
            ProductOrderMessage::ORDER_ADMIN_CANCELLED_MESSAGE
        );

        return new View();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     * @param int $adminId
     */
    private function checkAdminOrderPermission(
        $adminId,
        $opLevel,
        $platform = null
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_REFUND],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_USER],
            ],
            $opLevel,
            $platform
        );
    }
}
