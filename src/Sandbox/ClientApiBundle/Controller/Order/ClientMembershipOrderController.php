<?php

namespace Sandbox\ClientApiBundle\Controller\Order;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;

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
    const PAYMENT_BODY = 'VIP ORDER';
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
        $userId = $this->getUserId();
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
        $requestContent = json_decode($request->getContent(), true);
        $price = $requestContent['price'];
        $channel = $requestContent['channel'];
        $productId = $requestContent['product_id'];

        if (is_null($productId) || empty($productId)) {
            return $this->customErrorView(
                400,
                self::NO_VIP_PRODUCT_ID_CODE,
                self::NO_VIP_PRODUCT_ID_CODE_MESSAGE
            );
        }
        if (is_null($price) || empty($price)) {
            return $this->customErrorView(
                400,
                self::NO_PRICE_CODE,
                self::NO_PRICE_MESSAGE
            );
        }
        if (
            $channel !== 'alipay_wap' &&
            $channel !== 'upacp_wap' &&
            $channel !== 'account' &&
            $channel !== 'alipay'
        ) {
            return $this->customErrorView(
                400,
                self::WRONG_PAYMENT_STATUS_CODE,
                self::WRONG_PAYMENT_STATUS_MESSAGE
            );
        }
        if ($channel === 'account') {
            return $this->payMembershipByAccount(
                $productId,
                $price
            );
        }

        $orderNumber = $this->getOrderNumber(self::VIP_ORDER_LETTER_HEAD);

        $charge = $this->payForOrder(
            $orderNumber,
            $price,
            $channel,
            self::PAYMENT_SUBJECT,
            $this->getUserId()
        );
        $charge = json_decode($charge, true);
        $chargeId = $charge['id'];
        $this->createOrderMap('upgrade', $productId, $chargeId);

        return new View($charge);
    }

    /**
     * @param $type
     * @param $price
     *
     * @return View
     */
    private function payMembershipByAccount($productId, $price)
    {
        $orderNumber = $this->getOrderNumber(self::VIP_ORDER_LETTER_HEAD);

        $userId = $this->getUserId();
        $balance = $this->postBalanceChange(
            $userId,
            (-1) * $price,
            $orderNumber,
            'account'
        );
        if (is_null($balance)) {
            return $this->customErrorView(
                400,
                self::INSUFFICIENT_FUNDS_CODE,
                self::INSUFFICIENT_FUNDS_MESSAGE
            );
        }
        $order = $this->setMembershipOrder(
            $userId,
            $productId,
            $price,
            $orderNumber
        );
        $this->postAccountUpgrade(
            $userId,
            $productId,
            $orderNumber
        );
        $amount = $this->postConsumeBalance(
            $userId,
            $price,
            $orderNumber
        );
        $view = new View();

        return $view->setData(
            array(
                'balance' => $balance,
                'channel' => 'account',
            )
        );
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
