<?php

namespace Sandbox\AdminShopApiBundle\Controller\Shop;

use Rs\Json\Patch;
use Sandbox\AdminShopApiBundle\Data\Shop\ShopOrderPriceData;
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
    public function getShopOrderCountByTimeAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $shopId = $paramFetcher->get('shop');
        $time = $paramFetcher->get('time');

        $shop = $this->findEntityById($shopId, 'Shop\Shop');

        $count = $this->getRepo('Shop\ShopOrder')->getAdminShopOrderCount($shopId, $time);

        $view = new View();
        $view->setData(['count' => (int) $count]);

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
            $platform
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
                if ($oldStatus !== ShopOrder::STATUS_PAID) {
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
                if ($oldStatus !== ShopOrder::STATUS_READY) {
                    return $this->customErrorView(
                        400,
                        ShopOrder::NOT_READY_CODE,
                        ShopOrder::NOT_READY_MESSAGE
                    );
                }

                break;
            case ShopOrder::STATUS_ISSUE:
                if ($oldStatus !== ShopOrder::STATUS_READY && $oldStatus !== ShopOrder::STATUS_PAID) {
                    return $this->customErrorView(
                        400,
                        ShopOrder::NOT_READY_OR_PAID_CODE,
                        ShopOrder::NOT_READY_OR_PAID_MESSAGE
                    );
                }

                break;
            case ShopOrder::STATUS_TO_BE_REFUNDED:
                if ($oldStatus !== ShopOrder::STATUS_ISSUE) {
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

                if ($oldStatus !== ShopOrder::STATUS_TO_BE_REFUNDED) {
                    return $this->customErrorView(
                        400,
                        ShopOrder::NOT_TO_BE_REFUNDED_CODE,
                        ShopOrder::NOT_TO_BE_REFUNDED_MESSAGE
                    );
                }

                $this->refundAdminShopOrder($order);

                $this->sendXmppShopNotification(
                    ShopOrder::STATUS_REFUNDED,
                    $user,
                    $order->getOrderNumber(),
                    $order->getId()
                );

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
        $order->setUnoriginal(true);
        $order->setLinkedOrder($oldOrder);

        $em->persist($order);

        $oldOrder->setLinkedOrder($order);
        $oldOrder->setStatus(ShopOrder::STATUS_TO_BE_REFUNDED);

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

        if ($order->getPrice() != $priceData->getProductPrice()) {
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
     * @param $oldOrder
     */
    private function refundAdminShopOrder(
        $oldOrder
    ) {
        $userId = $oldOrder->getUserId();
        $oldPrice = $oldOrder->getPrice();
        $newOrderId = $oldOrder->getLinkedOrderId();

        if (is_null($newOrderId)) {
            $refund = $oldPrice;
        } else {
            $newOrder = $this->findEntityById($newOrderId, 'Shop\ShopOrder');
            $newPrice = $newOrder->getPrice();

            if ($oldPrice <= $newPrice) {
                return;
            } else {
                $refund = $oldPrice - $newPrice;
            }
        }

        $balance = $this->postBalanceChange(
            $userId,
            $refund,
            $oldOrder->getOrderNumber(),
            self::PAYMENT_CHANNEL_ACCOUNT,
            0,
            self::ORDER_REFUND
        );
    }

    /**
     * @param $opLevel
     * @param $permissions
     * @param $shopId
     */
    private function checkAdminOrderPermission(
        $opLevel,
        $permissions,
        $shopId = null
    ) {
        $this->throwAccessDeniedIfShopAdminNotAllowed(
            $this->getAdminId(),
            ShopAdminType::KEY_PLATFORM,
            $permissions,
            $opLevel,
            $shopId
        );
    }
}
