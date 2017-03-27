<?php

namespace Sandbox\AdminApiBundle\Controller\Room;

use Sandbox\ApiBundle\Controller\Room\RoomSuppliesController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sandbox\ApiBundle\Entity\Room\Room;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;

/**
 * Admin Room supplies controller.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminRoomSuppliesController extends RoomSuppliesController
{
    /**
     * Get a office supplies.
     *
     * @param Request $request
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/rooms/supplies")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getOfficeSuppliesAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminRoomSuppliesPermission(AdminPermission::OP_LEVEL_VIEW);

        // get supplies
        $supplies = $this->getRepo('Room\Supplies')->findAll();

        return new View($supplies);
    }

    /**
     * Get a office supply.
     *
     * @param Request $request
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/rooms/supplies/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getSupplyAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminRoomSuppliesPermission(AdminPermission::OP_LEVEL_VIEW);

        // get attachment
        $supplies = $this->getRepo('Room\Supplies')->find($id);

        return new View($supplies);
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminRoomSuppliesPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ROOM],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE],
            ],
            $opLevel
        );
    }
}
