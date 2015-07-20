<?php

namespace Sandbox\ClientApiBundle\Controller\Order;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Patch;

/**
 * Rest controller for Client MembershipOrders.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientMembershipOrderController extends PaymentController
{
    const PAYMENT_SUBJECT = 'VIP';
    const PAYMENT_BODY = 'month';
    const VIP_ORDER_LETTER_HEAD = 'V';

    /**
     * Get all orders for current user.
     *
     * @Get("/membership/orders/my")
     *
     *@Annotations\QueryParam(
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
    public function getUserMembershipOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserid();
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $orders = $this->getRepo('Order\MembershipOrder')->findBy(
            ['userId' => $userId],
            null,
            $limit,
            $offset
        );

        return new View($orders);
    }

    /**
     * @Post("/membership/orders")
     *
     * @param Request $request
     */
    public function payMembershipAction(
        Request $request
    ) {
        $type = $request->get('type');
        $price = $request->get('price');
        $channel = $request->get('channel');

        if (is_null($price) || empty($price)) {
            return $this->customErrorView(
                400,
                self::NO_PRICE_CODE,
                self::NO_PRICE_MESSAGE
            );
        }
        if ($channel !== 'alipay_wap' && $channel !== 'upacp_wap' && $channel !== 'account') {
            return $this->customErrorView(
                400,
                self::WRONG_PAYMENT_STATUS_CODE,
                self::WRONG_PAYMENT_STATUS_MESSAGE
            );
        }

        if ($channel === 'account') {
            $this->payMembershipByAccount($type, $price);
        }

        $orderNumber = $this->getOrderNumber(self::VIP_ORDER_LETTER_HEAD);

        $charge = $this->payForOrder(
            $orderNumber,
            $price,
            $channel,
            self::PAYMENT_SUBJECT,
            self::PAYMENT_BODY
        );
        $charge = json_decode($charge, true);

        return new View($charge);
    }

    /**
     * @Patch("/membership/cancel")
     *
     * @param Request $request
     *
     * @return View
     */
    public function cancelMembershipAction(
        Request $request
    ) {
        $orderArray = $this->getRepo('Order\MembershipOrder')->findBy(
            ['userId' => $this->getUserid()],
            ['id' => 'DESC'],
            1
        );
        $order = $orderArray[0];
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }
        $order->setCancelledDate(new \DateTime());
        $order->setModificationDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        return new View($order);
    }

    /**
     * @param $type
     * @param $price
     *
     * @return View
     */
    private function payMembershipByAccount($type, $price)
    {
        //TODO Call CRM API to get current balance
        $balance = 500;
        if ($price > $balance) {
            return $this->customErrorView(
                400,
                self::INSUFFICIENT_FUNDS_CODE,
                self::INSUFFICIENT_FUNDS_MESSAGE
            );
        }
        //TODO Call CRM API to subtract price from current balance
        $newBalance = $balance - $price;
        //TODO Call CRM API to get current balance AGAIN
        $updatedbalance = $newBalance;
        if ($newBalance !== $updatedbalance) {
            return $this->customErrorView(
                500,
                self::SYSTEM_ERROR_CODE,
                self::SYSTEM_ERROR_MESSAGE
            );
        }

        $orderNumber = $this->getOrderNumber(self::VIP_ORDER_LETTER_HEAD);
        $order = $this->setMembershipOrder($type, $price, $orderNumber);

        $view = new View();
        $view->setData(
            array(
                'id' => $order->getId(),
                'channel' => 'account',
            )
        );

        return $view;
    }

    /**
     * @Get("/membership/orders/{id}")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getOneMembershipOrderAction(
        Request $request,
        $id
    ) {
        $order = $order = $this->getRepo('Order\MembershipOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }

        return new View($order);
    }
}
