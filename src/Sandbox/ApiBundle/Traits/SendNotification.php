<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Constants\BundleConstants;
use Symfony\Component\Security\Acl\Exception\Exception;
use Sandbox\ApiBundle\Entity\User\User;

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
    use CurlUtil;

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
        $fromUser = null,
        $apns = null
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

        // get fromUserArray
        if (!is_null($apns)) {
            $contentArray = array_merge($contentArray, $apns);
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
                        ->getRepository(BundleConstants::BUNDLE.':'.'User\UserProfile')
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
     * @param array $apnsArray
     *
     * @return array
     */
    private function getNotificationJsonData(
        $receivers,
        $contentArray = null,
        $messageArray = null,
        $apnsArray = null
    ) {
        $jsonDataArray = array('receivers' => $receivers);

        return $this->setJsonDataArrayBody(
            $jsonDataArray,
            $contentArray,
            $messageArray,
            $apnsArray
        );
    }

    /**
     * @param array $jsonDataArray
     * @param array $contentArray
     * @param array $messageArray
     * @param array $apnsArray
     *
     * @return array
     */
    private function setJsonDataArrayBody(
        $jsonDataArray,
        $contentArray,
        $messageArray,
        $apnsArray = null
    ) {
        // check content array
        if (!is_null($contentArray)) {
            $jsonDataArray['content'] = $contentArray;
        }

        // check message array
        if (!is_null($messageArray)) {
            $jsonDataArray['message'] = $messageArray;
        }

        // check apns array
        if (!is_null($apnsArray)) {
            $jsonDataArray['apns'] = $apnsArray;
        }

        return $jsonDataArray;
    }

    /**
     * @param array $outcasts
     * @param array $contentArray
     * @param array $messageArray
     * @param array $apnsArray
     *
     * @return array
     */
    private function getNotificationBroadcastJsonData(
        $outcasts,
        $contentArray = null,
        $messageArray = null,
        $apnsArray = null
    ) {
        $jsonDataArray = array('outcasts' => $outcasts);

        return $this->setJsonDataArrayBody(
            $jsonDataArray,
            $contentArray,
            $messageArray,
            $apnsArray
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
            $this->callAPI($ch, 'POST', null, $jsonData);
        } catch (Exception $e) {
            error_log('Send XMPP notification went wrong.');
        }
    }

    /**
     * @param $zhMessage
     * @param $enMessage
     *
     * @return array
     */
    protected function setApnsJsonDataArray(
        $zhMessage,
        $enMessage
    ) {
        $apns = array(
            'alert' => array(
                'zh' => $zhMessage,
                'en' => $enMessage,
            ),
        );

        return $apns;
    }
}
