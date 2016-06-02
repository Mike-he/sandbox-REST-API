<?php

namespace Sandbox\ApiBundle\Traits;

use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Message Notification Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait MessagePushNotification
{
    use SendNotification;

    /**
     * @param string $content
     */
    protected function sendXmppMessagePushNotification(
        $content
    ) {
        try {
            $apns = $this->setApnsJsonDataArray(
                $content,
                $content
            );

            // get message data
            $data = $this->getNotificationBroadcastJsonData(
                array(),
                $apns,
                null,
                $apns
            );

            $jsonData = json_encode(array($data));

            // send xmpp notification
            $this->sendXmppNotification($jsonData, true);
        } catch (Exception $e) {
            error_log('Send message notification went wrong!');
        }
    }
}
