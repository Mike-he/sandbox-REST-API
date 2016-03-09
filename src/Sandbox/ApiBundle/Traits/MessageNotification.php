<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\User\User;
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
trait MessageNotification
{
    use SendNotification;

    /**
     * @param string $body
     */
    protected function sendXmppMessageNotification(
        $body
    ) {
        try {
            $globals = $this->getContainer()
                            ->get('twig')
                            ->getGlobals();

            $domainURL = $globals['xmpp_domain'];
            $jid = User::XMPP_SERVICE.'@'.$domainURL;

            $messageArray = [
                'type' => 'chat',
                'from' => $jid,
                'body' => $body,
            ];

            // get message data
            $data = $this->getNotificationBroadcastJsonData(
                array(),
                null,
                $messageArray
            );

            $jsonData = json_encode(array($data));

            // send xmpp notification
            $this->sendXmppNotification($jsonData, true);
        } catch (Exception $e) {
            error_log('Send message notification went wrong!');
        }
    }
}
