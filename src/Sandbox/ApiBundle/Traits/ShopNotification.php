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

        $jid = User::XMPP_SERVICE.'@'.$domainURL;

        $key = null;
        if ($action == ShopOrder::STATUS_READY) {
            $key = ShopOrder::READY_NOTIFICATION;
        } elseif ($action == ShopOrder::STATUS_ISSUE) {
            $key = ShopOrder::ISSUE_NOTIFICATION;
        } elseif ($action == ShopOrder::STATUS_REFUNDED) {
            $key = ShopOrder::REFUNDED_NOTIFICATION;
        }

        if (is_null($key)) {
            return;
        }

        $zhBody = $this->get('translator')->trans(
            $key,
            array(),
            null,
            'zh'
        );

        $enBody = $this->get('translator')->trans(
            $key,
            array(),
            null,
            'en'
        );

        $messageArray = null;
        if (!is_null($zhBody)) {
            $messageArray = array(
                'type' => 'chat',
                'from' => $jid,
                'body' => $zhBody,
            );
        }

        $apns = $this->setApnsJsonDataArray($zhBody, $enBody);

        // get content array
        $contentArray = $this->getDefaultContentArray(
            'shop',
            $action,
            null,
            $apns
        );

        $contentArray['order'] = array(
            'id' => $orderId,
            'order_no' => $orderNo,
        );

        $data = $this->getNotificationJsonData(
            $receiversArray,
            $contentArray,
            $messageArray,
            $apns
        );

        return json_encode(array($data));
    }
}
