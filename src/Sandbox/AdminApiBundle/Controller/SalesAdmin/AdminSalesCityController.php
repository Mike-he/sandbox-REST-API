<?php

namespace Sandbox\AdminApiBundle\Controller\SalesAdmin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Location\LocationController;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

/**
 * Class AdminCityController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminSalesCityController extends LocationController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Annotations\QueryParam(
     *    name="permission",
     *    default=null,
     *    nullable=false,
     *    description="permission key"
     * )
     *
     * @Annotations\QueryParam(
     *    name="op",
     *    default=1,
     *    nullable=true,
     *    description="op level"
     * )
     *
     * @Annotations\QueryParam(
     *    name="admin",
     *    default=null,
     *    nullable=true,
     *    description="id of admin"
     * )
     *
     * @Route("/{id}/cities")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesCitiesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $admin = $this->getRepo('User\User')->find($id);
        $this->throwNotFoundIfNull($admin, self::NOT_FOUND_MESSAGE);

        // get my building ids
        $myBuildingIds = $this->generateLocationSalesBuildingIds(
            $paramFetcher,
            $id
        );

        $cities = $this->getRepo('Room\RoomCity')->getSalesRoomCityByBuilding($myBuildingIds);

        // generate cities array
        $citiesArray = $this->generateCitiesArray(
            $cities
        );

        return new View($citiesArray);
    }
}
