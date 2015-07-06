<?php

namespace Sandbox\ApiBundle\Controller\Location;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * Location Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class LocationController extends SandboxRestController
{
    /**
     * @Get("/cities")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getCitiesAction(
        Request $request
    ) {
        $cities = $this->getRepo('Room\RoomCity')->findAll();

        return new View($cities);
    }

    /**
     * @Get("/buildings")
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    default=null,
     *    nullable=true,
     *    description="city id"
     * )
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getBuildingsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $cityId = $paramFetcher->get('city');
        if (!is_null($cityId)) {
            $buildings = $this->getRepo('Room\RoomBuilding')->findBy(
                ['cityId' => $cityId]
            );

            return new View($buildings);
        }
        $buildings = $this->getRepo('Room\RoomBuilding')->findAll();

        return new View($buildings);
    }

    /**
     * @Get("/buildings/{id}")
     *
     * @param Request $request
     * @param $id
     */
    public function getOneBuildingAction(
        Request $request,
        $id
    ) {
        $building = $this->getRepo('Room\RoomBuilding')->find($id);

        return new View($building);
    }

    /**
     * @Get("/floors")
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    default=null,
     *    nullable=true,
     *    description="building id"
     * )
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getFloorsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $buildingId = $paramFetcher->get('building');
        if (!is_null($buildingId)) {
            $floors = $this->getRepo('Room\RoomFloor')->findBy(
                ['buildingId' => $buildingId]
            );

            return new View($floors);
        }
        $floors = $this->getRepo('Room\RoomFloor')->findAll();

        return new View($floors);
    }
}
