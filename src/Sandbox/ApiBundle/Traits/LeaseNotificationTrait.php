<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Constants\BundleConstants;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Lease Notification Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait LeaseNotificationTrait
{
    use SendNotification;

    /**
     * @param Lease  $lease
     * @param array  $receivers
     * @param string $action
     * @param null   $fromUserId
     * @param array  $leases
     * @param null   $first
     * @param null   $second
     */
    protected function sendXmppLeaseNotification(
        $lease,
        $receivers,
        $action,
        $fromUserId = null,
        $leases = [],
        $first = null,
        $second = null
    ) {
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

        try {
            $jsonData = null;

            if (empty($orders) && !is_null($lease)) {
                $city = $lease->getCityName();
                $building = $lease->getBuildingName();
                $room = $lease->getRoomName();

                $bodyZh = $firstZh.$city.$building.$room.$secondZh;
                $bodyEn = $firstEn.$city.$building.$room.$secondEn;

                $result = $this->compareVersionForJpush($receivers);
                $receivers = $result['users'];
                $jpushReceivers = $result['jpush_users'];

                if (!empty($receivers)) {
                    // generate notification data
                    $data = $this->generateLeaseNotificationJsonData(
                        $lease->getId(),
                        $lease->getAccessNo(),
                        $fromUserId,
                        $receivers,
                        $action,
                        $bodyZh,
                        $bodyEn
                    );

                    $jsonData = json_encode(array($data));
                }

                if (!empty($jpushReceivers)) {
                    $this->setDataAndJPushNotification(
                        $lease->getId(),
                        $lease->getAccessNo(),
                        $fromUserId,
                        $jpushReceivers,
                        $action,
                        $bodyZh,
                        $bodyEn
                    );
                }
            } else {
                $dataArray = [];
                foreach ($leases as $lease) {
                    $userArray = [$lease->getSupervisorId()];
                    $result = $this->compareVersionForJpush($userArray);
                    $jpushReceivers = $result['jpush_users'];

                    if (!empty($jpushReceivers)) {
                        $this->setDataAndJPushNotification(
                            $lease->getId(),
                            $lease->getAccessNo(),
                            $fromUserId,
                            $jpushReceivers,
                            $action,
                            $firstZh,
                            $firstEn
                        );
                    } else {
                        $data = $this->generateLeaseNotificationJsonData(
                            $lease->getId(),
                            $lease->getAccessNo(),
                            $fromUserId,
                            $userArray,
                            $action,
                            $firstZh,
                            $firstEn
                        );

                        array_push($dataArray, $data);
                        $jsonData = json_encode($dataArray);
                    }
                }
            }

            // send xmpp notification
            if (!is_null($jsonData)) {
                $this->sendXmppNotification($jsonData, false);
            }
        } catch (Exception $e) {
            error_log('Send message notification went wrong!');
        }
    }

    /**
     * @param int    $leaseId
     * @param string $leaseNumber
     * @param int    $fromUserId
     * @param array  $receivers
     * @param string $action
     * @param string $bodyZh
     * @param string $bodyEn
     *
     * @return mixed
     */
    private function generateLeaseNotificationJsonData(
        $leaseId,
        $leaseNumber,
        $fromUserId,
        $receivers,
        $action,
        $bodyZh,
        $bodyEn
    ) {
        $globals = $this->getContainer()
            ->get('twig')
            ->getGlobals();

        $domainURL = $globals['xmpp_domain'];

        $fromUser = null;
        if (!is_null($fromUserId)) {
            $fromUser = $this->getContainer()
                ->get('doctrine')
                ->getRepository(BundleConstants::BUNDLE.':'.'User\User')
                ->find($fromUserId);
        }

        // get receivers array
        $receiversArray = [];
        foreach ($receivers as $receiverId) {
            $recevUser = $this->getContainer()
                ->get('doctrine')
                ->getRepository(BundleConstants::BUNDLE.':'.'User\User')
                ->find($receiverId);

            array_push($receiversArray, ['jid' => $recevUser->getXmppUsername().'@'.$domainURL]);
        }

        $apns = $this->setApnsJsonDataArray($bodyZh, $bodyEn);

        // get content array
        $contentArray = $this->getDefaultContentArray(
            LeaseConstants::ACTION_TYPE,
            $action,
            $fromUser,
            $apns
        );

        // get order array
        $contentArray['order'] = $this->getLeaseArray($leaseId, $leaseNumber);

        $jid = User::XMPP_SERVICE.'@'.$domainURL;
        // get message from service account
        $messageArray = [
            'type' => 'chat',
            'from' => $jid,
            'body' => $bodyZh,
        ];

        return $this->getNotificationJsonData(
            $receiversArray,
            $contentArray,
            $messageArray,
            $apns
        );
    }

    /**
     * @param int    $leaseId
     * @param string $leaseNumber
     *
     * @return array
     */
    private function getLeaseArray(
        $leaseId,
        $leaseNumber
    ) {
        return [
            'id' => $leaseId,
            'order_number' => $leaseNumber,
        ];
    }

    /**
     * @param int    $orderId
     * @param string $orderNumber
     * @param int    $fromUserId
     * @param array  $receivers
     * @param string $action
     * @param string $bodyZh
     * @param string $bodyEn
     *
     * @return mixed
     */
    private function setDataAndJPushNotification(
        $orderId,
        $orderNumber,
        $fromUserId,
        $receivers,
        $action,
        $bodyZh,
        $bodyEn
    ) {
        $fromUser = null;
        if (!is_null($fromUserId)) {
            $fromUser = $this->getContainer()
                ->get('doctrine')
                ->getRepository(BundleConstants::BUNDLE.':'.'User\User')
                ->find($fromUserId);
        }

        $apns = $this->setApnsJsonDataArray($bodyZh, $bodyEn);

        // get content array
        $contentArray = $this->getDefaultContentArray(
            LeaseConstants::ACTION_TYPE,
            $action,
            $fromUser,
            $apns
        );

        // get order array
        $contentArray['order'] = $this->getLeaseArray($orderId, $orderNumber);

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
}
