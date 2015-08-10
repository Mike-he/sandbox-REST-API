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

        $myCharge = $this->getRepo('Order\OrderMap')->findOneBy(
            [
                'chargeId' => $chargeId,
            ]
        );
//        if (is_null($myCharge) || empty($myCharge)) {
//            return $this->customErrorView(
//                400,
//                self::WRONG_CHARGE_ID_CODE,
//                self::WRONG_CHARGE_ID__MESSAGE
//            );
//        }

        switch ($object['subject']) {
            case 'Your Subject':
                if ($chargeId != 'ch_Xsr7u35O3m1Gw4ed2ODmi4Lw') {
                    return $this->customErrorView(
                        400,
                        self::WRONG_CHARGE_ID_CODE,
                        self::WRONG_CHARGE_ID__MESSAGE
                    );
                }

                return new Response();

                break;
            case 'ROOM':

                $order = $this->setProductOrder($chargeId);

                break;
            case 'VIP':

                $productId = $myCharge->getOrderId();
                $order = $this->setMembershipOrder($productId, $price, $orderNumber);
                $userId = $order->getUserId();
                $this->postAccountUpgrade($userId, $productId, $orderNumber);
                $amount = $this->postConsumeBalance($userId, $price, $orderNumber);

                break;
            case 'TOPUP':

                $order = $this->setTopUpOrder($price, $orderNumber);
                $userId = $order->getUserId();
                $balance = $this->postBalanceChange(
                    $userId,
                    $price,
                    $orderNumber,
                    $channel
                );

                break;
            default:
                break;
        }

        return new Response();
    }
}
