<?php

namespace Sandbox\ApiBundle\Controller\Location;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use JMS\Serializer\SerializationContext;

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
        } else {
            $buildings = $this->getRepo('Room\RoomBuilding')->findAll();
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

            return new View($floors);
        }
        $floors = $this->getRepo('Room\RoomFloor')->findAll();

        return new View($floors);
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
        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['main']));
        $view->setData($building);

        return $view;
    }
}
