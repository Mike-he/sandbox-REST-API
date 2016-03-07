<?php

namespace Sandbox\ApiBundle\Controller\Location;

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

        // generate cities array
        $citiesArray = $this->generateCitiesArray(
            $cities
        );

        return new View($citiesArray);
    }
    
    /**
     * @Get("/sales/cities")
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    default=null,
     *    nullable=true,
     *    description="city id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="admin",
     *    default=null,
     *    nullable=false,
     *    description="sales admin id"
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
     * @return View
     */
    public function getSalesCitiesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminId = $paramFetcher->get('admin');
        $permissionKeyArray = $paramFetcher->get('permission');
        $opLevel = $paramFetcher->get('op');

        if (is_null($adminId) || is_null($permissionKeyArray) || empty($permissionKeyArray)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $myBuildingIds = $this->getMySalesBuildingIds(
            $adminId,
            $permissionKeyArray,
            $opLevel
        );

        $cities = $this->getRepo('Room\RoomCity')->getSalesRoomCity($myBuildingIds);

        // generate cities array
        $citiesArray = $this->generateCitiesArray(
            $cities
        );

        return new View($citiesArray);
    }

    /**
     * @param $cities
     *
     * @return array
     */
    private function generateCitiesArray(
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
        $buildings = $this->getRepo('Room\RoomBuilding')->getLocationRoomBuildings($cityId);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['main']));
        $view->setData($buildings);

        return $view;
    }

    /**
     * @Get("/sales/buildings")
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    default=null,
     *    nullable=true,
     *    description="city id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="admin",
     *    default=null,
     *    nullable=false,
     *    description="sales admin id"
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
    public function getSalesBuildingsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $cityId = $paramFetcher->get('city');
        $adminId = $paramFetcher->get('admin');
        $permissionKeyArray = $paramFetcher->get('permission');
        $opLevel = $paramFetcher->get('op');

        if (is_null($adminId) || is_null($permissionKeyArray) || empty($permissionKeyArray)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $myBuildingIds = $this->getMySalesBuildingIds(
            $adminId,
            $permissionKeyArray,
            $opLevel
        );

        $buildings = $this->getRepo('Room\RoomBuilding')->getLocationRoomBuildings(
            $cityId,
            $myBuildingIds
        );

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
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="coordinate lat"
     * )
     *
     * @Annotations\QueryParam(
     *    name="lng",
     *    array=false,
     *    default=null,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="coordinate lng"
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
        $globals = $this->getGlobals();
        $range = $globals['nearby_range_km'];

        $buildings = $this->getRepo('Room\RoomBuilding')->findNearbyBuildings(
            $lat,
            $lng,
            $range
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['building_nearby']));
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

        return $building;
    }
}
