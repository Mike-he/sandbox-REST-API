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

class ClientCommunityHotController extends LocationController
{
    /**
     * @param Request $request
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
     * @Route("/communities")
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

        $hotCounts = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\CommnueBuildingHot')
                    ->getHotCommunityCounts();
        $parameter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array(
                'key'=>Parameter::KEY_COMMNUE_BUILDING_HOT
            ));

        $builingIds = [];
        $results = [];
        foreach ($hots as $hot){
            $id = $hot->getBuildingId();

            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($id);
            $results[] = $this->setBuildingInfo($building,$lat,$lng);
            $builingIds[] = $id;
        }
        $maxHot = $parameter->getValue();
        if($hotCounts < $maxHot){
            $limit = $maxHot - $hotCounts;
            $communities = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->getCommnueClientBuilding($builingIds, $limit);
            foreach($communities as $community){
                $results[] = $this->setBuildingInfo($community,$lat,$lng);
            }
        }

        $sort = [];
        foreach ($results as  $result){
            if(!is_null($userId)){
                foreach ($results as  $result){
                     $sort[] = $result['room_num'];
                }
                array_multisort($sort, SORT_DESC, $results);
            }else{
                foreach ($results as  $result){
                    $sort[] = $result['distance'];
                }
                array_multisort($sort, SORT_ASC, $results);
            }
        }


        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($results);

        return $view;
    }

    /**
     * @param RoomBuilding $building
     * @param $lat
     * @param $lng
     *
     * @return array
     */
    private function setBuildingInfo(
        $building,
        $lat,
        $lng
    ) {
        $data = [];
        $data['id'] = $building->getId();
        $data['name'] = $building->getName();
        $data['room_num'] = $building->getRoomCounts();
        $data['avatar'] = $building->getAvatar();
        $data['evaluation_star'] = $building->getEvaluationStar();
        $data['total_evaluation_number'] = $building->getTotalEvaluationNumber();
        $data['address'] = $building->getAddress();

        $buildingLat = $building->getLat();
        $buildingLng = $building->getLng();

        $distance = $this->getdistance($buildingLat,$buildingLng,$lat,$lng);

        $data['distance'] = $distance;

        return $data;
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

    private function getdistance(
        $lng1,
        $lat1,
        $lng2,
        $lat2,
        $decimal=2
    ) {
        //将角度转为狐度
        $radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度
        $radLat2=deg2rad($lat2);
        $radLng1=deg2rad($lng1);
        $radLng2=deg2rad($lng2);
        $a=$radLat1-$radLat2;
        $b=$radLng1-$radLng2;
        $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
        $s /= 1000;

        return round($s, $decimal);
    }
}