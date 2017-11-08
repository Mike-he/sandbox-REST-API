<?php

namespace Sandbox\ApiBundle\Traits;

use Symfony\Component\Security\Acl\Exception\Exception;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\User\User;

/**
 * Event Notification Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
trait EventNotification
{
    use SendNotification;

    /**
     * @param User   $recvUser
     * @param Event  $event
     * @param string $action
     */
    protected function sendXmppEventNotification(
        $recvUser,
        $event,
        $action
    ) {
        try {
            // get event message data
            $jsonData = $this->getEventNotificationJsonData(
                $action,
                $recvUser,
                $event
            );

            // send xmpp notification
            $this->sendXmppNotification($jsonData, false);
        } catch (Exception $e) {
            error_log('Send event registration accept notification went wrong!');
        }
    }

    /**
     * @param string $action
     * @param User   $recvUser
     * @param Event  $event
     *
     * @return object|string
     */
    private function getEventNotificationJsonData(
        $action,
        $recvUser,
        $event
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
            'event', $action
        );

        $contentArray['event'] = array(
            'id' => $event->getId(),
            'name' => $event->getName(),
        );

        $data = $this->getNotificationJsonData($receivers, $contentArray);

        return json_encode(array($data));
    }
}
