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
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * @Post("/payment/webhooks")
     *
     * @param Request $request
     */
    public function getWebhooksAction(
        Request $request
    ) {
        $input_data = json_decode($request->getContent(), true);

        if ($input_data['type'] == 'charge.succeeded' && $input_data['data']['object']['paid'] == true) {
            $mapId = $input_data['data']['object']['order_no'];
            $map = $this->getRepo('Order\OrderMap')->find($mapId);
            $orderId = $map->getOrderId();
            $order = $this->getRepo('Order\ProductOrder')->find($orderId);
            $order->setStatus(self::STATUS_PAID);
            $order->setPaymentDate(new \DateTime());
            $order->setModificationDate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();

            http_response_code(200);
        } elseif ($input_data['type'] == 'refund.succeeded' && $input_data['data']['object']['succeed'] == true) {
            $mapId = $input_data['data']['object']['order_no'];
            $map = $this->getRepo('Order\OrderMap')->find($mapId);
            $orderId = $map->getOrderId();
            $order = $this->getRepo('Order\ProductOrder')->find($orderId);
            $order->setStatus(self::STATUS_CANCELLED);
            $order->setPaymentDate(null);
            $order->setCancelledDate(new \DateTime());
            $order->setModificationDate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();

            http_response_code(200);
        }
    }
}
