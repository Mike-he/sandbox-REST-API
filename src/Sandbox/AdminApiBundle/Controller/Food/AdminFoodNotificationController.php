<?php

namespace Sandbox\AdminApiBundle\Controller\Food;

use Sandbox\ApiBundle\Controller\Food\FoodController;
use Sandbox\ApiBundle\Entity\Food\Food;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Traits\FoodNotification;

/**
 * Admin Food Notification Controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminFoodNotificationController extends FoodController
{
    use FoodNotification;

    const FOOD_STATUS_COMPLETED = 'completed';
    const FOOD_STATUS_REFUNDED = 'refunded';
    const FOOD_ORDER_COMPLETED_MESSAGE = '好吃的好喝的都已经准备好了哦，快来吧台领取吧~';
    const FOOD_ORDER_REFUNDED_MESSAGE = '您的订单好像有点问题，快来吧台询问一下吧~';

    /**
     * @param Request $request
     * @param $orderNo
     *
     * @Route("/food/orders/{orderNo}/notification")
     * @Method({"POST"})
     *
     * @return View
     */
    public function adminFoodNotificationAction(
        Request $request,
        $orderNo
    ) {
        // get auth
        $headers = apache_request_headers();
        $auth = $headers['Sandbox-Auth'];

        // compare auth
        $this->encodedKeysComparison($auth);

        $requestData = json_decode($request->getContent(), true);

        if (!is_null($requestData)) {
            $action = $requestData['status'];
            $userId = $requestData['user_id'];

            $user = $this->getRepo('User\User')->find($userId);
            $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

            $this->sendXmppFoodNotification($action, $user, $orderNo);
        }

        return new View();
    }
}
