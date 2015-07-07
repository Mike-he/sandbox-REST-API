<?php

namespace Sandbox\ClientApiBundle\Controller\Payment;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Post;

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
        $data = json_decode($request->getContent(), true);
        if ($data['type'] == 'charge.succeeded' && $data['data']['object']['paid'] == true) {
            switch ($data['data']['object']['subject']) {
                case 'ROOM':
                    $this->setProductOrder($data);
                    break;
                case 'VIP':
                    $type = $data['data']['object']['body'];
                    $price = $data['data']['object']['amount'] / 100;
                    $orderNumber = $data['data']['object']['order_no'];
                    $order = $this->setMembershipOrder($type, $price, $orderNumber);
                    //TODO: CALL CRM UPGRADE USER TO VIP
                    break;
                case 'TOPUP':
                    $price = $data['data']['object']['amount'] / 100;
                    $orderNumber = $data['data']['object']['order_no'];
                    $order = $this->setTopUpOrder($price, $orderNumber);
                    //TODO: CALL CRM UPDATE BALANCE
                    break;
            }
            http_response_code(200);
        } else {
            //return failed payment
            http_response_code(500);
        }
    }
}
