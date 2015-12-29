<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\AdminApiBundle\Controller\Food\AdminFoodNotificationController;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\Security\Acl\Exception\Exception;

trait FoodNotification
{
    use SendNotification;

    /**
     * @param $action
     * @param $user
     * @param $orderNo
     */
    protected function sendXmppFoodNotification(
        $action,
        $user,
        $orderNo
    ) {
        try {
            // get json data
            $jsonData = $this->generateFoodNotificationJsonData($action, $user, $orderNo);

            // send xmpp notification
            $this->sendXmppNotification($jsonData, false);
        } catch (Exception $e) {
            error_log('Send food order notification went wrong!');
        }
    }

    /**
     * @param $action
     * @param $recvUser
     * @param $orderNo
     *
     * @return mixed
     */
    private function generateFoodNotificationJsonData(
        $action,
        $recvUser,
        $orderNo
    ) {
        // get globals
        $globals = $this->getGlobals();

        $domainURL = $globals['xmpp_domain'];

        // get receivers
        $receiversArray = array(
            array('jid' => $recvUser->getXmppUsername().'@'.$domainURL),
        );

        // get content array
        $contentArray = $this->getDefaultContentArray(
            'food', $action
        );

        $contentArray['food'] = array(
            'order_no' => $orderNo,
        );

        $jid = User::XMPP_SERVICE.'@'.$domainURL;

        $body = null;
        if ($action == AdminFoodNotificationController::FOOD_STATUS_COMPLETED) {
            $body = AdminFoodNotificationController::FOOD_ORDER_COMPLETED_MESSAGE;
        } elseif ($action == AdminFoodNotificationController::FOOD_STATUS_REFUNDED) {
            $body = AdminFoodNotificationController::FOOD_ORDER_REFUNDED_MESSAGE;
        }

        $messageArray = null;
        if (!is_null($body)) {
            $messageArray = array(
                'type' => 'chat',
                'from' => $jid,
                'body' => $body,
            );
        }

        $data = $this->getNotificationJsonData(
            $receiversArray,
            $contentArray,
            $messageArray
        );

        return json_encode(array($data));
    }
}
