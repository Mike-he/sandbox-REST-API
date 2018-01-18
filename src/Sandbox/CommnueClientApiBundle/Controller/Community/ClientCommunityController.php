<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Community;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\Location\LocationController;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sandbox\ApiBundle\Constants\LocationConstants;

class ClientCommunityController extends LocationController
{
    /**
     * Get Commnue Community.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="lat",
     *    array=false,
     *    default="31.216",
     *    nullable=true,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="coordinate lat"
     * )
     *
     * @Annotations\QueryParam(
     *    name="lng",
     *    array=false,
     *    default="121.632",
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
     * @Annotations\QueryParam(
     *    name="province",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    description="provinceId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    description="cityId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="district",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    description="districtId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="sort string"
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

//        $lat = $paramFetcher->get('lat');
//        $lng = $paramFetcher->get('lng');
//        $location = $this->baiduToGaode($lat, $lng);
//        $lat = $location['lat'];
//        $lng = $location['lon'];

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $province = $paramFetcher->get('province');
        $city = $paramFetcher->get('city');
        $district = $paramFetcher->get('district');
        $sort = $paramFetcher->get('sort');

        $communities = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getCommnueClientAllCommunityBuilding(
                $province,
                $city,
                $district,
                $sort,
                $limit,
                $offset
            );

        $communities = $this->handleCommunityInfo($communities, $userId);

        $view = new View();
        $view->setData($communities);

        return $view;
    }

    /**
     * Get Commnue Hot Community.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="lat",
     *    array=false,
     *    default="31.216",
     *    nullable=true,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="coordinate lat"
     * )
     *
     * @Annotations\QueryParam(
     *    name="lng",
     *    array=false,
     *    default="121.632",
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
                'key' => Parameter::KEY_COMMNUE_BUILDING_HOT,
            ));
        $maxHot = $parameter->getValue();

        if ($hotCounts < $maxHot) {
            $limit = $maxHot - $hotCounts;
            $extraHots = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->getExtraHotCommnueClientBuilding($hots, $limit);

            foreach ($extraHots as $id) {
                array_push($hots, $id);
            }
        }

        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getCommnueClientCommunityBuilding(
                $lat,
                $lng,
                $hots
            );

        $buildings = $this->handleCommunityInfo($buildings, $userId);

        $view = new View();
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

    /**
     * @param $communities
     * @param $userId
     *
     * @return mixed
     */
    private function handleCommunityInfo(
        $communities,
        $userId
    ) {
        $counts = [];
        foreach ($communities as &$community) {
            $id = $community['id'];
            $buildingAttachment = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuildingAttachment')
                ->findOneBy(array('buildingId' => $id));
            $community['attachment'] = $buildingAttachment ? $buildingAttachment->getContent() : '';

            $number = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->countRoomsWithProductByBuilding(
                    $id
                );
            $community['room_number'] = (int) $number;
            $counts[] = $community['room_number'];

            $lat = $community['lat'];
            $lng = $community['lng'];
            $locationArray = $this->gaodeToBaidu($lat, $lng);
            $community['lat'] = $locationArray['lat'];
            $community['lng'] = $locationArray['lon'];
        }

        if (!is_null($userId)) {
            array_multisort($counts, SORT_DESC, $communities);
        }

        return $communities;
    }

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
}
