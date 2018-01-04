<?php

namespace Sandbox\ApiBundle\Traits;

use Symfony\Component\Security\Acl\Exception\Exception;
use Sandbox\ApiBundle\Entity\Feed\Feed;
use Sandbox\ApiBundle\Entity\Feed\FeedComment;
use Sandbox\ApiBundle\Entity\User\User;

/**
 * Feed Notification Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
trait FeedNotification
{
    use SendNotification;

    /**
     * @param Feed        $feed
     * @param User        $fromUser
     * @param array       $recvUsers
     * @param string      $action
     * @param FeedComment $comment
     */
    protected function sendXmppFeedNotification(
        $feed,
        $fromUser,
        $recvUsers,
        $action,
        $comment = null
    ) {
        try {
            // get event message data
            $jsonData = $this->getFeedNotificationJsonData(
                $feed, $action, $fromUser, $recvUsers, $comment
            );

            // send xmpp notification
            $this->sendXmppNotification($jsonData, false);
        } catch (Exception $e) {
            error_log('Send feed notification went wrong!');
        }
    }

    /**
     * @param Feed        $feed
     * @param string      $action
     * @param User        $fromUser
     * @param array       $recvUsers
     * @param FeedComment $comment
     *
     * @return string | object
     */
    private function getFeedNotificationJsonData(
        $feed,
        $action,
        $fromUser,
        $recvUsers,
        $comment = null
    ) {
        // get globals
        $globals = $this->getContainer()
                        ->get('twig')
                        ->getGlobals();

        $domainURL = $globals['xmpp_domain'];

        // get receivers
        $receivers = array();

        foreach ($recvUsers as $recvUser) {
            $jid = $recvUser->getXmppUsername().'@'.$domainURL;
            $receivers[] = array('jid' => $jid);
        }

        // get content array
        $contentArray = $this->getDefaultContentArray(
            'feed', $action, $fromUser
        );

        $contentArray['feed'] = array(
            'id' => $feed->getId(),
            'content' => $feed->getContent(),
        );

        if (!is_null($comment)) {
            $contentArray['comment'] = array(
                'id' => $comment->getId(),
                'payload' => $comment->getPayload(),
            );
        }

        $data = $this->getNotificationJsonData($receivers, $contentArray);

        return json_encode(array($data));
    }
}
