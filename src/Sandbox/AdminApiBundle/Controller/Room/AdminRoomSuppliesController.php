<?php

namespace Sandbox\AdminApiBundle\Controller\Room;

use Sandbox\ApiBundle\Controller\Room\RoomSuppliesController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sandbox\ApiBundle\Entity\Room\Room;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;

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
        $this->checkAdminRoomSuppliesPermission(AdminPermissionMap::OP_LEVEL_VIEW);

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
        $this->checkAdminRoomSuppliesPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // get attachment
        $supplies = $this->getRepo('Room\Supplies')->find($id);

        return new View($supplies);
    }

    /**
     * Check user permission.
     *
     * @param Integer $OpLevel
     */
    private function checkAdminRoomSuppliesPermission(
        $OpLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_ROOM,
            $OpLevel
        );
    }
}
