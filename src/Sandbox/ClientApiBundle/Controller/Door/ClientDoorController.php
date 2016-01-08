<?php

namespace Sandbox\ClientApiBundle\Controller\Door;

use Sandbox\ApiBundle\Controller\Door\DoorController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Request;
use Sandbox\ApiBundle\Constants\DoorAccessConstants;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;

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
    use DoorAccessTrait;

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
        if (
            is_null($result) ||
            $result['status'] === DoorController::STATUS_UNAUTHED
        ) {
            return $this->customErrorView(
                400,
                self::CARDNO_NOT_FOUND_CODE,
                self::CARDNO_NOT_FOUND_MESSAGE
            );
        }

        // set card
        $cardNo = $result['card_no'];
        $this->callUpdateCardStatusCommand(
            $userId,
            $cardNo,
            DoorAccessConstants::METHOD_LOST
        );
    }
}
