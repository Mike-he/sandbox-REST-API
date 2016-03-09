<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\Security\Acl\Exception\Exception;

trait ShopNotification
{
    use SendNotification;

    /**
     * @param string $action
     * @param User   $user
     * @param string $orderNo
     * @param int    $orderId
     */
    protected function sendXmppShopNotification(
        $action,
        $user,
        $orderNo,
        $orderId
    ) {
        try {
            // get json data
            $jsonData = $this->generateShopNotificationJsonData(
                $action,
                $user,
                $orderNo,
                $orderId
            );

            // send xmpp notification
            $this->sendXmppNotification($jsonData, false);
        } catch (Exception $e) {
            error_log('Send food order notification went wrong!');
        }
    }

    /**
     * @param string $action
     * @param User   $recvUser
     * @param string $orderNo
     * @param int    $orderId
     *
     * @return mixed
     */
    private function generateShopNotificationJsonData(
        $action,
        $recvUser,
        $orderNo,
        $orderId
    ) {
        // get globals
        $globals = $this->getContainer()->get('twig')->getGlobals();

        $domainURL = $globals['xmpp_domain'];

        // get receivers
        $receiversArray = array(
            array('jid' => $recvUser->getXmppUsername().'@'.$domainURL),
        );

        // get content array
        $contentArray = $this->getDefaultContentArray(
            'shop', $action
        );

        $contentArray['order'] = array(
            'id' => $orderId,
            'order_no' => $orderNo,
        );

        $jid = User::XMPP_SERVICE.'@'.$domainURL;

        $body = null;
        if ($action == ShopOrder::STATUS_READY) {
            $body = ShopOrder::READY_NOTIFICATION;
        } elseif ($action == ShopOrder::STATUS_REFUNDED) {
            $body = ShopOrder::READY_NOTIFICATION;
        }

        $messageArray = null;
        if (!is_null($body)) {
            $messageArray = array(
                'type' => 'chat',
                'from' => $jid,
                'body' => $body,
            );
        }

        $data = $this->getNotificationJsonData(
            $receiversArray,
            $contentArray,
            $messageArray
        );

        return json_encode(array($data));
    }
}
