<?php

namespace Sandbox\ClientApiBundle\Controller\Community;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ClientCommunityController.
 */
class ClientCommunityController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     *    name="range",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="coordinate range"
     * )
     *
     * @Annotations\QueryParam(
     *    name="room_type",
     *    default=null,
     *    nullable=true,
     *    array=false,
     *    description="types of room"
     * )
     *
     * @Route("/communities")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCommunitiesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        $lat = $paramFetcher->get('lat');
        $lng = $paramFetcher->get('lng');
        $range = $paramFetcher->get('range');
        $roomType = $paramFetcher->get('room_type');

        // exclude xiehe app data
        $excludeIds = [9];

        $communities = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->findClientCommunities(
                $lat,
                $lng,
                $range,
                $excludeIds,
                $roomType
            );

        $communities = $this->handleCommunitiesData($communities, $userId);

        return new View($communities);
    }

    /**
     * @param $communities
     * @param $userId
     *
     * @return array
     */
    public function handleCommunitiesData(
        $communities,
        $userId
    ) {
        $communitiesArray = [];

        foreach ($communities as $community) {
            $buildingId = $community->getId();

            $roomCount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->countRoomsWithProductByBuilding(
                    $buildingId,
                    $userId
                );

            $minLeasingSet = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->getMinProductLeasingSetByBuilding($buildingId);

            $unitPrice = $this->get('translator')
                ->trans(ProductOrderExport::TRANS_ROOM_UNIT.$minLeasingSet['min_unit_price']);

            $communityArray = [
                'id' => $community->getId(),
                'name' => $community->getName(),
                'room_count' => $roomCount,
                'lat' => $community->getLat(),
                'lng' => $community->getLng(),
                'min_base_price' => $minLeasingSet['min_base_price'],
                'min_unit_price' => $unitPrice,
                'evaluation_star' => $community->getEvaluationStar(),
                'total_evaluation_number' => $community->getOrderEvaluationNumber() + $community->getBuildingEvaluationNumber(),
            ];

            if (!is_null($userId)) {
                $favorite = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:User\UserFavorite')
                    ->findOneBy(array(
                        'userId' => $userId,
                        'object' => UserFavorite::OBJECT_BUILDING,
                        'objectId' => $community->getId(),
                    ));

                if ($favorite) {
                    $communityArray['is_favorite'] = true;
                }
            }

            array_push($communitiesArray, $communityArray);
        }

        return $communitiesArray;
    }
}
