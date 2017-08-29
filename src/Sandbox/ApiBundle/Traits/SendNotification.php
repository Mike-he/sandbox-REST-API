<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Constants\BundleConstants;
use Sandbox\ApiBundle\Constants\LeaseConstants;
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

        // removed chat message to send push notification only
        $messageArray = null;

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
        return;
//        try {
//            return;

//            $globals = $this->getContainer()
//                            ->get('twig')
//                            ->getGlobals();

//            // openfire API URL
//            $apiURL = $globals['openfire_innet_url'].
//                $globals['openfire_plugin_sandbox'].
//                $globals['openfire_plugin_sandbox_notification'];

//            if ($broadcast) {
//                $apiURL = $apiURL.$globals['openfire_plugin_sandbox_notification_broadcast'];
//            }

//            // call OpenFire API
//            $ch = curl_init($apiURL);
//            $this->callAPI($ch, 'POST', null, $jsonData);
//        } catch (Exception $e) {
//            error_log('Send XMPP notification went wrong.');
//        }
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

    /**
     * @param $users
     * @param $language
     * @param $message
     * @param $title
     * @param $contentArray
     *
     * @return object
     */
    protected function getJpushData(
        $users,
        $language,
        $message,
        $title,
        $contentArray,
        $sendMessage = false
    ) {
        // get globals
        $globals = $this->getContainer()
            ->get('twig')
            ->getGlobals();
        $option = $globals['jpush_apns_option'];

        if ($users == 'all') {
            $audience = 'all';
        } else {
            $audience = [
                'alias' => $users,
                'tag' => $language,
            ];
        }

        $data = [
            'platform' => 'all',
            'audience' => $audience,
            'notification' => [
                'alert' => $message,
                'android' => [
                    'alert' => $message,
                    'title' => $title,
                    'extras' => $contentArray,
                ],
                'ios' => [
                    'alert' => $message,
                    'sound' => 'default',
                    'badge' => '+1',
                    'extras' => $contentArray,
                ],
            ],
            'options' => [
                'apns_production' => filter_var($option, FILTER_VALIDATE_BOOLEAN),
            ],
        ];

        if ($sendMessage) {
            $data['message'] = [
                'msg_content' => $message,
                'content_type' => 'text',
                'title' => $title,
                'extras' => $contentArray,
            ];
        }

        return json_encode($data);
    }

    /**
     * @param object $jsonData
     */
    protected function sendJpushNotification(
        $jsonData
    ) {
        try {
            $apiURL = 'https://api.jpush.cn/v3/push';

            $appKey = $this->getContainer()->getParameter('jpush_key');
            $masterSecret = $this->getContainer()->getParameter('jpush_secret');

            $auth = base64_encode($appKey.':'.$masterSecret);
            // call JPush API
            $ch = curl_init($apiURL);
            $headers[] = 'Authorization: Basic '.$auth;

            $this->callAPI(
                $ch,
                'POST',
                $headers,
                $jsonData
            );
        } catch (Exception $e) {
            error_log('Send JPush notification went wrong.');
        }
    }

    /**
     * @param $receivers
     *
     * @return array
     */
    protected function compareVersionForJpush(
       $receivers
    ) {
        $jpushReceivers = [];
        foreach ($receivers as $receiver) {
            $tokens = $this->getContainer()
                ->get('doctrine')
                ->getRepository('SandboxApiBundle:User\UserToken')
                ->findBy(
                    ['userId' => $receiver],
                    ['clientId' => 'DESC']
                );

            foreach ($tokens as $token) {
                if (!empty($jpushReceivers)) {
                    continue;
                }

                $client = $this->getContainer()
                    ->get('doctrine')
                    ->getRepository('SandboxApiBundle:User\UserClient')
                    ->find($token->getClientId());

                if (is_null($client)) {
                    continue;
                }

                $name = $client->getName();
                if (is_null($name) || $name == 'SandBox Admin') {
                    continue;
                }

                $version = $client->getVersion();
                if (!is_null($version) && !empty($version)) {
                    $versionArray = explode('.', $version);

                    if ((int) $versionArray[0] < 2) {
                        continue;
                    } elseif ((int) $versionArray[0] == 2) {
                        if ((int) $versionArray[1] < 2) {
                            continue;
                        } elseif ((int) $versionArray[1] == 2) {
                            $versionNumber = preg_replace('/[^0-9]/', '', $versionArray[2]);

                            if ((int) $versionNumber < 9) {
                                continue;
                            }
                        }
                    }

                    array_push($jpushReceivers, $receiver);
                }
            }
        }

        $receivers = array_diff($receivers, $jpushReceivers);

        return array(
            'users' => $receivers,
            'jpush_users' => $jpushReceivers,
        );
    }

    private function generateJpushNotification(
        $receivers,
        $first = null,
        $second = null,
        $contentArray = [],
        $extra = null
    ) {
        $firstZh = '';
        $secondZh = '';
        $secondEn = '';
        $firstEn = '';

        if (!is_null($first)) {
            $firstZh = $this->getContainer()->get('translator')->trans(
                $first,
                array(),
                null,
                'zh'
            );

            $firstEn = $this->getContainer()->get('translator')->trans(
                $first,
                array(),
                null,
                'en'
            );
        }

        if (!is_null($second)) {
            $secondZh = $this->getContainer()->get('translator')->trans(
                $second,
                array(),
                null,
                'zh'
            );

            $secondEn = $this->getContainer()->get('translator')->trans(
                $second,
                array(),
                null,
                'en'
            );
        }

        $bodyZh = $firstZh.$extra.$secondZh;
        $bodyEn = $firstEn.$extra.$secondEn;

        $zhData = $this->getJpushData(
            $receivers,
            ['lang_zh'],
            $bodyZh,
            '创合秒租',
            $contentArray
        );

        $enData = $this->getJpushData(
            $receivers,
            ['lang_en'],
            $bodyEn,
            'Sandbox3',
            $contentArray
        );

        $this->sendJpushNotification($zhData);
        $this->sendJpushNotification($enData);
    }

    private function generateLeaseContentArray(
        $urlParam,
        $urlPath = null
    ) {
        $leaseUrl = $this->getContainer()
            ->getParameter('orders_url');

        if (is_null($urlPath)) {
            $urlPath = 'contract';
        }
        $url = $leaseUrl.'/'.$urlPath.'?'.$urlParam;

        return [
            'type' => LeaseConstants::ACTION_TYPE,
            'url' => $url,
        ];
    }
}
