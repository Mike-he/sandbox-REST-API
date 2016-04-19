<?php

namespace Sandbox\AdminShopApiBundle\Controller\Shop;

use Rs\Json\Patch;
use Sandbox\AdminShopApiBundle\Data\Shop\ShopOrderPriceData;
use Sandbox\ApiBundle\Constants\ShopOrderExport;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermission;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermissionMap;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminType;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Form\Shop\ShopOrderPatchType;
use Sandbox\ApiBundle\Form\Shop\ShopOrderType;
use Sandbox\ApiBundle\Traits\ShopNotification;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Shop\ShopController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Admin Shop Order Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminShopOrderController extends ShopController
{
    use ShopNotification;

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="time",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="Filter by time"
     * )
     *
     * @Annotations\QueryParam(
     *    name="shop",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="Filter by shop"
     * )
     *
     * @Method({"GET"})
     * @Route("/orders/new/sync")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getShopOrdersByTimeAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $shopId = $paramFetcher->get('shop');
        $time = $paramFetcher->get('time');

        $shop = $this->findEntityById($shopId, 'Shop\Shop');

        $orders = $this->getRepo('Shop\ShopOrder')->getAdminShopOrdersByTime(
            $shopId,
            $time
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($orders);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="shop",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by shop"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    requirements="{|paid|ready|completed|issue|waiting|refunded|}",
     *    strict=true,
     *    description="Filter by status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by payment date start"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by payment date end"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort",
     *    array=false,
     *    default="DESC",
     *    nullable=false,
     *    strict=true,
     *    description="sort direction"
     * )
     *
     * @Annotations\QueryParam(
     *    name="search",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="search by order orderNumber, username"
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
     * @Annotations\QueryParam(
     *    name="platform",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter coffee backend or kitchen ipad"
     * )
     *
     * @Method({"GET"})
     * @Route("/orders")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminShopOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminOrderPermission(
            ShopAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                ShopAdminPermission::KEY_SHOP_ORDER,
                ShopAdminPermission::KEY_SHOP_KITCHEN,
            )
        );

        // get my shop ids
        $myShopIds = $this->getMyShopIds(
            $this->getAdminId(),
            array(
                ShopAdminPermission::KEY_SHOP_ORDER,
                ShopAdminPermission::KEY_SHOP_KITCHEN,
            )
        );

        $shopId = $paramFetcher->get('shop');
        $status = $paramFetcher->get('status');
        $start = $paramFetcher->get('start');
        $end = $paramFetcher->get('end');
        $sort = $paramFetcher->get('sort');
        $search = $paramFetcher->get('search');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $platform = $paramFetcher->get('platform');

        if (!is_null($shopId) && !in_array((int) $shopId, $myShopIds)) {
            return new View();
        }

        $orders = $this->getRepo('Shop\ShopOrder')->getAdminShopOrders(
            $shopId,
            $status,
            $start,
            $end,
            $sort,
            $search,
            $platform,
            $myShopIds
        );

        $orders = $this->get('serializer')->serialize(
            $orders,
            'json',
            SerializationContext::create()->setGroups(['admin_shop'])
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
     * patch shop status.
     *
     * @param Request $request
     * @param $id
     *
     * @Method({"PATCH"})
     * @Route("/orders/{id}")
     *
     * @return View
     */
    public function patchShopOrderStatusAction(
        Request $request,
        $id
    ) {
        $order = $this->findEntityById($id, 'Shop\ShopOrder');

        // check user permission
        $this->checkAdminOrderPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_SHOP_ORDER,
                ShopAdminPermission::KEY_SHOP_KITCHEN,
            ),
            $order->getShopId()
        );

        $oldStatus = $order->getStatus();

        // bind data
        $orderJson = $this->get('serializer')->serialize($order, 'json');
        $patch = new Patch($orderJson, $request->getContent());
        $orderJson = $patch->apply();

        $form = $this->createForm(new ShopOrderPatchType(), $order);
        $form->submit(json_decode($orderJson, true));

        $now = new \DateTime();
        $status = $order->getStatus();

        $em = $this->getDoctrine()->getManager();

        $userId = $order->getUserId();
        $user = $this->findEntityById($userId, 'User\User');

        switch ($status) {
            case ShopOrder::STATUS_READY:
                if ($oldStatus != ShopOrder::STATUS_PAID) {
                    return $this->customErrorView(
                        400,
                        ShopOrder::NOT_PAID_CODE,
                        ShopOrder::NOT_PAID_MESSAGE
                    );
                }

                $this->sendXmppShopNotification(
                    ShopOrder::STATUS_READY,
                    $user,
                    $order->getOrderNumber(),
                    $order->getId()
                );

                break;
            case ShopOrder::STATUS_COMPLETED:
                if ($oldStatus != ShopOrder::STATUS_READY) {
                    return $this->customErrorView(
                        400,
                        ShopOrder::NOT_READY_CODE,
                        ShopOrder::NOT_READY_MESSAGE
                    );
                }

                break;
            case ShopOrder::STATUS_ISSUE:
                if ($oldStatus != ShopOrder::STATUS_READY && $oldStatus !== ShopOrder::STATUS_PAID) {
                    return $this->customErrorView(
                        400,
                        ShopOrder::NOT_READY_OR_PAID_CODE,
                        ShopOrder::NOT_READY_OR_PAID_MESSAGE
                    );
                }

                $this->sendXmppShopNotification(
                    ShopOrder::STATUS_ISSUE,
                    $user,
                    $order->getOrderNumber(),
                    $order->getId()
                );

                break;
            case ShopOrder::STATUS_TO_BE_REFUNDED:
                if ($oldStatus != ShopOrder::STATUS_ISSUE) {
                    return $this->customErrorView(
                        400,
                        ShopOrder::NOT_ISSUE_CODE,
                        ShopOrder::NOT_ISSUE_MESSAGE
                    );
                }

                // restock inventory
                $inventoryData = $this->getRepo('Shop\ShopOrderProduct')
                    ->getShopOrderProductInventory($order->getId());

                foreach ($inventoryData as $data) {
                    $data['item']->setInventory($data['inventory'] + $data['amount']);
                }

                $refund = $order->getPrice();

                if ($order->IsUnoriginal()) {
                    $originalOrder = $this->getRepo('Shop\ShopOrder')->findOneBy(
                        [
                            'unoriginal' => false,
                            'linkedOrderId' => $id,
                        ]
                    );

                    if (!is_null($originalOrder)) {
                        $originalOrder->setRefundAmount($originalOrder->getPrice());
                    }
                } else {
                    $order->setRefundAmount($refund);
                }

                break;
            case ShopOrder::STATUS_REFUNDED:
                // check user permission
                $this->checkAdminOrderPermission(
                    ShopAdminPermissionMap::OP_LEVEL_EDIT,
                    array(
                        ShopAdminPermission::KEY_SHOP_ORDER,
                    ),
                    $order->getShopId()
                );

                if ($oldStatus != ShopOrder::STATUS_TO_BE_REFUNDED) {
                    return $this->customErrorView(
                        400,
                        ShopOrder::NOT_TO_BE_REFUNDED_CODE,
                        ShopOrder::NOT_TO_BE_REFUNDED_MESSAGE
                    );
                }

                $linkedOrder = $this->getRepo('Shop\ShopOrder')->findOneBy(
                    [
                        'unoriginal' => true,
                        'linkedOrderId' => $id,
                    ]
                );

                if (!is_null($linkedOrder) && ShopOrder::STATUS_TO_BE_REFUNDED == $linkedOrder->getStatus()) {
                    $linkedOrder->setStatus(ShopOrder::STATUS_REFUNDED);
                }

                if ($order->getRefundAmount() > 0) {
                    $this->refundAdminShopOrder($order);

                    $this->sendXmppShopNotification(
                        ShopOrder::STATUS_REFUNDED,
                        $user,
                        $order->getOrderNumber(),
                        $order->getId()
                    );
                }

                break;
            default:
                return $this->customErrorView(
                    400,
                    ShopOrder::WRONG_STATUS_CODE,
                    ShopOrder::WRONG_STATUS_MESSAGE
                );

                break;
        }

        $order->setModificationDate($now);

        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Method({"POST"})
     * @Route("/orders/{id}/issue")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postAdminShopOrderAction(
        Request $request,
        $id
    ) {
        $oldOrder = $this->getRepo('Shop\ShopOrder')->findOneBy(
            [
                'id' => $id,
                'status' => ShopOrder::STATUS_ISSUE,
                'unoriginal' => false,
            ]
        );
        $this->throwNotFoundIfNull($oldOrder, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkAdminOrderPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_SHOP_ORDER,
                ShopAdminPermission::KEY_SHOP_KITCHEN,
            ),
            $oldOrder->getShopId()
        );

        $shop = $oldOrder->getShop();
        $this->throwNotFoundIfNull($shop, self::NOT_FOUND_MESSAGE);

        $order = new ShopOrder();

        $form = $this->createForm(new ShopOrderType(), $order);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $orderNumber = $this->getOrderNumber(ShopOrder::LETTER_HEAD);

        $order->setUserId($oldOrder->getUserId());
        $order->setShop($shop);
        $order->setOrderNumber($orderNumber);
        $order->setStatus(ShopOrder::STATUS_PAID);
        $order->setPaymentDate(new \DateTime());
        $order->setPayChannel($oldOrder->getPayChannel());
        $order->setUnoriginal(true);
        $order->setLinkedOrder($oldOrder);

        $em->persist($order);

        $oldOrder->setLinkedOrder($order);
        $oldOrder->setStatus(ShopOrder::STATUS_TO_BE_REFUNDED);

        // restock inventory
        $inventoryData = $this->getRepo('Shop\ShopOrderProduct')
            ->getShopOrderProductInventory($id);

        foreach ($inventoryData as $data) {
            $data['item']->setInventory($data['inventory'] + $data['amount']);
        }

        $newPrice = $order->getPrice();
        $oldPrice = $oldOrder->getPrice();
        if ($newPrice < $oldPrice) {
            $oldOrder->setRefundAmount($oldPrice - $newPrice);
        }

        $priceData = new ShopOrderPriceData();

        $inventoryError = $this->handleShopOrderProductPost(
            $em,
            $order,
            $shop,
            $priceData
        );

        if (!empty($inventoryError)) {
            return $this->customShopOrderErrorView(
                400,
                ShopOrder::INSUFFICIENT_INVENTORY_CODE,
                ShopOrder::INSUFFICIENT_INVENTORY_MESSAGE,
                $inventoryError
            );
        }

        if ($newPrice != $priceData->getProductPrice()) {
            return $this->customErrorView(
                400,
                self::DISCOUNT_PRICE_MISMATCH_CODE,
                self::DISCOUNT_PRICE_MISMATCH_MESSAGE
            );
        }

        $em->flush();

        return new View(['id' => $order->getId()]);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="shop",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by shop"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by payment date start"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by payment date end"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort",
     *    array=false,
     *    default="DESC",
     *    nullable=false,
     *    strict=true,
     *    description="sort direction"
     * )
     *
     * @Annotations\QueryParam(
     *    name="search",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="search by order orderNumber, username"
     * )
     *
     * @Annotations\QueryParam(
     *    name="platform",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter coffee backend or kitchen ipad"
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
        $adminId = $this->authenticateAdminCookie();

        // check user permission
        $this->checkAdminOrderPermission(
            ShopAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                ShopAdminPermission::KEY_SHOP_ORDER,
                ShopAdminPermission::KEY_SHOP_KITCHEN,
            ),
            null,
            $adminId
        );

        // get my shop ids
        $myShopIds = $this->getMyShopIds(
            $adminId,
            array(
                ShopAdminPermission::KEY_SHOP_ORDER,
            )
        );

        $language = $paramFetcher->get('language');
        $shopId = $paramFetcher->get('shop');
        $status = $paramFetcher->get('status');
        $start = $paramFetcher->get('start');
        $end = $paramFetcher->get('end');
        $sort = $paramFetcher->get('sort');
        $search = $paramFetcher->get('search');
        $platform = $paramFetcher->get('platform');

        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Sandbox Shop Orders');

        if (!is_null($status) && !empty($status)) {
            $status = explode(',', $status);
        }

        $orders = $this->getRepo('Shop\ShopOrder')->getAdminShopOrders(
            $shopId,
            $status,
            $start,
            $end,
            $sort,
            $search,
            $platform,
            $myShopIds
        );

        $excelBody = [];
        $orderCount = 0;
        $total = 0;
        $refundOrderCount = 0;
        $totalRefund = 0;
        $productArray = [];

        // set excel body
        foreach ($orders as $order) {
            $amountString = '';
            $menuString = '';
            $productString = '';

            $orderNumber = $order->getOrderNumber();
            $shopName = $order->getShop()->getName();

            $orderTime = null;
            $paymentDate = $order->getPaymentDate();

            if (!is_null($paymentDate)) {
                $orderTime = $order->getPaymentDate()->format('Y-m-d H:i:s');
            }

            // set status
            $statusKey = $order->getStatus();
            $status = $this->get('translator')->trans(
                ShopOrderExport::TRANS_SHOP_ORDER_STATUS.$statusKey,
                array(),
                null,
                $language
            );

            $orderProducts = $order->getShopOrderProducts();
            foreach ($orderProducts as $orderProduct) {
                $shopProductInfo = json_decode($orderProduct->getShopProductInfo(), true);

                $menuName = $shopProductInfo['menu']['name'];
                $productName = $shopProductInfo['name'];

                $orderProductSpecs = $orderProduct->getShopOrderProductSpecs();
                foreach ($orderProductSpecs as $orderProductSpec) {
                    $shopProductSpecInfo = json_decode($orderProductSpec->getShopProductSpecInfo(), true);

                    if ($shopProductSpecInfo['spec']['has_inventory']) {
                        $orderProductSpecItem = $this->getRepo('Shop\ShopOrderProductSpecItem')
                            ->findOneBySpecId($orderProductSpec->getId());

                        if (is_null($orderProductSpecItem)) {
                            continue;
                        }

                        $amount = $orderProductSpecItem->getAmount();
                        $itemInfo = json_decode($orderProductSpecItem->getShopProductSpecItemInfo(), true);

                        $price = $amount * $itemInfo['price'];

                        $amountString .= $amount."\n";
                    }
                }

                if ($statusKey == ShopOrder::STATUS_COMPLETED) {
                    $productId = $shopProductInfo['id'];

                    if (array_key_exists("$productId", $productArray)) {
                        $productArray[$productId]['amount'] += $amount;
                        $productArray[$productId]['price'] += $price;
                    } else {
                        $productArray["$productId"] = [
                            'menu_name' => $menuName,
                            'name' => $productName,
                            'amount' => $amount,
                            'price' => $price,
                        ];
                    }
                }

                $menuString .= $menuName."\n";
                $productString .= $productName."\n";
            }

            $user = $this->getRepo('User\UserProfile')->findOneByUserId($order->getUserId());
            $userName = $user->getName();

            $price = $order->getPrice();
            $refund = $order->getRefundAmount();

            if ($statusKey == ShopOrder::STATUS_COMPLETED) {
                ++$orderCount;

                if ($order->IsUnoriginal()) {
                    $oldOrder = $order->getLinkedOrder();
                    $oldPrice = $oldOrder->getPrice();

                    if ($oldPrice >= $price) {
                        $total += $price;
                    } else {
                        $total += $oldPrice;
                    }
                } else {
                    $total += $price;
                }
            } elseif ($statusKey == ShopOrder::STATUS_REFUNDED) {
                ++$refundOrderCount;

                $totalRefund += $refund;
            }

            $paymentChannel = $order->getPayChannel();
            if (!is_null($paymentChannel) && !empty($paymentChannel)) {
                $paymentChannel = $this->get('translator')->trans(
                    ShopOrderExport::TRANS_SHOP_ORDER_CHANNEL.$paymentChannel,
                    array(),
                    null,
                    $language
                );
            }

            // set excel body
            $body = array(
                ShopOrderExport::ORDER_NUMBER => $orderNumber,
                ShopOrderExport::SHOP_NAME => $shopName,
                ShopOrderExport::ORDER_TIME => $orderTime,
                ShopOrderExport::PRODUCT_NAME => $productString,
                ShopOrderExport::PRODUCT_TYPE => $menuString,
                ShopOrderExport::USER_NAME => $userName,
                ShopOrderExport::TOTAL_AMOUNT => $amountString,
                ShopOrderExport::TOTAL_PRICE => $price,
                ShopOrderExport::TOTAL_REFUND => $refund,
                ShopOrderExport::ORDER_STATUS => $status,
                ShopOrderExport::PAY_CHANNEL => $paymentChannel,
            );

            $excelBody[] = $body;
        }

        $headers = [
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_ORDER_NO, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_SHOP, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_ORDER_TIME, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_PRODUCT_NAME, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_PRODUCT_TYPE, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_USER, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_AMOUNT, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_TOTAL_PRICE, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_REFUND, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_STATUS, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_CHANNEL, array(), null, $language),
        ];

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($excelBody, null, 'A2');

        $phpExcelObject->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);
        $phpExcelObject->getActiveSheet()->getStyle('D2:D'.$phpExcelObject->getActiveSheet()->getHighestRow())
            ->getAlignment()->setWrapText(true);
        $phpExcelObject->getActiveSheet()->getStyle('E2:E'.$phpExcelObject->getActiveSheet()->getHighestRow())
            ->getAlignment()->setWrapText(true);
        $phpExcelObject->getActiveSheet()->getStyle('G2:G'.$phpExcelObject->getActiveSheet()->getHighestRow())
            ->getAlignment()->setWrapText(true);

        $phpExcelObject->getActiveSheet()->insertNewRowBefore($phpExcelObject->getActiveSheet()->getHighestRow() + 1, 1);
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '统计-----');
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '日期');
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(1, $phpExcelObject->getActiveSheet()->getHighestRow(), $start.' - '.$end);

        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '总订单数量');
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(1, $phpExcelObject->getActiveSheet()->getHighestRow(), $orderCount.'笔');

        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '总金销售额');
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(1, $phpExcelObject->getActiveSheet()->getHighestRow(), $total.'元');

        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '退款订单数量');
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(1, $phpExcelObject->getActiveSheet()->getHighestRow(), $refundOrderCount.'笔');

        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '退款总金额');
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(1, $phpExcelObject->getActiveSheet()->getHighestRow(), $totalRefund.'元');

        $phpExcelObject->getActiveSheet()->insertNewRowBefore($phpExcelObject->getActiveSheet()->getHighestRow() + 1, 1);
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '销售商品数表');

        $bodyArray = [];

        foreach ($productArray as $item) {
            $body = array(
                'menu_name' => $item['menu_name'],
                'name' => $item['name'],
                'amount' => $item['amount'].'个',
                'price' => $item['price'].'元',
            );

            $bodyArray[] = $body;
        }

        $phpExcelObject->setActiveSheetIndex(0)->fromArray($bodyArray, null, 'A'.($phpExcelObject->getActiveSheet()->getHighestRow() + 1));

        //set column dimension
        for ($col = ord('a'); $col <= ord('o'); ++$col) {
            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension(chr($col))->setAutoSize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('Shop Orders');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        $date = new \DateTime('now');
        $stringDate = $date->format('Y-m-d');

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'shop_orders_'.$stringDate.'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Method({"GET"})
     * @Route("/orders/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminShopOrderByIdAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Shop\ShopOrder')->getAdminShopOrderById($id);

        // check user permission
        $this->checkAdminOrderPermission(
            ShopAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                ShopAdminPermission::KEY_SHOP_ORDER,
                ShopAdminPermission::KEY_SHOP_KITCHEN,
            ),
            $order->getShopId()
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($order);

        return $view;
    }

    /**
     * authenticate with web browser cookie.
     */
    private function authenticateAdminCookie()
    {
        $cookie_name = self::SHOP_COOKIE_NAME;
        if (!isset($_COOKIE[$cookie_name])) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $token = $_COOKIE[$cookie_name];
        $adminToken = $this->getRepo('Shop\ShopAdminToken')->findOneByToken($token);
        if (is_null($adminToken)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        return $adminToken->getAdminId();
    }

    /**
     * @param $oldOrder
     */
    private function refundAdminShopOrder(
        $oldOrder
    ) {
        $balance = $this->postBalanceChange(
            $oldOrder->getUserId(),
            $oldOrder->getRefundAmount(),
            $oldOrder->getOrderNumber(),
            self::PAYMENT_CHANNEL_ACCOUNT,
            0,
            self::ORDER_REFUND
        );
    }

    /**
     * @param $adminId
     * @param $opLevel
     * @param $permissions
     * @param $shopId
     */
    private function checkAdminOrderPermission(
        $opLevel,
        $permissions,
        $shopId = null,
        $adminId = null
    ) {
        if (is_null($adminId)) {
            $adminId = $this->getAdminId();
        }

        $this->throwAccessDeniedIfShopAdminNotAllowed(
            $adminId,
            ShopAdminType::KEY_PLATFORM,
            $permissions,
            $opLevel,
            $shopId
        );
    }
}
