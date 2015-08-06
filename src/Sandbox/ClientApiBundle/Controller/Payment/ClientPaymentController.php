<?php

namespace Sandbox\ClientApiBundle\Controller\Payment;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Post;
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
     */
    public function getWebhooksAction(
        Request $request
    ) {
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);
        if ($data['type'] == 'charge.succeeded' && $data['data']['object']['paid'] == true) {
            $chargeId = $data['data']['object']['id'];
            $price = $data['data']['object']['amount'] / 100;
            $orderNumber = $data['data']['object']['order_no'];
            $channel = $data['data']['object']['channel'];

            switch ($data['data']['object']['subject']) {
                case 'ROOM':
                    $myCharge = $this->getRepo('Order\OrderMap')->findOneBy(
                        [
                            'type' => 'product',
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
                    $order = $this->setProductOrder($data);

                    $view = new View();
                    $view->setData(
                        ['result' => true]
                    );

                    return $view;
                    break;
                case 'VIP':
                    $myCharge = $this->getRepo('Order\OrderMap')->findOneBy(
                        [
                            'type' => 'upgrade',
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
                    $productId = $myCharge->getOrderId();
                    $order = $this->setMembershipOrder($productId, $price, $orderNumber);
                    $userId = $order->getUserId();
                    $this->postAccountUpgrade($userId, $productId, $orderNumber);
                    $amount = $this->postConsumeBalance($userId, $price, $orderNumber);
                    $view = new View();
                    $view->setData(
                        ['result' => true]
                    );

                    return $view;

                    break;
                case 'TOPUP':
                    $myCharge = $this->getRepo('Order\OrderMap')->findOneBy(
                        [
                            'type' => 'topup',
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
                    $order = $this->setTopUpOrder($price, $orderNumber);
                    $userId = $order->getUserId();
                    $balance = $this->postBalanceChange(
                        $userId,
                        $price,
                        $orderNumber,
                        $channel
                    );
                    $view = new View();
                    $view->setData(
                        ['result' => true]
                    );

                    return $view;

                    break;
            }
            http_response_code(200);
        } else {
            //return failed payment
            http_response_code(500);
        }
    }
}
