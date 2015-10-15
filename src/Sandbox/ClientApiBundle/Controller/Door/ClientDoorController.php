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
        $cardNo = $this->getCardNoIfUserAuthorized();
        if (is_null($cardNo)) {
            return $this->customErrorView(
                400,
                self::CARDNO_NOT_FOUND_CODE,
                self::CARDNO_NOT_FOUND_MESSAGE
            );
        }

        $this->updateEmployeeCardStatus(
            $userId,
            '',
            $cardNo,
            DoorController::METHOD_LOST
        );
    }
}
