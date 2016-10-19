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
 * @link     http://www.Sandbox.cn/
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
                $receivers = $result['users'];
                $jpushReceivers = $result['jpush_users'];

                if (!empty($receivers)) {
                    // get notification data
                    $data = $this->getProductOrderNotificationJsonData(
                        $order->getId(),
                        $order->getOrderNumber(),
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
                        $order->getId(),
                        $order->getOrderNumber(),
                        $fromUserId,
                        $receivers,
                        $action,
                        $bodyZh,
                        $bodyEn
                    );
                }
            } else {
                $dataArray = [];
                foreach ($orders as $order) {
                    $userArray = [$order->getUserId()];
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
                    } else {
                        $data = $this->getProductOrderNotificationJsonData(
                            $order->getId(),
                            $order->getOrderNumber(),
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
    private function getProductOrderNotificationJsonData(
        $orderId,
        $orderNumber,
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
            ProductOrder::ACTION_TYPE,
            $action,
            $fromUser,
            $apns
        );

        // get order array
        $contentArray['order'] = $this->getOrderArray($orderId, $orderNumber);

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
            '展想创合',
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
