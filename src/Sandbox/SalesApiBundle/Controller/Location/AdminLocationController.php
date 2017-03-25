<?php

namespace Sandbox\SalesApiBundle\Controller\Location;

use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Sales Admin Location Controller.
 *
 * @category Sandbox
 *
 * @author   Feng Li <feng.li@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminLocationController extends SalesRestController
{
    /**
     * @Get("/location/cities")
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     *    name="all",
     *    default=null,
     *    nullable=true,
     *    description="tag of all"
     * )
     *
     * @return View
     */
    public function getCitiesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $all = $paramFetcher->get('all');
        $permissionArray = $paramFetcher->get('permission');

        if (is_null($all)) {
            $adminPlatform = $this->getAdminPlatform();
            $platform = $adminPlatform['platform'];
            $salesCompanyId = $adminPlatform['sales_company_id'];

            $isSuperAdmin = $this->hasSuperAdminPosition(
                $this->getAdminId(),
                $platform,
                $salesCompanyId
            );

            if ($isSuperAdmin ||
                in_array(AdminPermission::KEY_SALES_PLATFORM_ADMIN, $permissionArray)
            ) {
                $cities = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomCity')
                    ->getSalesRoomCityByCompanyId($salesCompanyId);
            } else {
                // get my building ids
                $myBuildingIds = $this->generateLocationSalesBuildingIds(
                    $paramFetcher
                );

                $cities = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomCity')
                    ->getSalesRoomCityByBuilding($myBuildingIds);
            }
        } else {
            $cities = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->findBy(array(
                    'level' => RoomCity::LEVEL_CITY,
                ));
        }

        // generate cities array
        $citiesArray = $this->generateCitiesArray(
            $cities
        );

        return new View($citiesArray);
    }

    /**
     * @Get("/location/buildings")
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    default=null,
     *    nullable=true,
     *    description="city id"
     * )
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
        $permissionArray = $paramFetcher->get('permission');

        $adminPlatform = $this->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $isSuperAdmin = $this->hasSuperAdminPosition(
            $this->getAdminId(),
            $platform,
            $salesCompanyId
        );

        // get buildings by admin type
        if ($isSuperAdmin ||
            in_array(AdminPermission::KEY_SALES_PLATFORM_ADMIN, $permissionArray) ||
            in_array(AdminPermission::KEY_SALES_PLATFORM_BUILDING, $permissionArray)
        ) {
            $buildings = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->getLocationRoomBuildings(
                    $cityId,
                    null,
                    $salesCompanyId
                );
        } else {
            // get my building ids
            $myBuildingIds = $this->generateLocationSalesBuildingIds(
                $paramFetcher
            );

            $buildings = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->getLocationRoomBuildings(
                    $cityId,
                    $myBuildingIds
                );
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['main']));
        $view->setData($buildings);

        return $view;
    }

    /**
     * @param ParamFetcherInterface $paramFetcher
     * @param $adminId
     *
     * @return array
     */
    protected function generateLocationSalesBuildingIds(
        $paramFetcher,
        $adminId = null
    ) {
        if (is_null($adminId)) {
            $adminId = $this->getUser()->getUserId();
        }

        $permissionKeyArray = $paramFetcher->get('permission');
        $opLevel = $paramFetcher->get('op');

        if (is_null($adminId) || is_null($permissionKeyArray) || empty($permissionKeyArray)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $myBuildingIds = $this->getMySalesBuildingIds(
            $adminId,
            $permissionKeyArray,
            $opLevel
        );
    }

    /**
     * @param $cities
     *
     * @return array
     */
    protected function generateCitiesArray(
        $cities
    ) {
        if (is_null($cities) || empty($cities)) {
            return array();
        }

        $citiesArray = array();
        foreach ($cities as $city) {
            $name = $city->getName();

            $cityArray = array(
                'id' => $city->getId(),
                'name' => $name,
            );
            array_push($citiesArray, $cityArray);
        }

        return $citiesArray;
    }
}
