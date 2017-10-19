<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\AdminApiBundle\Controller\Food\AdminFoodNotificationController;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\Security\Acl\Exception\Exception;

trait FoodNotification
{
    use SendNotification;

    /**
     * @param string $action
     * @param User   $user
     * @param string $orderNo
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
     * @param string $action
     * @param User   $recvUser
     * @param string $orderNo
     *
     * @return mixed
     */
    private function generateFoodNotificationJsonData(
        $action,
        $recvUser,
        $orderNo
    ) {
        // get globals
        $globals = $this->getContainer()
                        ->get('twig')
                        ->getGlobals();

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
        if (AdminFoodNotificationController::FOOD_STATUS_COMPLETED == $action) {
            $body = AdminFoodNotificationController::FOOD_ORDER_COMPLETED_MESSAGE;
        } elseif (AdminFoodNotificationController::FOOD_STATUS_CANCELLED == $action) {
            $body = AdminFoodNotificationController::FOOD_ORDER_CANCELLED_MESSAGE;
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
