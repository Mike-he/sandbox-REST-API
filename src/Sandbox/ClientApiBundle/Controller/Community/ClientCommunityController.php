<?php

namespace Sandbox\ClientApiBundle\Controller\Community;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\Product\ProductController;
use Sandbox\ApiBundle\Entity\Room\RoomTypes;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ClientCommunityController.
 */
class ClientCommunityController extends ProductController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="range",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="coordinate range"
     * )
     *
     * @Annotations\QueryParam(
     *    name="room_type_tags",
     *    default=null,
     *    nullable=true,
     *    array=true,
     *    description="types tags of room"
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
     *    name="property_types",
     *    default=null,
     *    nullable=true,
     *    array=true,
     *    description="property types of building"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    default=null,
     *    nullable=true,
     *    description="city id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="district",
     *    default=null,
     *    nullable=true,
     *    description="district id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="buildings",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    description="building ids"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start",
     *    default=null,
     *    nullable=true,
     *    description="start time"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end",
     *    default=null,
     *    nullable=true,
     *    description="end time"
     * )
     *
     * @Annotations\QueryParam(
     *    name="min_allowed_people",
     *    default=null,
     *    nullable=true,
     *    description="min allowed people"
     * )
     *
     * @Annotations\QueryParam(
     *    name="max_allowed_people",
     *    default=null,
     *    nullable=true,
     *    description="max allowed people"
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
     *    name="type",
     *    default=null,
     *    nullable=true,
     *    description="room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="unit",
     *    default=null,
     *    nullable=true,
     *    description="product unit"
     * )
     *
     * @Annotations\QueryParam(
     *    name="include_company_id",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    description="include_company_id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="exclude_company_id",
     *    array=true,
     *    nullable=true,
     *    description="exclude_company_id"
     * )
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
     *    name="sales_recommend",
     *    array=false,
     *    default=false,
     *    nullable=true,
     *    strict=true,
     *    description="sales recommend"
     * )
     *
     * @Annotations\QueryParam(
     *    name="is_favorite",
     *    array=false,
     *    default=false,
     *    nullable=true,
     *    strict=true,
     *    description="my favorite"
     * )
     *
     * @Annotations\QueryParam(
     *    name="min_base_price",
     *    default=null,
     *    nullable=true,
     *    description="min base price"
     * )
     *
     * @Annotations\QueryParam(
     *    name="max_base_price",
     *    default=null,
     *    nullable=true,
     *    description="max base price"
     * )
     *
     * @Route("/communities")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCommunitiesSearchAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        // get params
        $cityId = $paramFetcher->get('city');
        $districtId = $paramFetcher->get('district');
        $lat = $paramFetcher->get('lat');
        $lng = $paramFetcher->get('lng');
        $range = $paramFetcher->get('range');
        $buildingTags = $paramFetcher->get('building_tags');
        $buildingServices = $paramFetcher->get('building_services');
        $propertyTypes = $paramFetcher->get('property_types');

        $start = $paramFetcher->get('start');
        $end = $paramFetcher->get('end');
        $type = $paramFetcher->get('type');
        $roomTypeTags = $paramFetcher->get('room_type_tags');
        $includeIds = $paramFetcher->get('include_company_id');
        $excludeIds = [9];
        $unit = $paramFetcher->get('unit');
        $isFavorite = (bool) $paramFetcher->get('is_favorite');
        $minAllowedPeople = $paramFetcher->get('min_allowed_people');
        $maxAllowedPeople = $paramFetcher->get('max_allowed_people');
        $minBasePrice = $paramFetcher->get('min_base_price');
        $maxBasePrice = $paramFetcher->get('max_base_price');

        $buildingIds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->findClientCommunities(
                $lat,
                $lng,
                $range,
                $excludeIds,
                $type,
                $buildingTags,
                $buildingServices,
                $propertyTypes,
                $cityId,
                $districtId
            );

        $communities = $this->handleCommunitiesData(
            $buildingIds,
            $userId,
            $minAllowedPeople,
            $maxAllowedPeople,
            $start,
            $end,
            $type,
            $includeIds,
            $excludeIds,
            $isFavorite,
            $minBasePrice,
            $maxBasePrice,
            $roomTypeTags,
            $unit
        );

        return new View($communities);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="range",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="coordinate range"
     * )
     *
     * @Annotations\QueryParam(
     *    name="room_type_tags",
     *    default=null,
     *    nullable=true,
     *    array=true,
     *    description="types tags of room"
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
     *    name="property_types",
     *    default=null,
     *    nullable=true,
     *    array=true,
     *    description="property types of building"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    default=null,
     *    nullable=true,
     *    description="city id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="district",
     *    default=null,
     *    nullable=true,
     *    description="district id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="buildings",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    description="building ids"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start",
     *    default=null,
     *    nullable=true,
     *    description="start time"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end",
     *    default=null,
     *    nullable=true,
     *    description="end time"
     * )
     *
     * @Annotations\QueryParam(
     *    name="min_allowed_people",
     *    default=null,
     *    nullable=true,
     *    description="min allowed people"
     * )
     *
     * @Annotations\QueryParam(
     *    name="max_allowed_people",
     *    default=null,
     *    nullable=true,
     *    description="max allowed people"
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
     *    name="type",
     *    default=null,
     *    nullable=true,
     *    description="room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="unit",
     *    default=null,
     *    nullable=true,
     *    description="product unit"
     * )
     *
     * @Annotations\QueryParam(
     *    name="include_company_id",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    description="include_company_id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="exclude_company_id",
     *    array=true,
     *    nullable=true,
     *    description="exclude_company_id"
     * )
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
     *    name="sales_recommend",
     *    array=false,
     *    default=false,
     *    nullable=true,
     *    strict=true,
     *    description="sales recommend"
     * )
     *
     * @Annotations\QueryParam(
     *    name="is_favorite",
     *    array=false,
     *    default=false,
     *    nullable=true,
     *    strict=true,
     *    description="my favorite"
     * )
     *
     * @Annotations\QueryParam(
     *    name="min_base_price",
     *    default=null,
     *    nullable=true,
     *    description="min base price"
     * )
     *
     * @Annotations\QueryParam(
     *    name="max_base_price",
     *    default=null,
     *    nullable=true,
     *    description="max base price"
     * )
     *
     * @Route("/communities/products/search")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getProductSearchAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        // get params
        $cityId = $paramFetcher->get('city');
        $districtId = $paramFetcher->get('district');
        $lat = $paramFetcher->get('lat');
        $lng = $paramFetcher->get('lng');
        $range = $paramFetcher->get('range');
        $buildingTags = $paramFetcher->get('building_tags');
        $buildingServices = $paramFetcher->get('building_services');
        $propertyTypes = $paramFetcher->get('property_types');

        $start = $paramFetcher->get('start');
        $end = $paramFetcher->get('end');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $type = $paramFetcher->get('type');
        $roomTypeTags = $paramFetcher->get('room_type_tags');
        $includeIds = $paramFetcher->get('include_company_id');
        $excludeIds = [9];
        $recommend = $paramFetcher->get('sales_recommend');
        $unit = $paramFetcher->get('unit');
        $isFavorite = (bool) $paramFetcher->get('is_favorite');
        $minAllowedPeople = $paramFetcher->get('min_allowed_people');
        $maxAllowedPeople = $paramFetcher->get('max_allowed_people');
        $minBasePrice = $paramFetcher->get('min_base_price');
        $maxBasePrice = $paramFetcher->get('max_base_price');

        $buildingIds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->findClientCommunities(
                $lat,
                $lng,
                $range,
                $excludeIds,
                $type,
                $buildingTags,
                $buildingServices,
                $propertyTypes,
                $cityId,
                $districtId
            );

        $productIds = $this->getProductIds(
            $userId,
            $buildingIds,
            $minAllowedPeople,
            $maxAllowedPeople,
            $start,
            $end,
            $type,
            $includeIds,
            $excludeIds,
            $isFavorite,
            $minBasePrice,
            $maxBasePrice,
            $roomTypeTags,
            $unit
        );

        if (is_null($type)) {
            $products = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->getAllProductsForCommunities(
                    $buildingIds,
                    $userId,
                    $limit,
                    $offset,
                    $includeIds,
                    $recommend
                );
        } else {
            $products = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->productSortByNearestBuilding(
                    $lat,
                    $lng,
                    $productIds,
                    $limit,
                    $offset
                );
        }

        foreach ($products as $product) {
            $this->generateProductInfo($product);
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($products);

        return $view;
    }

    /**
     * @param $communityIds
     * @param $userId
     * @param $minAllowedPeople
     * @param $maxAllowedPeople
     * @param $start
     * @param $end
     * @param $type
     * @param $includeIds
     * @param $excludeIds
     * @param $isFavorite
     * @param $minBasePrice
     * @param $maxBasePrice
     * @param $roomTypeTags
     * @param $unit
     *
     * @return array
     */
    public function handleCommunitiesData(
        $communityIds,
        $userId,
        $minAllowedPeople,
        $maxAllowedPeople,
        $start,
        $end,
        $type,
        $includeIds,
        $excludeIds,
        $isFavorite,
        $minBasePrice,
        $maxBasePrice,
        $roomTypeTags,
        $unit
    ) {
        $communitiesArray = [];

        foreach ($communityIds as $communityId) {
            $community = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($communityId);

            if (!is_null($community->getDistrict())) {
                $districtId = $community->getDistrictId();
            }

            $productIds = $this->getProductIds(
                $userId,
                [$communityId],
                $minAllowedPeople,
                $maxAllowedPeople,
                $start,
                $end,
                $type,
                $includeIds,
                $excludeIds,
                $isFavorite,
                $minBasePrice,
                $maxBasePrice,
                $roomTypeTags,
                $unit
            );

            $minPrice = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->getMinPriceByProducts(
                    $productIds
                );

            $communityArray = [
                'id' => $community->getId(),
                'name' => $community->getName(),
                'evaluation_star' => $community->getEvaluationStar(),
                'total_evaluation_number' => $community->getOrderEvaluationNumber() + $community->getBuildingEvaluationNumber(),
                'product' => [
                    'count' => count($productIds),
                    'min_base_price' => $minPrice['base_price'],
                    'min_unit_price' => $unitPrice = $this->get('translator')
                        ->trans(ProductOrderExport::TRANS_ROOM_UNIT.$minPrice['unit_price']),
                ],
                'district_id' => !is_null($community->getDistrictId()) ? $community->getDistrictId() : null,
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

    private function getProductIds(
        $userId,
        $buildingIds,
        $minAllowedPeople,
        $maxAllowedPeople,
        $start,
        $end,
        $type,
        $includeIds,
        $excludeIds,
        $isFavorite,
        $minBasePrice,
        $maxBasePrice,
        $roomTypeTags,
        $unit
    ) {
        $startTime = null;
        $endTime = null;
        $productIds = [];

        if (RoomTypes::TYPE_NAME_MEETING == $type ||
            RoomTypes::TYPE_NAME_OTHERS == $type
        ) {
            $startHour = null;
            $endHour = null;

            if (!is_null($start) && !empty($start)) {
                $startTime = new \DateTime($start);
                $startHour = $startTime->format('H:i:s');
            }

            if (!is_null($end) && !empty($end)) {
                $endTime = new \DateTime($end);
                $endHour = $endTime->format('H:i:s');
            }

            $productIds = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->getMeetingProductsForClientCommunities(
                    $userId,
                    $buildingIds,
                    $minAllowedPeople,
                    $maxAllowedPeople,
                    $startTime,
                    $endTime,
                    $startHour,
                    $endHour,
                    $type,
                    $includeIds,
                    $excludeIds,
                    $isFavorite,
                    $minBasePrice,
                    $maxBasePrice,
                    $roomTypeTags
                );
        } elseif (RoomTypes::TYPE_NAME_DESK == $type) {
            if (!is_null($start) && !is_null($end) && !empty($start) && !empty($end)) {
                $startTime = new \DateTime($start);
                $startTime->setTime(0, 0, 0);
                $endTime = new \DateTime($end);
                $endTime->setTime(23, 59, 59);
            }

            $productIds = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->getWorkspaceProductsForClientCommunities(
                    $userId,
                    $buildingIds,
                    $minAllowedPeople,
                    $maxAllowedPeople,
                    $startTime,
                    $endTime,
                    $type,
                    $includeIds,
                    $excludeIds,
                    $unit,
                    $isFavorite
                );
        } elseif (RoomTypes::TYPE_NAME_OFFICE == $type) {
            if (!is_null($start) && !is_null($end) && !empty($start) && !empty($end)) {
                $startTime = new \DateTime($start);
                $startTime->setTime(0, 0, 0);
                $endTime = new \DateTime($end);
                $endTime->setTime(23, 59, 59);
            }

            $productIds = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->getOfficeProductsForClientCommunities(
                    $userId,
                    $buildingIds,
                    $minAllowedPeople,
                    $maxAllowedPeople,
                    $startTime,
                    $endTime,
                    $includeIds,
                    $excludeIds,
                    $isFavorite
                );
        }

        return $productIds;
    }
}
