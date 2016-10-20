<?php

namespace Sandbox\ApiBundle\Controller\Location;

use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingServices;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingTag;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
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

    public static $pi = 3.1415926535897932384626;

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
     * @Annotations\QueryParam(
     *    name="sales_company",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="id of sales admin"
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
        $permissionArray = $paramFetcher->get('permission');
        $salesCompanyId = $paramFetcher->get('sales_company');

        // filter by sales admin
        $cities = null;
        if (!is_null($salesCompanyId)) {
            $cities = $this->getRepo('Room\RoomCity')->getSalesRoomCityByCompanyId($salesCompanyId);
        } else {
            // get all cities
            $cities = $this->getRepo('Room\RoomCity')->findAll();
        }

        if (!is_null($user) && is_null($all)) {
            $userId = $user->getUserId();
            $clientId = $user->getClientId();

            $adminPlatform = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPlatform')
                ->findOneBy(array(
                    'userId' => $userId,
                    'clientId' => $clientId,
                ));

            // response for client
            if (is_null($adminPlatform)) {
                $cities = $this->getRepo('Room\RoomCity')->findAll();
                $citiesArray = $this->generateCitiesArray(
                    $cities
                );

                return new View($citiesArray);
            }

            // response for backend
            $platform = $adminPlatform['platform'];
            $salesCompanyId = $adminPlatform['sales_company_id'];

            $isSuperAdmin = $this->hasSuperAdminPosition(
                $this->getAdminId(),
                $platform,
                $salesCompanyId
            );
            // sales bundle
            if ($platform == AdminPermission::PERMISSION_PLATFORM_SALES) {
                // get cities by admin type
                if ($isSuperAdmin ||
                    in_array(AdminPermission::KEY_SALES_PLATFORM_ADMIN, $permissionArray)
                ) {
                    $cities = $this->getRepo('Room\RoomCity')->getSalesRoomCityByCompanyId($salesCompanyId);
                } else {
                    // get my building ids
                    $myBuildingIds = $this->generateLocationSalesBuildingIds(
                        $paramFetcher
                    );

                    $cities = $this->getRepo('Room\RoomCity')->getSalesRoomCityByBuilding($myBuildingIds);
                }
            }

            // shop bundle
            if ($platform == AdminPermission::PERMISSION_PLATFORM_SHOP) {
                // get cities by admin type
                if ($isSuperAdmin ||
                    in_array(AdminPermission::KEY_SHOP_PLATFORM_SHOP, $permissionArray) ||
                    in_array(AdminPermission::KEY_SHOP_PLATFORM_ADMIN, $permissionArray)
                ) {
                    $myBuildings = $this->getRepo('Room\RoomBuilding')->getBuildingsByCompany($salesCompanyId);
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
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    description="id of building"
     * )
     *
     * @Annotations\QueryParam(
     *    name="platform",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="platform"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sales_company",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="id of sales admin"
     * )
     *
     * @Annotations\QueryParam(
     *    name="exclude_company_id",
     *    array=true,
     *    nullable=true,
     *    description="exclude_company_id"
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

        $ids = $paramFetcher->get('id');
        $cityId = $paramFetcher->get('city');
        $permissionArray = $paramFetcher->get('permission');
        $platform = $paramFetcher->get('platform');
        $salesCompanyId = $paramFetcher->get('sales_company');
        $excludeIds = [9];

        if (RoomBuilding::PLATFORM_BACKEND_USER_BUILDING == $platform) {
            $excludeIds = null;
        }

        $visible = true;
        if (RoomBuilding::PLATFORM_SALES_USER_BUILDING == $platform) {
            $visible = null;
            $excludeIds = null;
        }

        // get all buildings
        $buildings = $this->getRepo('Room\RoomBuilding')->getLocationRoomBuildings(
            $cityId,
            $ids,
            $salesCompanyId,
            RoomBuilding::STATUS_ACCEPT,
            $visible,
            $excludeIds
        );

        $headers = array_change_key_case($_SERVER, CASE_LOWER);

        $userId = null;
        $clientId = null;
        if (!is_null($user)) {
            $userId = $user->getUserId();
            $clientId = $user->getClientId();
            $client = $this->getRepo('User\UserClient')->find($clientId);

            if (!is_null($client)) {
                $version = $client->getVersion();
                $name = strtoupper($client->getName());

                if (!is_null($version) && !empty($version)) {
                    $versionArray = explode('.', $version);

                    $this->checkForTransformWithToken(
                        $name,
                        $versionArray,
                        $buildings
                    );
                }
            }
        } elseif (array_key_exists('http_user_agent', $headers)) {
            $agent = $headers['http_user_agent'];

            $versionName = explode(' (', $agent);
            $versionNameArray = explode('/', $versionName[0]);

            if ($versionNameArray[0] == 'SandBox') {
                $versionArray = explode('.', $versionNameArray[1]);

                $this->checkForTransformWithoutToken($versionArray, $buildings);
            }
        }

        $adminPlatform = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPlatform')
            ->findOneBy(array(
                'userId' => $userId,
                'clientId' => $clientId,
            ));

        // response for client
        if (is_null($adminPlatform)) {
            $view = new View();
            $view->setSerializationContext(SerializationContext::create()->setGroups(['main']));
            $view->setData($buildings);

            return $view;
        }

        // response for backend
        if (!is_null($user) && empty($ids)) {
            $myPlatform = $adminPlatform->getPlatform();
            $salesCompanyId = $adminPlatform->getSalesCompanyId();

            $isSuperAdmin = $this->hasSuperAdminPosition(
                $this->getAdminId(),
                $myPlatform,
                $salesCompanyId
            );

            // sales bundle
            if ($myPlatform == AdminPermission::PERMISSION_PLATFORM_SALES) {
                // get buildings by admin type
                if ($isSuperAdmin ||
                    in_array(AdminPermission::KEY_SALES_PLATFORM_ADMIN, $permissionArray) ||
                    in_array(AdminPermission::KEY_SALES_BUILDING_USER, $permissionArray)
                ) {
                    $buildings = $this->getRepo('Room\RoomBuilding')->getLocationRoomBuildings(
                        $cityId,
                        null,
                        $salesCompanyId
                    );
                } else {
                    // get my building ids
                    $myBuildingIds = $this->generateLocationSalesBuildingIds(
                        $paramFetcher
                    );

                    $buildings = $this->getRepo('Room\RoomBuilding')->getLocationRoomBuildings(
                        $cityId,
                        $myBuildingIds
                    );
                }
            }

            // shop bundle
            if ($myPlatform == AdminPermission::PERMISSION_PLATFORM_SHOP) {
                // get buildings by admin type
                if ($isSuperAdmin ||
                    in_array(AdminPermission::KEY_SHOP_PLATFORM_SHOP, $permissionArray) ||
                    in_array(AdminPermission::KEY_SHOP_PLATFORM_ADMIN, $permissionArray)
                ) {
                    $buildings = $this->getRepo('Room\RoomBuilding')->getLocationRoomBuildings(
                        $cityId,
                        null,
                        $salesCompanyId
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
     * @Annotations\QueryParam(
     *    name="exclude_company_id",
     *    array=true,
     *    nullable=true,
     *    description="exclude_company_id"
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
        $excludeIds = [9];

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
            $range,
            $excludeIds
        );

        if ($addon == 'shop') {
            $count = count($buildings);

            for ($i = 0; $i < $count; ++$i) {
                $shops = $this->getRepo('Shop\Shop')->getShopByBuilding(
                    $buildings[$i]->getId(),
                    true,
                    true
                );

                if (empty($shops)) {
                    unset($buildings[$i]);
                } else {
                    $buildings[$i]->setShops($shops);
                }
            }

            $buildings = array_values($buildings);
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

        // set building room types according to present data
        $types = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomTypes')
            ->getPresentRoomTypes($building);

        foreach ($types as $type) {
            $typeText = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$type->getName());
            $type->setDescription($typeText);
        }
        $building->setBuildingRoomTypes($types);

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

        // set building services
        $services = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceBinding')
            ->findBy(array(
                'building' => $building,
            ));
        foreach ($services as $service) {
            if (is_null($service)) {
                continue;
            }

            $serviceText = $this->get('translator')->trans(
                RoomBuildingServices::TRANS_PREFIX.$service->getService()->getKey()
            );
            $service->getService()->setName($serviceText);
        }
        $building->setBuildingServices($services);

        // set building tags
        $tags = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingTagBinding')
            ->findBy(array(
                'building' => $building,
            ));
        foreach ($tags as $tag) {
            if (is_null($tag)) {
                continue;
            }

            $serviceText = $this->get('translator')->trans(
                RoomBuildingTag::TRANS_PREFIX.$tag->getTag()->getKey()
            );
            $tag->getTag()->setName($serviceText);
        }
        $building->setBuildingTags($tags);

        //set building rooms types
        $roomTypes = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingTypeBinding')
            ->findBy(array(
                'building' => $building,
            ));
        foreach ($roomTypes as $type) {
            $typeText = $this->container->get('translator')->trans($type->getType()->getName());
            $type->getType()->setDescription($typeText);
        }
        $building->setBuildingRoomTypes($roomTypes);

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
        if (is_null($cities) || empty($cities)) {
            return array();
        }

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

    /**
     * @param array $buildings
     */
    private function transformLocation(
        $buildings
    ) {
        foreach ($buildings as $building) {
            $lat = $building->getLat();
            $lng = $building->getLng();

            $locationArray = $this->gaodeToBaidu($lat, $lng);

            if (array_key_exists('lat', $locationArray) && array_key_exists('lon', $locationArray)) {
                $building->setLat($locationArray['lat']);
                $building->setLng($locationArray['lon']);
            }
        }
    }

    /**
     * 火星坐标系 (GCJ-02) 与百度坐标系 (BD-09) 的转换算法 将 GCJ-02 坐标转换成 BD-09 坐标.
     *
     * @param gg_lat
     * @param gg_lon
     */
    private function gaodeToBaidu($gg_lat, $gg_lon)
    {
        $x = $gg_lon;
        $y = $gg_lat;

        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * self::$pi);
        $theta = atan2($y, $x) + 0.000003 * cos($x * self::$pi);
        $bd_lon = $z * cos($theta) + 0.0065;
        $bd_lat = $z * sin($theta) + 0.006;

        return  array('lat' => $bd_lat, 'lon' => $bd_lon);
    }

    /**
     * @param $versionArray
     * @param $buildings
     */
    private function checkForTransformWithToken(
        $name,
        $versionArray,
        $buildings
    ) {
        $transform = false;

        if ((int) $versionArray[0] < RoomBuilding::LOCATION_TRANSFORM_VERSION_2) {
            $transform = true;
        } elseif ((int) $versionArray[0] == RoomBuilding::LOCATION_TRANSFORM_VERSION_2) {
            if ((int) $versionArray[1] < RoomBuilding::LOCATION_TRANSFORM_VERSION_2) {
                $transform = true;
            } elseif ((int) $versionArray[1] == RoomBuilding::LOCATION_TRANSFORM_VERSION_2) {
                $pos = strpos($name, 'ANDROID');

                if ($pos !== false) {
                    if ((int) $versionArray[2] < RoomBuilding::LOCATION_TRANSFORM_VERSION_3) {
                        $transform = true;
                    }
                } else {
                    if ((int) $versionArray[2] < RoomBuilding::LOCATION_TRANSFORM_VERSION_2) {
                        $transform = true;
                    }
                }
            }
        }

        if ($transform) {
            $this->transformLocation($buildings);
        }
    }

    /**
     * @param $versionArray
     * @param $buildings
     */
    private function checkForTransformWithoutToken(
        $versionArray,
        $buildings
    ) {
        $transform = false;

        if ((int) $versionArray[0] < RoomBuilding::LOCATION_TRANSFORM_VERSION_2) {
            $transform = true;
        } elseif ((int) $versionArray[0] == RoomBuilding::LOCATION_TRANSFORM_VERSION_2) {
            if ((int) $versionArray[1] < RoomBuilding::LOCATION_TRANSFORM_VERSION_2) {
                $transform = true;
            } elseif ((int) $versionArray[1] == RoomBuilding::LOCATION_TRANSFORM_VERSION_2) {
                if ((int) $versionArray[2] < RoomBuilding::LOCATION_TRANSFORM_VERSION_2) {
                    $transform = true;
                }
            }
        }

        if ($transform) {
            $this->transformLocation($buildings);
        }
    }
}
