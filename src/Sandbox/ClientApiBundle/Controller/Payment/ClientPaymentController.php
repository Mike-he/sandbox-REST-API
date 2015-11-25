<?php

namespace Sandbox\ClientApiBundle\Controller\Payment;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Response;

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
                    $channel
                );
                $amount = $this->postConsumeBalance(
                    $userId,
                    $price,
                    $orderNumber
                );

                break;
            case 'F':
                $orderId = $this->findAndSetFoodOrder($orderNumber);
                $balance = $this->postBalanceChange(
                    $userId,
                    $price,
                    $orderNumber,
                    $channel
                );
                $amount = $this->postConsumeBalance(
                    $userId,
                    $price,
                    $orderNumber
                );

                break;
            default:
                break;
        }

        return new Response();
    }
}
