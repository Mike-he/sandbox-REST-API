<?php

namespace Sandbox\ApiBundle\Controller\Payment;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use Sandbox\ApiBundle\Entity\Order\MembershipOrder;
use Sandbox\ApiBundle\Entity\Order\OrderCount;
use Pingpp\Pingpp;
use Pingpp\Charge;
use Pingpp\Error\Base;

/**
 * Payment Controller.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class PaymentController extends SandboxRestController
{
    const BAD_REQUEST = 'BAD REQUEST FOR CREATING ORDER FORM';
    const WRONG_PAYMENT_STATUS = 'WRONG STATUS';
    const WRONG_CHANNEL = 'THIS CHANNEL IS NOT SUPPORTED';
    const STATUS_PAID = 'paid';

    /**
     * @param $order
     * @param $channel
     *
     * @return Charge
     */
    public function payForOrder($orderNumber, $price, $channel, $subject, $body)
    {
        $extra = [];
        switch ($channel) {
            case 'alipay_wap':
                $extra = array(
                    'success_url' => 'http://www.yourdomain.com/success',
                    'cancel_url' => 'http://www.yourdomain.com/cancel',
                );
                break;
            case 'upacp_wap':
                $extra = array(
                    'result_url' => 'http://www.yourdomain.com/result?code=',
                );
                break;
        }

        $keyGlobal = $this->get('twig')->getGlobals();
        $key = $keyGlobal['pingpp_test_key'];
        $appGlobal = $this->get('twig')->getGlobals();
        $appId = $appGlobal['pingpp_app_id'];

        Pingpp::setApiKey($key);
        try {
            $ch = Charge::create(
                array(
                    'order_no' => $orderNumber,
                    'amount' => $price * 100,
                    'app' => array('id' => $appId),
                    'channel' => $channel,
                    'currency' => 'cny',
                    'extra' => $extra,
                    'client_ip' => $_SERVER['REMOTE_ADDR'],
                    'subject' => $subject,
                    'body' => $body,
                )
            );

            return $ch;
        } catch (Base $e) {
            header('Status: '.$e->getHttpStatus());
            echo($e->getHttpBody());
        }
    }

    /**
     * @param $chargeId
     *
     * @return Charge
     */
    public function getChargeDetail($chargeId)
    {
        $keyGlobal = $this->get('twig')->getGlobals();
        $key = $keyGlobal['pingpp_test_key'];
        Pingpp::setApiKey($key);
        try {
            $ch = Charge::retrieve($chargeId);

            return $ch;
        } catch (Base $e) {
            header('Status: '.$e->getHttpStatus());
            echo($e->getHttpBody());
        }
    }

    /**
     * @param $data
     */
    public function setProductOrder($data)
    {
        $mapId = $data['data']['object']['order_no'];
        $map = $this->getRepo('Order\OrderMap')->find($mapId);
        $orderId = $map->getOrderId();
        $order = $this->getRepo('Order\ProductOrder')->find($orderId);
        $order->setStatus(self::STATUS_PAID);
        $order->setPaymentDate(new \DateTime());
        $order->setModificationDate(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();
    }

    /**
     * @param $type
     * @param $price
     *
     * @return MembershipOrder
     */
    public function setMembershipOrder($type, $price, $orderNumber)
    {
        $userId = $this->getUserid();
        $endDate = $this->calculateEndDate($type);

        $order = new MembershipOrder();
        $order->setUserId($userId);
        $order->setEndDate($endDate);
        $order->setPrice($price);
        $order->setType($type);
        $order->setOrderNumber($orderNumber);
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        return $order;
    }

    /**
     * @param $count
     * @param $now
     */
    public function setOrderCount($count, $now)
    {
        $counter = new OrderCount();
        $counter->setCount($count);
        $counter->setOrderDate($now);
        $em = $this->getDoctrine()->getManager();
        $em->persist($counter);
        $em->flush();
    }

    /**
     * @param $letter
     *
     * @return string
     */
    public function getOrderNumber($letter)
    {
        $datetime = new \DateTime();
        $now = clone $datetime;
        $now->setTime(00, 00, 00);
        $date = $datetime->format('Ymdhis');
        $counter = $this->getRepo('Order\OrderCount')->findOneBy(['orderDate' => $now]);
        if (is_null($counter)) {
            $count = 1;
            $this->setOrderCount($count, $now);
        } else {
            $count = $counter->getCount() + 1;
            $counter->setCount($count);
            $em = $this->getDoctrine()->getManager();
            $em->persist($counter);
            $em->flush();
        }
        $orderNumber = $letter.$date.$count;

        return $orderNumber;
    }

    /**
     * @param $type
     *
     * @return \DateTime
     */
    public function calculateEndDate($type)
    {
        $orderArray = $this->getRepo('Order\MembershipOrder')->findBy(
            ['userId' => $this->getUserid()],
            ['id' => 'DESC'],
            1
        );
        $order = $orderArray[0];
        if (empty($order)) {
            $startDate = new \DateTime();
        } else {
            $startDate = $order->getEndDate();
        }

        $endDate = clone $startDate;
        switch ($type) {
            case 'month':
                $endDate->modify('+ 1 month');
                break;
            case 'quarter':
                $endDate->modify('+ 3 month');
                break;
            case 'year':
                $endDate->modify('+ 1 year');
                break;
        }

        return $endDate;
    }
}
