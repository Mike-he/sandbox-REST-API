<?php

namespace Sandbox\ClientApiBundle\Controller\Door;

use Sandbox\ApiBundle\Controller\Door\DoorController;
use FOS\RestBundle\View\View;
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
        $userId = $this->getUserId();
        $result = $this->getCardNoIfUserAuthorized();
        if ($result['status'] === DoorController::STATUS_UNAUTHED || is_null($result)) {
            return $this->customErrorView(
                400,
                self::CARDNO_NOT_FOUND_CODE,
                self::CARDNO_NOT_FOUND_MESSAGE
            );
        }
        $cardNo = $result['card_no'];

        $this->updateEmployeeCardStatus(
            $userId,
            '',
            $cardNo,
            DoorController::METHOD_LOST
        );
    }
}
