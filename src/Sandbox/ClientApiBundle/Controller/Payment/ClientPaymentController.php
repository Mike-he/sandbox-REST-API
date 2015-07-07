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
                    $order = $this->setMembershipOrder($type, $price);
                    break;
            }

            http_response_code(200);
        } else {
            //TODO: return failed payment
        }

//        elseif ($input_data['type'] == 'refund.succeeded' && $input_data['data']['object']['succeed'] == true) {
//            $mapId = $input_data['data']['object']['order_no'];
//            $map = $this->getRepo('Order\OrderMap')->find($mapId);
//            $orderId = $map->getOrderId();
//            $order = $this->getRepo('Order\ProductOrder')->find($orderId);
//            $order->setStatus(self::STATUS_CANCELLED);
//            $order->setPaymentDate(null);
//            $order->setCancelledDate(new \DateTime());
//            $order->setModificationDate(new \DateTime());
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($order);
//            $em->flush();
//
//            http_response_code(200);
//        }
    }
}
