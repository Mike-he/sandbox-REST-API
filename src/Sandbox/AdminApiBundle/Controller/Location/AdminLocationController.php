<?php

namespace Sandbox\AdminApiBundle\Controller\Location;

use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;

/**
 *  Admin Location Controller.
 *
 * @category Sandbox
 *
 * @author   Feng Li <feng.li@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminLocationController extends AdminRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Get("/location/cities")
     *
     * @return View
     */
    public function getCitiesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $cities = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findBy(array(
                'level' => RoomCity::LEVEL_CITY,
            ));

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
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="id of sales admin"
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
        $companyId = $paramFetcher->get('company');

        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getLocationRoomBuildings(
                $cityId,
                null,
                $companyId,
                RoomBuilding::STATUS_ACCEPT,
                true
            );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['main']));
        $view->setData($buildings);

        return $view;
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
