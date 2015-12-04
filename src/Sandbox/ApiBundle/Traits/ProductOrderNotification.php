<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Order Notification Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait ProductOrderNotification
{
    use SendNotification;

    /**
     * @param int    $orderId
     * @param string $orderNumber
     * @param int    $fromUserId
     * @param array  $receivers
     * @param string $action
     */
    protected function sendXmppProductOrderNotification(
        $order,
        $receivers,
        $action,
        $fromUserId = null,
        $orders = [],
        $first = null,
        $second = null
    ) {
        try {
            if (empty($orders) && !is_null($order)) {
                $city = $order->getProduct()->getRoom()->getCity()->getName();
                $building = $order->getProduct()->getRoom()->getBuilding()->getName();
                $room = $order->getProduct()->getRoom()->getName();

                $body = $first.$city.$building.$room.$second;
                // get notification data
                $data = $this->getProductOrderNotificationJsonData(
                    $order->getId(),
                    $order->getOrderNumber(),
                    $fromUserId,
                    $receivers,
                    $action,
                    $body
                );

                $jsonData = json_encode(array($data));
            } else {
                $dataArray = [];
                foreach ($orders as $order) {
                    $data = $this->getProductOrderNotificationJsonData(
                        $order->getId(),
                        $order->getOrderNumber(),
                        $fromUserId,
                        [$order->getUserId()],
                        $action,
                        $first
                    );

                    array_push($dataArray, $data);
                }

                $jsonData = json_encode($dataArray);
            }

            // send xmpp notification
            $this->sendXmppNotification($jsonData, false);
        } catch (Exception $e) {
            error_log('Send message notification went wrong!');
        }
    }

    /**
     * @param $orderId
     * @param $orderNumber
     * @param $fromUserId
     * @param $receivers
     * @param $action
     *
     * @return mixed
     */
    private function getProductOrderNotificationJsonData(
        $orderId,
        $orderNumber,
        $fromUserId,
        $receivers,
        $action,
        $body
    ) {
        $globals = $this->getGlobals();
        $domainURL = $globals['xmpp_domain'];
        $fromUser = null;
        if (!is_null($fromUserId)) {
            $fromUser = $this->getRepo('User\User')->find($fromUserId);
        }

        // get receivers array
        $receiversArray = [];
        foreach ($receivers as $receiverId) {
            $recevUser = $this->getRepo('User\User')->find($receiverId);
            array_push($receiversArray, ['jid' => $recevUser->getXmppUsername().'@'.$domainURL]);
        }

        // get content array
        $contentArray = $this->getDefaultContentArray(
            ProductOrder::ACTION_TYPE,
            $action,
            $fromUser
        );

        // get order array
        $contentArray['order'] = $this->getOrderArray($orderId, $orderNumber);

        $jid = User::XMPP_SERVICE.'@'.$domainURL;
        // get message from service account
        $messageArray = [
            'type' => 'chat',
            'from' => $jid,
            'body' => $body,
        ];

        return $this->getNotificationJsonData(
            $receiversArray,
            $contentArray,
            $messageArray
        );
    }

    /**
     * @param int    $orderId
     * @param string $orderNumber
     *
     * @return array
     */
    private function getOrderArray(
        $orderId,
        $orderNumber
    ) {
        return [
            'id' => $orderId,
            'order_number' => $orderNumber,
        ];
    }
}
