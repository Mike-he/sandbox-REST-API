<?php

namespace Sandbox\SalesApiBundle\Controller\Order;

use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Controller\Order\OrderController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Order\OrderOfflineTransfer;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Form\Order\OrderOfflineTransferPost;
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
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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

        $numbers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersNumbers(
                $ids
            );

        return new View($numbers);
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
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by room type"
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
     *    name="orderStartDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="orderEndDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="payStartDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="payEndDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="rentStartDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="rentEndDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="invoiceStartDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="invoiceEndDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_INVOICE,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        // get sales company id
        $salesCompanyId = $this->getSalesCompanyId();

        // filters
        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');
        $type = $paramFetcher->get('type');
        $buildingId = $paramFetcher->get('building');
        $orderStartDate = $paramFetcher->get('orderStartDate');
        $orderEndDate = $paramFetcher->get('orderEndDate');
        $payStartDate = $paramFetcher->get('payStartDate');
        $payEndDate = $paramFetcher->get('payEndDate');
        $rentStartDate = $paramFetcher->get('rentStartDate');
        $rentEndDate = $paramFetcher->get('rentEndDate');
        $invoiceStartDate = $paramFetcher->get('invoiceStartDate');
        $invoiceEndDate = $paramFetcher->get('invoiceEndDate');

        $ordersQuery = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getAdminNotInvoicedOrders(
                $type,
                $buildingId,
                $orderStartDate,
                $orderEndDate,
                $payStartDate,
                $payEndDate,
                $rentStartDate,
                $rentEndDate,
                $invoiceStartDate,
                $invoiceEndDate,
                $salesCompanyId
            );

        $ordersQuery = $this->get('serializer')->serialize(
            $ordersQuery,
            'json',
            SerializationContext::create()->setGroups(['admin_detail'])
        );
        $ordersQuery = json_decode($ordersQuery, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $ordersQuery,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

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
        $em = $this->getDoctrine()->getManager();
        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrderByIdAndStatus($id);

        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }

        $buildingId = $order->getProduct()->getRoom()->getBuildingId();

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
                    'building_id' => $buildingId,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
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
        $productId = $order->getProductId();
        $startDate = $order->getStartDate();
        $endDate = $order->getEndDate();
        $status = $order->getStatus();

        if ($newRejected) {
            if ($channel == ProductOrder::CHANNEL_OFFLINE && $status == ProductOrder::STATUS_UNPAID) {
                $existTransfer = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Order\OrderOfflineTransfer')
                    ->findOneByOrderId($order->getId());
                $this->throwNotFoundIfNull($existTransfer, self::NOT_FOUND_MESSAGE);

                $transferStatus = $existTransfer->getTransferStatus();
                if ($transferStatus == OrderOfflineTransfer::STATUS_UNPAID) {
                    $order->setStatus(ProductOrder::STATUS_CANCELLED);
                    $order->setCancelledDate(new \DateTime());
                    $order->setModificationDate(new \DateTime());
                } else {
                    $existTransfer->setTransferStatus(OrderOfflineTransfer::STATUS_VERIFY);
                }
            } else {
                $order->setStatus(ProductOrder::STATUS_CANCELLED);
                $order->setCancelledDate($now);
                $order->setModificationDate($now);
                $order->setCancelByUser(true);

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
            }

            $action = Log::ACTION_REJECT;

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
            $action = Log::ACTION_AGREE;

            if ($status != ProductOrder::STATUS_PAID) {
                return $this->customErrorView(
                    400,
                    self::WRONG_ORDER_STATUS_CODE,
                    self::WRONG_ORDER_STATUS_MESSAGE
                );
            }

            $acceptedOrders = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Order\ProductOrder')
                ->getOfficeAccepted(
                    $productId,
                    $startDate,
                    $endDate
                );

            if (!empty($acceptedOrders)) {
                throw new ConflictHttpException();
            }

            // set door access
            $this->setDoorAccessForSingleOrder($order, $em);

            // set invoice amount
            if ($order->getStartDate() <= $now) {
                $order->setStatus(ProductOrder::STATUS_COMPLETED);
                $order->setModificationDate($now);

                if ($price > 0 &&
                    $channel != ProductOrder::CHANNEL_ACCOUNT &&
                    !$order->isSalesInvoice()
                ) {
                    $amount = $this->postConsumeBalance(
                        $userId,
                        $price,
                        $order->getOrderNumber()
                    );

                    if (!is_null($amount)) {
                        $order->setInvoiced(true);
                    }
                }
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

            $orders = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Order\ProductOrder')
                ->getOfficeRejected(
                    $productId,
                    $startDate,
                    $endDate,
                    null,
                    $order->getId()
                );

            foreach ($orders as $rejectedOrder) {
                $status = $rejectedOrder->getStatus();
                $channel = $rejectedOrder->getPayChannel();

                if ($channel == ProductOrder::CHANNEL_OFFLINE && $status == ProductOrder::STATUS_UNPAID) {
                    $existTransfer = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Order\OrderOfflineTransfer')
                        ->findOneByOrderId($rejectedOrder->getId());
                    $this->throwNotFoundIfNull($existTransfer, self::NOT_FOUND_MESSAGE);

                    $transferStatus = $existTransfer->getTransferStatus();
                    if ($transferStatus == OrderOfflineTransfer::STATUS_UNPAID) {
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
                    }

                    $em->flush();

                    $this->generateAdminLogs(array(
                        'logModule' => Log::MODULE_ROOM_ORDER,
                        'logAction' => Log::ACTION_REJECT,
                        'logObjectKey' => Log::OBJECT_ROOM_ORDER,
                        'logObjectId' => $rejectedOrder->getId(),
                    ));
                }

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

        $em->flush();

        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_ROOM_ORDER,
            'logAction' => $action,
            'logObjectKey' => Log::OBJECT_ROOM_ORDER,
            'logObjectId' => $id,
        ));

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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
                    'building_id' => $buildingId,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
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
     *    name="room",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by room id"
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

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

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
        $roomId = $paramFetcher->get('room');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

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
                $channel,
                $type,
                null,
                null,
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
                $createDateRange,
                $createStart,
                $createEnd,
                $status,
                $roomId,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countSalesOrdersForAdmin(
                $channel,
                $type,
                null,
                null,
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
                $createDateRange,
                $createStart,
                $createEnd,
                $status,
                $roomId
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
     *    nullable=false,
     *    strict=true,
     *    description="company id"
     * )
     *
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
        $companyId = $paramFetcher->get('company');

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW,
            AdminPermission::PERMISSION_PLATFORM_SALES,
            $companyId
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

        //get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $adminId,
            array(
                AdminPermission::KEY_SALES_BUILDING_ORDER,
            ),
            null,
            AdminPermission::PERMISSION_PLATFORM_SALES,
            $companyId
        );

        //get array of orders
        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getSalesOrdersToExport(
                $channel,
                $type,
                null,
                null,
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
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $buildingId = $order->getProduct()->getRoom()->getBuildingId();

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
                    'building_id' => $buildingId,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_USER,
                    'building_id' => $buildingId,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_INVOICE,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
                    'building_id' => $buildingId,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
                    'building_id' => $buildingId,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
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
            $this->throwAccessDeniedIfAdminNotAllowed(
                $this->getAdminId(),
                array(
                    array(
                        'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
                        'building_id' => $buildingId,
                    ),
                    array(
                        'key' => AdminPermission::KEY_SALES_BUILDING_SPACE,
                        'building_id' => $buildingId,
                    ),
                ),
                AdminPermission::OP_LEVEL_EDIT
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

            $this->generateAdminLogs(array(
                'logModule' => Log::MODULE_ORDER_RESERVE,
                'logAction' => Log::ACTION_CREATE,
                'logObjectKey' => Log::OBJECT_ROOM_ORDER,
                'logObjectId' => $order->getId(),
            ));

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
     * @Route("/orders/{id}/transfer")
     * @Method({"PATCH"})
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function patchTransferNoAction(
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

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
                    'building_id' => $buildingId,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $existTransfer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\OrderOfflineTransfer')
            ->findOneByOrderId($id);
        $this->throwNotFoundIfNull($existTransfer, self::NOT_FOUND_MESSAGE);

        // bind data
        $transferJson = $this->container->get('serializer')->serialize($existTransfer, 'json');
        $patch = new Patch($transferJson, $request->getContent());
        $transferJson = $patch->apply();

        $form = $this->createForm(new OrderOfflineTransferPost(), $existTransfer);
        $form->submit(json_decode($transferJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_ROOM_ORDER,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_ROOM_ORDER,
            'logObjectId' => $id,
        ));

        return new View();
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

        $buildingId = $order->getProduct()->getRoom()->getBuildingId();
        $adminId = $this->getAdminId();

        $type = $order->getType();
        $module = Log::MODULE_ORDER_PREORDER;

        // check user permission
        $permissions = array(array(
            'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
            'building_id' => $buildingId,
        ));

        if (ProductOrder::RESERVE_TYPE == $type) {
            $module = Log::MODULE_ORDER_RESERVE;
            array_push($permissions, array(
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
                'building_id' => $buildingId,
            ));
        } elseif (ProductOrder::PREORDER_TYPE == $type) {
            array_push($permissions, array(
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
                'building_id' => $buildingId,
            ));
        } else {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            $permissions,
            AdminPermission::OP_LEVEL_VIEW
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
        $payDate = $order->getPaymentDate();

        if (ProductOrder::PREORDER_TYPE == $type && !is_null($payDate)) {
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

        $this->generateAdminLogs(array(
            'logModule' => $module,
            'logAction' => Log::ACTION_CANCEL,
            'logObjectKey' => Log::OBJECT_ROOM_ORDER,
            'logObjectId' => $order->getId(),
        ));

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
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->find($productId);
            $buildingId = $product->getRoom()->getBuildingId();

            // check user permission
            $this->throwAccessDeniedIfAdminNotAllowed(
                $adminId,
                array(
                    array(
                        'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
                        'building_id' => $buildingId,
                    ),
                    array(
                        'key' => AdminPermission::KEY_SALES_BUILDING_SPACE,
                        'building_id' => $buildingId,
                    ),
                ),
                AdminPermission::OP_LEVEL_EDIT
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

            // set order drawer
            $this->setOrderDrawer(
                $product,
                $order
            );

            // set service fee
            $companyId = $product->getRoom()->getBuilding()->getCompanyId();
            $serviceInfo = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
                ->findOneBy([
                    'companyId' => $companyId,
                    'roomTypes' => $type,
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

            // set door access
            if (0 == $order->getDiscountPrice()) {
                $this->setDoorAccessForSingleOrder($order, $em);
            }

            $this->generateAdminLogs(array(
                'logModule' => Log::MODULE_ORDER_PREORDER,
                'logAction' => Log::ACTION_CREATE,
                'logObjectKey' => Log::OBJECT_ROOM_ORDER,
                'logObjectId' => $order->getId(),
            ));

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
}
