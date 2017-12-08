<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Community;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\Location\LocationController;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Constants\LocationConstants;

class ClientCommunityController extends LocationController
{
    /**
     * Get Commnue Community
     *
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="lat",
     *    array=false,
     *    default=0,
     *    nullable=true,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="coordinate lat"
     * )
     *
     * @Annotations\QueryParam(
     *    name="lng",
     *    array=false,
     *    default=0,
     *    nullable=true,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="coordinate lng"
     * )
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for the page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="start of the page"
     * )
     *
     * @Route("/communities")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAllCommnueCommunityAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        $lat = $paramFetcher->get('lat');
        $lng = $paramFetcher->get('lng');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $location = $this->baiduToGaode($lat, $lng);
        $lat = $location['lat'];
        $lng = $location['lon'];

        $communities = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getCommnueClientCommunityBuilding(
                $userId,
                $lat,
                $lng,
                null,
                $limit,
                $offset
            );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($communities);

        return $view;
    }

    /**
     * Get Commnue Hot Community
     *
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="lat",
     *    array=false,
     *    default=0,
     *    nullable=true,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="coordinate lat"
     * )
     *
     * @Annotations\QueryParam(
     *    name="lng",
     *    array=false,
     *    default=0,
     *    nullable=true,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="coordinate lng"
     * )
     *
     * @Route("/communities/hot")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getHotCommunityAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        $lat = $paramFetcher->get('lat');
        $lng = $paramFetcher->get('lng');

        $location = $this->baiduToGaode($lat, $lng);
        $lat = $location['lat'];
        $lng = $location['lon'];

        $hots = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\CommnueBuildingHot')
            ->getHotCommunities();
        $hotCounts = count($hots);

        $parameter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array(
                'key'=>Parameter::KEY_COMMNUE_BUILDING_HOT
            ));
        $maxHot = $parameter->getValue();

        if($hotCounts < $maxHot){
            $limit = $maxHot - $hotCounts;
            $extraHots = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->getExtraHotCommnueClientBuilding($hots, $limit);

            foreach($extraHots as $id){
                array_push($hots,$id);
            }
        }

        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getCommnueClientCommunityBuilding(
                $userId,
                $lat,
                $lng,
                $hots
            );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($buildings);

        return $view;
    }

    /**
     * @param $lat
     * @param $lng
     *
     * @return array
     */
    private function baiduToGaode(
        $lat,
        $lng
    ) {
        $x = $lng - 0.0065;
        $y = $lat - 0.006;

        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * LocationConstants::$pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * LocationConstants::$pi);

        $gg_lon = $z * cos($theta);
        $gg_lat = $z * sin($theta);

        return  array('lat' => $gg_lat, 'lon' => $gg_lon);
    }
}