<?php

namespace Sandbox\ApiBundle\Controller\Location;

use Sandbox\AdminShopApiBundle\Entity\Auth\ShopAdminApiAuth;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sandbox\SalesApiBundle\Entity\Auth\SalesAdminApiAuth;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;

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
class LocationController extends SalesRestController
{
    const LOCATION_CITY_PREFIX = 'location.city.';

    /**
     * @Get("/cities")
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
        $user = $this->getUser();

        $all = $paramFetcher->get('all');

        // get all cities
        $cities = $this->getRepo('Room\RoomCity')->findAll();

        if (!is_null($user) && is_null($all)) {
            // sales bundle
            if ($user->getRoles() == array(SalesAdminApiAuth::ROLE_SALES_ADMIN_API)) {
                // get my building ids
                $myBuildingIds = $this->generateLocationSalesBuildingIds(
                    $paramFetcher
                );

                $cities = $this->getRepo('Room\RoomCity')->getSalesRoomCityByBuilding($myBuildingIds);
            }

            // shop bundle
            if ($user->getRoles() == array(ShopAdminApiAuth::ROLE_SHOP_ADMIN_API)) {
                $admin = $this->getRepo('Shop\ShopAdmin')->find($this->getUser()->getAdminId());

                // get cities by admin type
                if ($admin->getType()->getKey() == ShopAdminType::KEY_SUPER) {
                    $myBuildings = $this->getRepo('Room\RoomBuilding')->getBuildingsByCompany($admin->getCompanyId());
                    $myBuildingIds = array_map('current', $myBuildings);

                    $cities = $this->getRepo('Room\RoomCity')->getSalesRoomCityByBuilding($myBuildingIds);
                } else {
                    // get my shops ids
                    $myShopIds = $this->generateLocationShopIds(
                        $paramFetcher
                    );

                    $cities = $this->getRepo('Room\RoomCity')->getSalesRoomCityByShop($myShopIds);
                }
            }
        }

        // generate cities array
        $citiesArray = $this->generateCitiesArray(
            $cities
        );

        return new View($citiesArray);
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
        $user = $this->getUser();

        $cityId = $paramFetcher->get('city');

        // get all buildings
        $buildings = $this->getRepo('Room\RoomBuilding')->getLocationRoomBuildings($cityId);

        if (!is_null($user)) {
            // sales bundle
            if ($user->getRoles() == array(SalesAdminApiAuth::ROLE_SALES_ADMIN_API)) {
                // get my building ids
                $myBuildingIds = $this->generateLocationSalesBuildingIds(
                    $paramFetcher
                );

                $buildings = $this->getRepo('Room\RoomBuilding')->getLocationRoomBuildings(
                    $cityId,
                    $myBuildingIds
                );
            }

            // shop bundle
            if ($user->getRoles() == array(ShopAdminApiAuth::ROLE_SHOP_ADMIN_API)) {
                $admin = $this->getRepo('Shop\ShopAdmin')->find($this->getUser()->getAdminId());

                // get buildings by admin type
                if ($admin->getType()->getKey() == ShopAdminType::KEY_SUPER) {
                    $buildings = $this->getRepo('Room\RoomBuilding')->getLocationRoomBuildings(
                        $cityId,
                        null,
                        $admin->getCompanyId()
                    );
                } else {
                    // get my shops ids
                    $myShopIds = $this->generateLocationShopIds(
                        $paramFetcher
                    );

                    $buildings = $this->getRepo('Room\RoomBuilding')->getLocationBuildingByShop(
                        $cityId,
                        $myShopIds
                    );
                }
            }
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['main']));
        $view->setData($buildings);

        return $view;
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
        } else {
            $floors = $this->getRepo('Room\RoomFloor')->findAll();
        }

        $view = new View($floors);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['main']));

        return $view;
    }

    /**
     * Get building avatar.
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
     * @Route("/buildings/{id}/avatar")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getBuildingAvatarAction(
        Request $request,
        $id
    ) {
        $building = $this->getRepo('Room\RoomBuilding')->find($id);
        if (is_null($building)) {
            throw new NotFoundHttpException();
        }

        $filePath = $building->getAvatar();
        if (is_null($filePath)) {
            throw new NotFoundHttpException();
        }

        $hash = hash_file('md5', $filePath);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->setEtag($hash);

        $response->setCallback(function () use ($filePath) {
            $bytes = @readfile($filePath);
            if ($bytes === false || $bytes <= 0) {
                throw new NotFoundHttpException();
            }
        });

        $response->send();
    }

    /**
     * Get closest building.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Annotations\QueryParam(
     *    name="lat",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="coordinate lat"
     * )
     *
     * @Annotations\QueryParam(
     *    name="lng",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="coordinate lng"
     * )
     *
     * @Annotations\QueryParam(
     *    name="addon",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="shop addon"
     * )
     *
     * @Route("/buildings/nearby")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getNearbyBuildingsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $lat = $paramFetcher->get('lat');
        $lng = $paramFetcher->get('lng');
        $addon = $paramFetcher->get('addon');

        $globals = $this->getGlobals();
        $range = $globals['nearby_range_km'];
        $viewGroup = 'building_nearby';

        if ($addon == 'shop') {
            $range = $globals['nearby_shop_range_km'];
            $viewGroup = 'shop_nearby';
        }

        $buildings = $this->getRepo('Room\RoomBuilding')->findNearbyBuildings(
            $lat,
            $lng,
            $range
        );

        if ($addon == 'shop') {
            foreach ($buildings as $building) {
                $shops = $this->getRepo('Shop\Shop')->getShopByBuilding(
                    $building->getId(),
                    true,
                    true
                );

                $building->setShops($shops);
            }
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups([$viewGroup]));
        $view->setData($buildings);

        return $view;
    }

    /**
     * @Get("/buildings/{id}")
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function getOneBuildingAction(
        Request $request,
        $id
    ) {
        $building = $this->getRepo('Room\RoomBuilding')->find($id);

        // set more information
        $this->setRoomBuildingMoreInformation($building);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['main']));
        $view->setData($building);

        return $view;
    }

    /**
     * Set room building more information.
     *
     * @param RoomBuilding $building
     *
     * @return RoomBuilding
     */
    protected function setRoomBuildingMoreInformation(
        $building
    ) {
        // set floor numbers
        $floors = $this->getRepo('Room\RoomFloor')->findByBuilding($building);
        $building->setFloors($floors);

        // set building attachments
        $buildingAttachments = $this->getRepo('Room\RoomBuildingAttachment')->findByBuilding($building);
        $building->setBuildingAttachments($buildingAttachments);

        // set building company
        $buildingCompany = $this->getRepo('Room\RoomBuildingCompany')->findOneByBuilding($building);
        $building->setBuildingCompany($buildingCompany);

        // set phones
        $phones = $this->getRepo('Room\RoomBuildingPhones')->findByBuilding($building);
        $building->setPhones($phones);

        // set shop counts
        $shopCounts = $this->getRepo('Shop\Shop')->countsShopByBuilding($building);
        $building->setShopCounts((int) $shopCounts);

        // set room counts
        $roomCounts = $this->getRepo('Room\Room')->countsRoomByBuilding($building);
        $building->setRoomCounts((int) $roomCounts);

        // set product counts
        $productCounts = $this->getRepo('Product\Product')->countsProductByBuilding($building);
        $building->setProductCounts((int) $productCounts);

        // set order counts
        $orderCounts = $this->getRepo('Order\ProductOrder')->countsOrderByBuilding($building);
        $building->setOrderCounts((int) $orderCounts);

        return $building;
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
            $adminId = $this->getUser()->getAdminId();
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
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return array
     */
    private function generateLocationShopIds(
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
        $citiesArray = array();
        foreach ($cities as $city) {
            $name = $city->getName();
            $key = $city->getKey();

            $translatedKey = self::LOCATION_CITY_PREFIX.$key;
            $translatedName = $this->get('translator')->trans($translatedKey);
            if ($translatedName != $translatedKey) {
                $name = $translatedName;
            }

            $cityArray = array(
                'id' => $city->getId(),
                'key' => $key,
                'name' => $name,
            );
            array_push($citiesArray, $cityArray);
        }

        return $citiesArray;
    }
}
