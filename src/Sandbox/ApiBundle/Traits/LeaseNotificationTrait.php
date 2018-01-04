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
 * @see     http://www.Sandbox.cn/
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

            if (empty($leases) && !is_null($lease)) {
                $city = $lease->getCityName();
                $building = $lease->getBuildingName();
                $room = $lease->getRoomName();

                $bodyZh = $firstZh.$city.$building.$room.$secondZh;
                $bodyEn = $firstEn.$city.$building.$room.$secondEn;

                $result = $this->compareVersionForJpush($receivers);
                $jpushReceivers = $result['jpush_users'];

                if (!empty($jpushReceivers)) {
                    $this->setLeaseJPushNotification(
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
                foreach ($leases as $lease) {
                    $userArray = [$lease->getSupervisorId()];
                    $result = $this->compareVersionForJpush($userArray);
                    $jpushReceivers = $result['jpush_users'];

                    if (!empty($jpushReceivers)) {
                        $this->setLeaseJPushNotification(
                            $lease->getId(),
                            $lease->getAccessNo(),
                            $fromUserId,
                            $jpushReceivers,
                            $action,
                            $firstZh,
                            $firstEn
                        );
                    }
                }
            }
        } catch (Exception $e) {
            error_log('Send message notification went wrong!');
        }
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
    private function setLeaseJPushNotification(
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
