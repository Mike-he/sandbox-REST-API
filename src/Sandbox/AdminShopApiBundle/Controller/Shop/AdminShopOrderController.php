<?php

namespace Sandbox\AdminShopApiBundle\Controller\Shop;

use Rs\Json\Patch;
use Sandbox\AdminShopApiBundle\Data\Shop\ShopOrderPriceData;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_ORDER,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        // get my shop ids
        $myShopIds = $this->getMyShopIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SHOP_SHOP_ORDER,
                AdminPermission::KEY_SHOP_SHOP_KITCHEN,
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
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $count = 0;

        if (!is_null($shopId) && !in_array((int) $shopId, $myShopIds)) {
            return new View();
        }

        if ($platform == ShopOrder::PLATFORM_BACKEND) {
            $offset = ($pageIndex - 1) * $pageLimit;
            $limit = $pageLimit;

            $count = $this->getRepo('Shop\ShopOrder')->countAdminShopOrders(
                $shopId,
                $status,
                $start,
                $end,
                $search,
                $myShopIds
            );
        }

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Shop\ShopOrder')
            ->getAdminShopOrders(
                $shopId,
                $status,
                $start,
                $end,
                $sort,
                $search,
                $platform,
                $myShopIds,
                $limit,
                $offset
            );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));

        // using limit and offset instead of pagination
        if ($platform == ShopOrder::PLATFORM_KITCHEN) {
            $view->setData($orders);
        } elseif ($platform == ShopOrder::PLATFORM_BACKEND) {
            $view->setData(
                array(
                    'current_page_number' => $pageIndex,
                    'num_items_per_page' => (int) $pageLimit,
                    'items' => $orders,
                    'total_count' => (int) $count,
                )
            );
        }

        return $view;
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_ORDER,
                    'shop_id' => $order->getShopId(),
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
                    'shop_id' => $order->getShopId(),
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
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

                if ($order->IsUnoriginal()) {
                    $originalOrder = $this->getRepo('Shop\ShopOrder')->findOneBy(
                        [
                            'unoriginal' => false,
                            'linkedOrderId' => $id,
                        ]
                    );

                    if (!is_null($originalOrder)) {
                        $price = $originalOrder->getPrice();
                        $refund = $originalOrder->getRefundAmount();

                        if ($price > $refund) {
                            $invoice = $price - $refund;

                            // set invoice amount
                            $amount = $this->postConsumeBalance(
                                $userId,
                                $invoice,
                                $order->getOrderNumber()
                            );
                        }
                    }
                } else {
                    // set invoice amount
                    $amount = $this->postConsumeBalance(
                        $userId,
                        $order->getPrice(),
                        $order->getOrderNumber()
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
                if ($oldStatus != ShopOrder::STATUS_TO_BE_REFUNDED || $order->IsUnoriginal()) {
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_ORDER,
                    'shop_id' => $oldOrder->getShopId(),
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
                    'shop_id' => $oldOrder->getShopId(),
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
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
     *    name="sales_company",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="company id"
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
     *    default="backend",
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
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();
        $companyId = $paramFetcher->get('sales_company');

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_ORDER,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW,
            AdminPermission::PERMISSION_PLATFORM_SHOP,
            $companyId
        );

        // get my shop ids
        $myShopIds = $this->getMyShopIds(
            $adminId,
            array(
                AdminPermission::KEY_SHOP_SHOP_ORDER,
            ),
            null,
            AdminPermission::PERMISSION_PLATFORM_SHOP,
            $companyId
        );

        $language = $paramFetcher->get('language');
        $shopId = $paramFetcher->get('shop');
        $status = $paramFetcher->get('status');
        $start = $paramFetcher->get('start');
        $end = $paramFetcher->get('end');
        $sort = $paramFetcher->get('sort');
        $search = $paramFetcher->get('search');
        $platform = $paramFetcher->get('platform');

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
            $myShopIds,
            null,
            null
        );

        return $this->getShopOrderExport($orders, $language);
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_ORDER,
                    'shop_id' => $order->getShopId(),
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
                    'shop_id' => $order->getShopId(),
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($order);

        return $view;
    }

    /**
     * @param $oldOrder
     */
    private function refundAdminShopOrder(
        $oldOrder
    ) {
        $channel = $oldOrder->getPayChannel();
        $refund = $oldOrder->getRefundAmount();
        $oldOrder->setNeedToRefund(true);

        if (ShopOrder::CHANNEL_ACCOUNT == $channel) {
            $balance = $this->postBalanceChange(
                $oldOrder->getUserId(),
                $refund,
                $oldOrder->getOrderNumber(),
                self::PAYMENT_CHANNEL_ACCOUNT,
                0,
                self::ORDER_REFUND
            );

            $oldOrder->setRefundProcessed(true);
            $oldOrder->setRefundProcessedDate(new \DateTime());

            if (!is_null($balance)) {
                $oldOrder->setRefunded(true);
                $oldOrder->setNeedToRefund(false);
            }
        }
    }
}
