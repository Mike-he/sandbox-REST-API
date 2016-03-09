<?php

namespace Sandbox\ClientApiBundle\Controller\Shop;

use Sandbox\AdminShopApiBundle\Controller\ShopRestController;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Form\Shop\ShopOrderPayChannelType;
use Sandbox\ApiBundle\Form\Shop\ShopOrderType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * Client ShopOrder Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xue <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientShopOrderController extends ShopRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @Route("/shops/orders")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getShopOrdersByUserAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $order = $this->getRepo('Shop\ShopOrder')->findBy(
            [
                'userId' => $userId,
                'unoriginal' => false,
            ],
            ['modificationDate' => 'DESC'],
            $limit,
            $offset
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_order']));
        $view->setData($order);

        return $view;
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Method({"GET"})
     * @Route("/shops/orders/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getShopOrderByIdAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        $order = $this->getRepo('Shop\ShopOrder')->findOneBy(
            [
                'userId' => $userId,
                'id' => $id,
            ]
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_order']));
        $view->setData($order);

        return $view;
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Method({"POST"})
     * @Route("/shops/{id}/orders")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postShopOrderAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        $shop = $this->getRepo('Shop\Shop')->getShopById(
            $id,
            true,
            true
        );
        $this->throwNotFoundIfNull($shop, self::NOT_FOUND_MESSAGE);

        // check shop is closed
        if ($shop->isClose()) {
            return $this->customErrorView(
                400,
                Shop::CLOSED_CODE,
                Shop::CLOSED_MESSAGE
            );
        }

        // check shop opening hours
        $now = new \DateTime();
        if ($now < $shop->getStartHour()  || $now >= $shop->getEndHour()) {
            return $this->customErrorView(
                400,
                Shop::CLOSED_CODE,
                Shop::CLOSED_MESSAGE
            );
        }

        $order = new ShopOrder();

        $form = $this->createForm(new ShopOrderType(), $order);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();

        $orderNumber = $this->getOrderNumber(ShopOrder::LETTER_HEAD);

        $order->setUserId($userId);
        $order->setShop($shop);
        $order->setOrderNumber($orderNumber);

        $em->persist($order);

        $calculatedPrice = $this->handleShopOrderProductPost(
            $em,
            $order,
            $shop
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

    /**
     * @param Request $request
     * @param $id
     *
     *
     * @Method({"POST"})
     * @Route("/shops/orders/{id}")
     *
     * @return View
     */
    public function payShopOrderAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        $order = $this->getRepo('Shop\ShopOrder')->findOneBy(
            [
                'id' => $id,
                'userId' => $userId,
                'status' => ShopOrder::STATUS_UNPAID,
            ]
        );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(new ShopOrderPayChannelType(), $order);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $channel = $order->getPayChannel();

        if (
            $channel !== self::PAYMENT_CHANNEL_ALIPAY_WAP &&
            $channel !== self::PAYMENT_CHANNEL_UPACP &&
            $channel !== self::PAYMENT_CHANNEL_UPACP_WAP &&
            $channel !== self::PAYMENT_CHANNEL_ACCOUNT &&
            $channel !== self::PAYMENT_CHANNEL_WECHAT &&
            $channel !== self::PAYMENT_CHANNEL_ALIPAY
        ) {
            return $this->customErrorView(
                400,
                self::WRONG_CHANNEL_CODE,
                self::WRONG_CHANNEL_MESSAGE
            );
        }

        if ($channel === self::PAYMENT_CHANNEL_ACCOUNT) {
            return $this->payByAccount(
                $order
            );
        }

        $orderNumber = $order->getOrderNumber();

        $charge = $this->payForOrder(
            $orderNumber,
            $order->getPrice(),
            $channel,
            ShopOrder::PAYMENT_SUBJECT,
            ShopOrder::PAYMENT_BODY
        );

        $charge = json_decode($charge, true);
        $chargeId = $charge['id'];

        $this->createOrderMap('shop', $order->getId(), $chargeId);

        return new View($charge);
    }

    /**
     * @param $order
     *
     * @return View
     */
    private function payByAccount(
        $order
    ) {
        $price = $order->getPrice();
        $orderNumber = $order->getOrderNumber();
        $channel = $order->getPayChannel();

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

        $now = new \DateTime();
        $order->setStatus(ShopOrder::STATUS_PAID);
        $order->setPaymentDate($now);
        $order->setModificationDate($now);

        // store payment channel
        $this->storePayChannel(
            $order,
            $channel
        );

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $view = new View();

        return $view->setData(
            array(
                'balance' => $balance,
                'channel' => self::PAYMENT_CHANNEL_ACCOUNT,
            )
        );
    }
}
