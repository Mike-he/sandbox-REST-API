<?php

namespace Sandbox\ApiBundle\Traits;

use Symfony\Component\Security\Acl\Exception\Exception;
use Sandbox\ApiBundle\Entity\User\User;

/**
 * Buddy Notification Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
trait BuddyNotification
{
    use SendNotification;

    /**
     * @param User   $fromUser
     * @param User   $recvUser
     * @param string $action
     */
    protected function sendXmppBuddyNotification(
        $fromUser,
        $recvUser,
        $action
    ) {
        try {
            // get event message data
            $jsonData = $this->getBuddyNotificationJsonData($action, $fromUser, $recvUser);

            // send xmpp notification
            $this->sendXmppNotification($jsonData, false);
        } catch (Exception $e) {
            error_log('Send buddy notification went wrong!');
        }
    }

    /**
     * @param string $action
     * @param User   $fromUser
     * @param User   $recvUser
     *
     * @return string | object
     */
    private function getBuddyNotificationJsonData(
        $action,
        $fromUser,
        $recvUser
    ) {
        // get globals
        $globals = $this->getContainer()
                        ->get('twig')
                        ->getGlobals();

        $domainURL = $globals['xmpp_domain'];

        // get receivers
        $receivers = array(
            array('jid' => $recvUser->getXmppUsername().'@'.$domainURL),
        );

        // get content array
        $contentArray = $this->getDefaultContentArray(
            'buddy', $action, $fromUser
        );

        $data = $this->getNotificationJsonData($receivers, $contentArray);

        return json_encode(array($data));
    }
}
