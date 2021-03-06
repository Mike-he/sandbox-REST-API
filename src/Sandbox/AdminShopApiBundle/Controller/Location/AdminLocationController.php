<?php

namespace Sandbox\AdminShopApiBundle\Controller\Location;

use Sandbox\AdminShopApiBundle\Controller\ShopRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Location Controller.
 *
 * @category Sandbox
 *
 * @author   Feng Li <feng.li@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminLocationController extends ShopRestController
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
     * @return View
     */
    public function getCitiesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $permissionArray = $paramFetcher->get('permission');

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $isSuperAdmin = $this->hasSuperAdminPosition(
            $this->getAdminId(),
            $platform,
            $salesCompanyId
        );

        // get cities by admin type
        if ($isSuperAdmin ||
            in_array(AdminPermission::KEY_SHOP_PLATFORM_SHOP, $permissionArray) ||
            in_array(AdminPermission::KEY_SHOP_PLATFORM_ADMIN, $permissionArray)
        ) {
            $myBuildings = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->getBuildingsByCompany($salesCompanyId);
            $myBuildingIds = array_map('current', $myBuildings);

            $cities = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->getSalesRoomCityByBuilding($myBuildingIds);
        } else {
            // get my shops ids
            $myShopIds = $this->generateLocationShopIds(
                $paramFetcher
            );

            $cities = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->getSalesRoomCityByShop($myShopIds);
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

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $isSuperAdmin = $this->hasSuperAdminPosition(
            $this->getAdminId(),
            $platform,
            $salesCompanyId
        );

        // get buildings by admin type
        if ($isSuperAdmin ||
            in_array(AdminPermission::KEY_SHOP_PLATFORM_SHOP, $permissionArray) ||
            in_array(AdminPermission::KEY_SHOP_PLATFORM_ADMIN, $permissionArray)
        ) {
            $buildings = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->getLocationRoomBuildings(
                    $cityId,
                    null,
                    $salesCompanyId
                );
        } else {
            // get my shops ids
            $myShopIds = $this->generateLocationShopIds(
                $paramFetcher
            );

            $buildings = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->getLocationBuildingByShop(
                    $myShopIds
                );
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['main']));
        $view->setData($buildings);

        return $view;
    }

    /**
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return array
     */
    protected function generateLocationShopIds(
        $paramFetcher
    ) {
        $adminId = $this->getUser()->getAdminId();

        $permissionKeyArray = $paramFetcher->get('permission');
        $opLevel = $paramFetcher->get('op');

        if (is_null($adminId) || is_null($permissionKeyArray) || empty($permissionKeyArray)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $myBuildingIds = $this->getMyShopIds(
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
