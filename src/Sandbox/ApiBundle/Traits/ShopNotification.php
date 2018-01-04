<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\Security\Acl\Exception\Exception;

trait ShopNotification
{
    use SendNotification;

    /**
     * @param string $action
     * @param User   $user
     * @param string $orderNo
     * @param int    $orderId
     */
    protected function sendXmppShopNotification(
        $action,
        $user,
        $orderNo,
        $orderId
    ) {
        try {
            $userId = $user->getId();

            $key = null;
            if (ShopOrder::STATUS_READY == $action) {
                $key = ShopOrder::READY_NOTIFICATION;
            } elseif (ShopOrder::STATUS_ISSUE == $action) {
                $key = ShopOrder::ISSUE_NOTIFICATION;
            } elseif (ShopOrder::STATUS_REFUNDED == $action) {
                $key = ShopOrder::REFUNDED_NOTIFICATION;
            }

            if (is_null($key)) {
                return;
            }

            $zhBody = $this->getContainer()->get('translator')->trans(
                $key,
                array(),
                null,
                'zh'
            );

            $enBody = $this->getContainer()->get('translator')->trans(
                $key,
                array(),
                null,
                'en'
            );

            $apns = $this->setApnsJsonDataArray($zhBody, $enBody);

            // get content array
            $contentArray = $this->getDefaultContentArray(
                'shop',
                $action,
                null,
                $apns
            );

            $contentArray['order'] = array(
                'id' => $orderId,
                'order_number' => $orderNo,
            );

            $result = $this->compareVersionForJpush([$userId]);
            $jpushReceivers = $result['jpush_users'];

            if (!empty($jpushReceivers)) {
                $zhData = $this->getJpushData(
                    [$userId],
                    ['lang_zh'],
                    $zhBody,
                    '创合秒租',
                    $contentArray
                );

                $enData = $this->getJpushData(
                    [$userId],
                    ['lang_en'],
                    $enBody,
                    'Sandbox3',
                    $contentArray
                );

                $this->sendJpushNotification($zhData);
                $this->sendJpushNotification($enData);
            }
        } catch (Exception $e) {
            error_log('Send food order notification went wrong!');
        }
    }
}
