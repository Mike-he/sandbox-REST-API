<?php

namespace Sandbox\ApiBundle\Controller\Location;

use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingServices;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingTag;
use Sandbox\ApiBundle\Traits\HandleCoordinateTrait;
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
use Sandbox\ApiBundle\Constants\LocationConstants;

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
    use HandleCoordinateTrait;

    /**
     * @Get("/cities")
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getCitiesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $language = $request->getPreferredLanguage(array('zh', 'en'));

        $cities = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomCity')
            ->getLocationCities();

        $length = count($cities);

        // sort city by building count
        for ($i = 1; $i < $length; ++$i) {
            for ($j = $length - 1; $j >= $i; --$j) {
                if ($cities[$j]['building_count'] > $cities[$j - 1]['building_count']) {
                    $tmp = $cities[$j];
                    $cities[$j] = $cities[$j - 1];
                    $cities[$j - 1] = $tmp;
                }
            }
        }

        $citiesArray = array();
        foreach ($cities as $city) {
            array_push($citiesArray, $city['city']);
        }

        // generate cities array
        $response = $this->generateCitiesArray(
            $citiesArray,
            $language
        );

        return new View($response);
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
        $ids = !empty($ids) ? $ids : null;
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
        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getLocationRoomBuildings(
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
     * @Annotations\QueryParam(
     *    name="lat",
     *    array=false,
     *    default=0,
     *    nullable=false,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="latitude"
     * )
     *
     * @Annotations\QueryParam(
     *    name="lng",
     *    array=false,
     *    default=0,
     *    nullable=false,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="longitude"
     * )
     *
     * @param ParamFetcherInterface $paramFetcher
     * @param Request               $request
     * @param $id
     *
     * @return View
     */
    public function getOneBuildingAction(
        ParamFetcherInterface $paramFetcher,
        Request $request,
        $id
    ) {
        $lat = $paramFetcher->get('lat');
        $lng = $paramFetcher->get('lng');

        $building = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->find($id);

        // set more information
        $this->setRoomBuildingMoreInformation($building);

        // set building room types according to present data
        $types = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomTypes')
            ->getPresentRoomTypes($building);

        // generate a url of web page to get all spaces
        $allSpaces = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array('key' => 'all_spaces'));

        $allSpacesUrl = $allSpaces->getValue().'buildingid='.$building->getId();
        $building->setAllSpacesUrl($allSpacesUrl);

        // generate a url of web page to quick book
        $quickBooking = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array('key' => 'quick_booking'));

        $tmpUrl = $quickBooking->getValue().'buildingid='.$building->getId().'&btype=';

        foreach ($types as $type) {
            $typeText = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$type->getName());
            $type->setDescription($typeText);

            $url = $tmpUrl.$type->getName();
            $type->setQuickBookingUrl($url);
        }
        $building->setBuildingRoomTypes($types);

        // generate a url of web page to weChat Share Url
        $mobileUrl = $this->container->getParameter('room_mobile_url');
        $wxShareUrl = $mobileUrl.'/building?id='.$building->getId();
        $building->setWxShareUrl($wxShareUrl);

        $totalEvaluationNumber = $building->getOrderEvaluationNumber() + $building->getBuildingEvaluationNumber();
        $building->setTotalEvaluationNumber($totalEvaluationNumber);

        // 0 means user disable location
        if ($lat != 0 && $lng != 0) {
            $distance = $this->calculateDistanceBetweenCoordinates(
                $lat,
                $lng,
                $building->getLat(),
                $building->getLng()
            );
            $building->setDistance($distance);
        }

        $userId = $this->getUser() ? $this->getUserId() : null;
        $roomWithProductNumber = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->countRoomsWithProductByBuilding(
                $building->getId(),
                $userId
            );
        $building->setRoomWithProductNumber((int) $roomWithProductNumber);

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
        if ($floors) {
            $building->setFloors($floors);
        }

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

        // set country id & province id
        $city = $building->getCity();
        $province = $city->getParent();

        if (!is_null($province)) {
            $building->setProvince(array(
                'id' => $province->getId(),
                'name' => $province->getName(),
            ));

            $country = $province->getParent();
            if (!is_null($country)) {
                $building->setCountry(array(
                    'id' => $country->getId(),
                    'name' => $country->getName(),
                ));
            }
        }

        $members = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
            ->findBy([
                'buildingId' => $building->getId(),
            ]);
        $building->setCustomerServices($members);

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
     * @param $language
     *
     * @return array
     */
    protected function generateCitiesArray(
        $cities,
        $language = null
    ) {
        if (is_null($cities) || empty($cities)) {
            return array();
        }

        $citiesArray = array();
        foreach ($cities as $city) {
            switch ($language) {
                case 'en':
                    $name = $city->getEnName();
                    break;
                default:
                    $name = $city->getName();
                    break;
            }

            $cityArray = array(
                'id' => $city->getId(),
                'name' => $name,
                'key' => $city->getKey(),
                'capital' => $city->isCapital(),
                'latitude' => $city->getLat(),
                'longitude' => $city->getLng(),
                'code' => $city->getCode(),
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
            if (is_array($building)) {
                $lat = $building['lat'];
                $lng = $building['lng'];
            } else {
                $lat = $building->getLat();
                $lng = $building->getLng();
            }

            $locationArray = $this->gaodeToBaidu($lat, $lng);

            if (array_key_exists('lat', $locationArray) && array_key_exists('lon', $locationArray)) {
                if (is_array($building)) {
                    $building['lat'] = $locationArray['lat'];
                    $building['lng'] = $locationArray['lon'];
                } else {
                    $building->setLat($locationArray['lat']);
                    $building->setLng($locationArray['lon']);
                }
            }
        }
    }

    /**
     * 火星坐标系 (GCJ-02) 与百度坐标系 (BD-09) 的转换算法 将 GCJ-02 坐标转换成 BD-09 坐标.
     *
     * @param gg_lat
     * @param gg_lon
     *
     * @return array
     */
    private function gaodeToBaidu($gg_lat, $gg_lon)
    {
        $x = $gg_lon;
        $y = $gg_lat;

        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * LocationConstants::$pi);
        $theta = atan2($y, $x) + 0.000003 * cos($x * LocationConstants::$pi);
        $bd_lon = $z * cos($theta) + 0.0065;
        $bd_lat = $z * sin($theta) + 0.006;

        return  array('lat' => $bd_lat, 'lon' => $bd_lon);
    }

    /**
     * @param $name
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

    /**
     * @Get("/communities/search")
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    default=null,
     *    nullable=true,
     *    description="id of city"
     * )
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    default=null,
     *    nullable=true,
     *    description="query text"
     * )
     *
     * @Annotations\QueryParam(
     *    name="room_types",
     *    default=null,
     *    nullable=true,
     *    array=true,
     *    description="types of room"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort_by",
     *    default="smart",
     *    nullable=true,
     *    description="smart sort"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building_tags",
     *    default=null,
     *    nullable=true,
     *    array=true,
     *    description="tags of building"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building_services",
     *    default=null,
     *    nullable=true,
     *    array=true,
     *    description="services of building"
     * )
     *
     * @Annotations\QueryParam(
     *    name="lat",
     *    array=false,
     *    default="31.216",
     *    nullable=false,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="latitude"
     * )
     *
     * @Annotations\QueryParam(
     *    name="lng",
     *    array=false,
     *    default="121.632",
     *    nullable=false,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="longitude"
     * )
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function searchBuildingsAction(
        ParamFetcherInterface $paramFetcher
    ) {
        $cityId = $paramFetcher->get('city');
        $queryText = $paramFetcher->get('query');
        $sortBy = $paramFetcher->get('sort_by');
        $roomTypes = $paramFetcher->get('room_types');
        $buildingTags = $paramFetcher->get('building_tags');
        $buildingServices = $paramFetcher->get('building_services');
        $lng = $paramFetcher->get('lng');
        $lat = $paramFetcher->get('lat');

        // get all buildings
        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->searchBuildings(
                $cityId,
                $queryText,
                $roomTypes,
                $sortBy,
                $buildingTags,
                $buildingServices,
                $lng,
                $lat,
                $excludeIds = [9] // 9 is the company id of xiehe
            );

        $buildings = $this->handleSearchBuildingsData($buildings);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['main']));
        $view->setData($buildings);

        return $view;
    }

    protected function handleSearchBuildingsData(
        $buildings
    ) {
        foreach ($buildings as &$building) {
            $building['distance'] = round($building['distance'], 3);

            $attachments = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuildingAttachment')
                ->findRoomBuildingAttachmentByBuildingId($building['id']);

            if (!empty($attachments)) {
                $building['cover'] = $attachments[0]['content'];
            }

            $tags = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuildingTagBinding')
                ->findRoomBuildingTagsByBuildingId($building['id']);

            if (!empty($tags)) {
                $building['building_tags'] = $tags;
            }

            if (!is_null($building['district_id'])) {
                $district = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomCity')
                    ->find($building['district_id']);

                $building['location'] = $district->getName();
                unset($building['district_id']);
            }

            $roomCount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->countRoomsWithProductByBuilding(
                    $building['id'],
                    null
                );

            $building['room_count'] = $roomCount;
        }

        return $buildings;
    }

    /**
     * @Get("/communities/filter")
     */
    public function getFilterAction()
    {
        $filter = array();

        // room types
        $types = $this->findAllAndTranslateResources(
            'Room\RoomTypes',
            ProductOrderExport::TRANS_ROOM_TYPE
        );

        $filter[] = $this->buildFilter(
            LocationConstants::FILTER_SPACE_TYPE,
            [
                [
                    'type' => LocationConstants::TAG,
                    'name' => LocationConstants::SUB_FILTER_SPACE_TYPE,
                    'queryParamKey' => LocationConstants::QUERY_ROOM_TYPES,
                    'filterAllTitle' => $this->get('translator')->trans(LocationConstants::TRANS_BUILDING_FILTER_ALL_TITLE),
                    'items' => $types,
                ],
            ]
        );

        // sort by
        $sorts = array();
        foreach (LocationConstants::$plainTextSortKeys as &$sortKey) {
            $sort['name'] = $this->get('translator')->trans(LocationConstants::TRANS_BUILDING_SORT.$sortKey);
            $sort['key'] = $sortKey;

            // set default option
            $sort['selected'] = false;
            if ($sort['key'] == LocationConstants::SORT_BY_DEFAULT_KEY) {
                $sort['selected'] = true;
            }

            $sorts[] = $sort;
        }

        $filter[] = $this->buildFilter(
            LocationConstants::FILTER_SORT_BY,
            [
                [
                    'type' => LocationConstants::RADIO,
                    'name' => LocationConstants::SUB_FILTER_SORT_BY,
                    'queryParamKey' => LocationConstants::QUERY_SORT_BY,
                    'items' => $sorts,
                ],
            ]
        );

        // filter
        $buildingTagsItems = $this->findAllAndTranslateResources(
            'Room\RoomBuildingTag',
            LocationConstants::TRANS_BUILDING_TAG
        );

        $buildingServicesItems = $this->findAllAndTranslateResources(
            'Room\RoomBuildingServices',
            LocationConstants::TRANS_BUILDING_SERVICE
        );

        $configs[] = [
            'type' => LocationConstants::TAG,
            'name' => LocationConstants::SUB_FILTER_TAG,
            'queryParamKey' => LocationConstants::QUERY_BUILDING_TAGS,
            'filterAllTitle' => $this->get('translator')->trans(LocationConstants::TRANS_BUILDING_FILTER_ALL_TITLE),
            'items' => $buildingTagsItems,
        ];

        $configs[] = [
            'type' => LocationConstants::TAG,
            'name' => LocationConstants::SUB_FILTER_CONFIGURE,
            'queryParamKey' => LocationConstants::QUERY_BUILDING_SERVICES,
            'filterAllTitle' => null,
            'items' => $buildingServicesItems,
        ];

        $filter[] = $this->buildFilter(
            LocationConstants::FILTER_FILTER,
            $configs
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['build_filter']));
        $view->setData($filter);

        return $view;
    }

    /**
     * @param $entityName
     * @param $trans
     *
     * @return array
     */
    private function findAllAndTranslateResources(
        $entityName,
        $trans
    ) {
        $resources = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:'.$entityName)
            ->findAll();

        foreach ($resources as &$resource) {
            if ($entityName == 'Room\RoomTypes') {
                $resourceKey = $this->get('translator')->trans($trans.$resource->getName());
            } else {
                $resourceKey = $this->get('translator')->trans($trans.$resource->getKey());
            }

            $resource->setName($resourceKey);
        }

        return $resources;
    }

    /**
     * @param $name
     * @param $configs
     *
     * @return array
     */
    private function buildFilter(
        $name,
        $configs
    ) {
        $buildFilter['name'] = $this->get('translator')
            ->trans(LocationConstants::TRANS_BUILDING_FILTER.$name);

        foreach ($configs as &$config) {
            $config['name'] = $this->get('translator')
                ->trans(LocationConstants::TRANS_BUILDING_FILTER.$config['name']);
        }

        $buildFilter['filters'] = $configs;

        return $buildFilter;
    }

    /**
     * @Get("/communities/recommend_search")
     *
     * @return array
     */
    public function recommendSearchAction()
    {
        return [
            'hots' => [
                '创合',
                '孵化器',
                '展想',
                '社区',
                '会议室',
                '独享办公桌',
                '静安',
                '杨浦',
                '办公室',
            ],
        ];
    }
}
