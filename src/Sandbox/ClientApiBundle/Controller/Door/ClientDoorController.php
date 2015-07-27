<?php

namespace Sandbox\ClientApiBundle\Controller\Door;

use Sandbox\ApiBundle\Controller\Door\DoorController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Request;

/**
 * Admin Door Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientDoorController extends DoorController
{
    /**
     * @Post("/doors/permission/lost")
     *
     * @param Request $request
     *
     * @return View
     */
    public function lostCardPermissionAction(
        Request $request
    ) {
        $userId = $request->get('user_id');
        $cardNo = $request->get('card_no');
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $userName = $userProfile->getName();

        $orders = $this->getRepo('Order\ProductOrder')->findBy(['userId' => $userId]);
        if (empty($orders)) {
            return $this->customErrorView(
                400,
                self::NO_ORDER_CODE,
                self::NO_ORDER_MESSAGE
            );
        }
        $ids = $this->getRepo('Door\DoorAccess')->getBuildingIds($userId);

        foreach ($ids as $id) {
            $this->get('door_service')->cardPermission(
                $id['buildingId'],
                $userId,
                $userName,
                $cardNo,
                $doorArray = [],
                DoorController::METHOD_LOST
            );
        }
    }
}
