<?php

namespace Sandbox\SalesApiBundle\Controller\Room;

use Sandbox\ApiBundle\Controller\Room\RoomSuppliesController;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermission;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminType;
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
        // get attachment
        $supplies = $this->getRepo('Room\Supplies')->find($id);
        $this->throwNotFoundIfNull($supplies, self::NOT_FOUND_MESSAGE);

        return new View($supplies);
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     * @param int $buildingId
     */
    private function checkAdminRoomSuppliesPermission(
        $opLevel,
        $buildingId
    ) {
        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $this->getAdminId(),
            SalesAdminType::KEY_PLATFORM,
            SalesAdminPermission::KEY_PLATFORM_ROOM,
            $opLevel,
            $buildingId
        );
    }
}
