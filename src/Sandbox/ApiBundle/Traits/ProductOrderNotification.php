<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Constants\BundleConstants;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Order Notification Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
trait ProductOrderNotification
{
    use SendNotification;

    /**
     * @param ProductOrder $order
     * @param array        $receivers
     * @param string       $action
     * @param null         $fromUserId
     * @param array        $orders
     * @param null         $first
     * @param null         $second
     */
    protected function sendXmppProductOrderNotification(
        $order,
        $receivers,
        $action,
        $fromUserId = null,
        $orders = [],
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

            if (empty($orders) && !is_null($order)) {
                $city = $order->getProduct()->getRoom()->getCity()->getName();
                $building = $order->getProduct()->getRoom()->getBuilding()->getName();
                $room = $order->getProduct()->getRoom()->getName();

                $bodyZh = $firstZh.$city.$building.$room.$secondZh;
                $bodyEn = $firstEn.$city.$building.$room.$secondEn;

                $result = $this->compareVersionForJpush($receivers);
                $jpushReceivers = $result['jpush_users'];

                if (!empty($jpushReceivers)) {
                    $this->setDataAndJPushNotification(
                        $order->getId(),
                        $order->getOrderNumber(),
                        $fromUserId,
                        $jpushReceivers,
                        $action,
                        $bodyZh,
                        $bodyEn
                    );
                }
            } else {
                foreach ($orders as $order) {
                    if ($order->getUser()) {
                        $userArray = [$order->getUserId()];
                    } elseif ($order->getCustomerId()) {
                        $customer = $this->getContainer()
                            ->get('doctrine')
                            ->getRepository('SandboxApiBundle:User\UserCustomer')
                            ->find($order->getCustomerId());

                        $userId = $customer ? $customer->getUserId() : null;

                        if ($userId) {
                            $userArray = [$userId];
                        } else {
                            continue;
                        }
                    }

                    $result = $this->compareVersionForJpush($userArray);
                    $jpushReceivers = $result['jpush_users'];

                    if (!empty($jpushReceivers)) {
                        $this->setDataAndJPushNotification(
                            $order->getId(),
                            $order->getOrderNumber(),
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
     * @param int    $orderId
     * @param string $orderNumber
     *
     * @return array
     */
    private function getOrderArray(
        $orderId,
        $orderNumber
    ) {
        return [
            'id' => $orderId,
            'order_number' => $orderNumber,
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
            ProductOrder::ACTION_TYPE,
            $action,
            $fromUser,
            $apns
        );

        // get order array
        $contentArray['order'] = $this->getOrderArray($orderId, $orderNumber);

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
