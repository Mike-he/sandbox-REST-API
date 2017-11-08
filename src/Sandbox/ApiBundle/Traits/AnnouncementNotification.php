<?php

namespace Sandbox\ApiBundle\Traits;

use Symfony\Component\Security\Acl\Exception\Exception;
use Sandbox\ApiBundle\Entity\Announcement\Announcement;

/**
 * Announcement Notification Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
trait AnnouncementNotification
{
    use SendNotification;

    /**
     * @param Announcement $announcement
     * @param string       $action
     */
    protected function sendXmppAnnouncementNotification(
        $announcement,
        $action
    ) {
        try {
            // get event message data
            $jsonData = $this->getAnnouncementNotificationJsonData($announcement, $action);

            // send xmpp notification
            $this->sendXmppNotification($jsonData, true);
        } catch (Exception $e) {
            error_log('Send announcement notification went wrong!');
        }
    }

    /**
     * @param Announcement $announcement
     * @param string       $action
     *
     * @return string | object
     */
    private function getAnnouncementNotificationJsonData(
        $announcement,
        $action
    ) {
        // get content array
        $contentArray = $this->getDefaultContentArray(
            'announcement', $action
        );

        $contentArray['announcement'] = array(
            'id' => $announcement->getId(),
            'title' => $announcement->getTitle(),
        );

        $data = $this->getNotificationBroadcastJsonData(array(), $contentArray);

        return json_encode(array($data));
    }
}
