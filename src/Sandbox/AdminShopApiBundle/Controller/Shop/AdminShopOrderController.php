<?php

namespace Sandbox\AdminShopApiBundle\Controller\Shop;

use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Form\Shop\ShopOrderPatchType;
use Sandbox\ApiBundle\Form\Shop\ShopOrderType;
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
 * @author   Leo Xue <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminShopOrderController extends ShopController
{
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
     *    requirements="{ready|completed|issue|waiting|refunded}",
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
        $shopId = $paramFetcher->get('shop');
        $status = $paramFetcher->get('status');
        $start = $paramFetcher->get('start');
        $end = $paramFetcher->get('end');
        $search = $paramFetcher->get('search');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $orders = $this->getRepo('Shop\ShopOrder')->getAdminShopOrders(
            $shopId,
            $status,
            $start,
            $end,
            $search
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
     * @Route("/orders/{id}/status")
     *
     * @return View
     */
    public function patchShopOrderStatusAction(
        Request $request,
        $id
    ) {
        //TODO: Check Coffee Admin Permission
        $order = $this->findEntityById($id, 'Shop\ShopOrder');
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

        switch ($status) {
            case ShopOrder::STATUS_READY:
                if ($oldStatus !== ShopOrder::STATUS_PAID) {
                    return $this->customErrorView(
                        400,
                        ShopOrder::NOT_PAID_CODE,
                        ShopOrder::NOT_PAID_MESSAGE
                    );
                }

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
                //TODO: Check Coffee Admin Backend Permission
                //TODO: Throw Exception If No Permission
                if ($oldStatus !== ShopOrder::STATUS_TO_BE_REFUNDED) {
                    return $this->customErrorView(
                        400,
                        ShopOrder::NOT_TO_BE_REFUNDED_CODE,
                        ShopOrder::NOT_TO_BE_REFUNDED_MESSAGE
                    );
                }

                //TODO: Refund

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

        $calculatedPrice = 0;
        $calculatedPrice = $this->handleShopOrderProductPost(
            $em,
            $order,
            $shop,
            $calculatedPrice
        );

        if ($order->getPrice() != $calculatedPrice) {
            return $this->customErrorView(
                400,
                self::DISCOUNT_PRICE_MISMATCH_CODE,
                self::DISCOUNT_PRICE_MISMATCH_MESSAGE
            );
        }

        $em->flush();

        return new View(['id' => $order->getId()]);
    }
}
