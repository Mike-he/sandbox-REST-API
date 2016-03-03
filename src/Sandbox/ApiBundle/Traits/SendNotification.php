<?php

namespace Sandbox\ApiBundle\Traits;

use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Send Notification Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait SendNotification
{
    use CommonMethod;

    /**
     * @param string $type
     * @param string $action
     * @param User   $fromUser
     *
     * @return array
     */
    private function getDefaultContentArray(
        $type,
        $action,
        $fromUser = null
    ) {
        $timestamp = round(microtime(true) * 1000);

        $contentArray = array(
            'type' => $type,
            'action' => $action,
            'timestamp' => "$timestamp",
        );

        // get fromUserArray
        if (!is_null($fromUser)) {
            $contentArray['from'] = $this->getFromUserArray($fromUser);
        }

        return $contentArray;
    }

    /**
     * @param User $fromUser
     *
     * @return array
     */
    private function getFromUserArray(
        $fromUser
    ) {
        $name = '';

        $profile = $this->getContainer()
                        ->get('doctrine')
                        ->getRepository('User\UserProfile')
                        ->findOneByUser($fromUser);

        if (!is_null($profile)) {
            $name = $profile->getName();
        }

        return array(
            'id' => $fromUser->getId(),
            'xmpp_username' => $fromUser->getXmppUsername(),
            'name' => $name,
        );
    }

    /**
     * @param array $receivers
     * @param array $contentArray
     * @param array $messageArray
     *
     * @return array
     */
    private function getNotificationJsonData(
        $receivers,
        $contentArray = null,
        $messageArray = null
    ) {
        $jsonDataArray = array('receivers' => $receivers);

        return $this->setJsonDataArrayBody(
            $jsonDataArray,
            $contentArray,
            $messageArray
        );
    }

    /**
     * @param array $jsonDataArray
     * @param array $contentArray
     * @param array $messageArray
     *
     * @return array
     */
    private function setJsonDataArrayBody(
        $jsonDataArray,
        $contentArray,
        $messageArray
    ) {
        // check content array
        if (!is_null($contentArray)) {
            $jsonDataArray['content'] = $contentArray;
        }

        // check message array
        if (!is_null($messageArray)) {
            $jsonDataArray['message'] = $messageArray;
        }

        return $jsonDataArray;
    }

    /**
     * @param array $outcasts
     * @param array $contentArray
     * @param array $messageArray
     *
     * @return array
     */
    private function getNotificationBroadcastJsonData(
        $outcasts,
        $contentArray = null,
        $messageArray = null
    ) {
        $jsonDataArray = array('outcasts' => $outcasts);

        return $this->setJsonDataArrayBody(
            $jsonDataArray,
            $contentArray,
            $messageArray
        );
    }

    /**
     * @param object $jsonData
     * @param bool   $broadcast
     */
    protected function sendXmppNotification(
        $jsonData,
        $broadcast = false
    ) {
        try {
            // get globals
            $globals = $this->getContainer()
                            ->get('twig')
                            ->getGlobals();

            // openfire API URL
            $apiURL = $globals['openfire_innet_url'].
                $globals['openfire_plugin_sandbox'].
                $globals['openfire_plugin_sandbox_notification'];

            if ($broadcast) {
                $apiURL = $apiURL.$globals['openfire_plugin_sandbox_notification_broadcast'];
            }

            // call OpenFire API
            $ch = curl_init($apiURL);
            $this->getContainer()->get('curl_util')->callAPI($ch, 'POST', null, $jsonData);
        } catch (Exception $e) {
            error_log('Send XMPP notification went wrong.');
        }
    }
}
