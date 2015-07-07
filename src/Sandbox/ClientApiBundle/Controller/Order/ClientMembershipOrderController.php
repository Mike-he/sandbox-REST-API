<?php

namespace Sandbox\ClientApiBundle\Controller\Order;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
    const BAD_REQUEST = 'BAD REQUEST FOR CREATING MEMBERSHIP ORDER FORM';
    const INSUFFICIENT_FUNDS_CODE = 400001;
    const INSUFFICIENT_FUNDS_MESSAGE = 'Insufficient funds in account balance - 余额不足';
    const SYSTEM_ERROR_CODE = 500001;
    const SYSTEM_ERROR_MESSAGE = 'System error - 系统出错';
    const PAYMENT_SUBJECT = 'VIP';
    const PAYMENT_BODY = 'month';
    const VIP_ORDER_LETTER_HEAD = 'V';

    /**
     * @Get("/membership/orders/{id}")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getOneMembershipOrderAction(
        Request $request,
        $id
    ) {
        $order = $order = $this->getRepo('Order\MembershipOrder')->find($id);

        return new View($order);
    }

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
        if ($channel !== 'alipay_wap' && $channel !== 'upacp_wap' && $channel !== 'account') {
            throw new BadRequestHttpException(self::WRONG_CHANNEL);
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
            )
        );

        return $view;
    }
}
