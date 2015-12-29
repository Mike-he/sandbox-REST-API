<?php

namespace Sandbox\ClientApiBundle\Controller\Payment;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;

/**
 * Rest controller for Client Orders.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientPaymentController extends PaymentController
{
    /**
     * @Post("/payment/webhooks")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getWebhooksAction(
        Request $request
    ) {
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);
        $object = $data['data']['object'];

        if ($data['type'] != 'charge.succeeded' || $object['paid'] != true) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $chargeId = $object['id'];
        $price = $object['amount'] / 100;
        $orderNumber = $object['order_no'];
        $channel = $object['channel'];
        $userId = (int) $object['body'];
        $orderType = $orderNumber[0];

        $myCharge = $this->getRepo('Order\OrderMap')->findOneBy(
            [
                'chargeId' => $chargeId,
            ]
        );
        if (is_null($myCharge) || empty($myCharge)) {
            return $this->customErrorView(
                400,
                self::WRONG_CHARGE_ID_CODE,
                self::WRONG_CHARGE_ID__MESSAGE
            );
        }

        switch ($orderType) {
            case 'P':
                $order = $this->setProductOrder(
                    $chargeId,
                    $channel
                );
                $amount = $this->postConsumeBalance(
                    $order->getUserId(),
                    $price,
                    $orderNumber
                );
                $balance = $this->postBalanceChange(
                    $order->getUserId(),
                    0,
                    $orderNumber,
                    $channel,
                    $price
                );

                break;
            case 'V':
                $productId = $myCharge->getOrderId();
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
                $balance = $this->postBalanceChange(
                    $userId,
                    0,
                    $orderNumber,
                    $channel,
                    $price
                );

                break;
            case 'T':
                $this->setTopUpOrder(
                    $userId,
                    $price,
                    $orderNumber,
                    $channel
                );
                $balance = $this->postBalanceChange(
                    $userId,
                    $price,
                    $orderNumber,
                    $channel,
                    $price
                );
                $amount = $this->postConsumeBalance(
                    $userId,
                    $price,
                    $orderNumber
                );

                break;
            case 'F':
                $data = $this->getJsonData(
                    $orderNumber,
                    $channel,
                    $chargeId,
                    true
                );

                $result = $this->foodPaymentCallback($data);

                $amount = $this->postConsumeBalance(
                    $userId,
                    $price,
                    $orderNumber
                );
                $balance = $this->postBalanceChange(
                    $userId,
                    0,
                    $orderNumber,
                    $channel,
                    $price
                );

                break;
            default:
                break;
        }

        return new Response();
    }

    /**
     * @Post("/payment/create")
     *
     * @param Request $request
     *
     * @return View
     */
    public function createPaymentAction(
        Request $request
    ) {
        $serverId = $this->getGlobal('server_order_id');
        $data = json_decode($request->getContent(), true);
        $subject = $data['subject'];
        $orderNo = $data['order_no']."$serverId";
        $amount = $data['amount'];
        $channel = $data['channel'];
        $userId = $this->getUserId();

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
            return $this->accountPayment(
                $userId,
                $orderNo,
                $amount
            );
        }

        $chargeJson = $this->payForOrder(
            $orderNo,
            $amount,
            $channel,
            $subject,
            "$userId"
        );

        $charge = json_decode($chargeJson, true);

        return new View($charge);
    }
}
