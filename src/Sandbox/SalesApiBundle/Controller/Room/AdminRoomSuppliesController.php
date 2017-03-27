<?php

namespace Sandbox\SalesApiBundle\Controller\Room;

use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sandbox\ApiBundle\Entity\Room\Room;
use FOS\RestBundle\View\View;

/**
 * Admin Room supplies controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminRoomSuppliesController extends SalesRestController
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
        $this->throwNotFoundIfNull($supplies, self::NOT_FOUND_MESSAGE);

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
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                ),
            ),
            $opLevel
        );
    }
}
