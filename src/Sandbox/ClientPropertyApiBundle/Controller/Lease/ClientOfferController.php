<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Lease;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\LeaseOffer;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomTypeTags;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;

class ClientOfferController extends SalesRestController
{
    use GenerateSerialNumberTrait;

    /**
     * Get Lease Offers.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *  @Annotations\QueryParam(
     *    name="building",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="source",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by lease source"
     * )
     *
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="applicant, room, number"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    default=null,
     *    nullable=true,
     *    description="create start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_end",
     *    default=null,
     *    nullable=true,
     *    description="create end date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment end date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="status of lease"
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
     * @Route("/offers")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function geOfferListsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $buildingId = $paramFetcher->get('building');
        $status = $paramFetcher->get('status');
        $source = $paramFetcher->get('source');

        // search keyword and query
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');

        // rent date filter
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        //get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_LEASE_OFFER,
            )
        );

        $defaultStatusSort = [
            LeaseOffer::LEASE_OFFER_STATUS_OFFER,
            LeaseOffer::LEASE_OFFER_STATUS_CONTRACT,
            LeaseOffer::LEASE_OFFER_STATUS_CLOSED,
        ];

        if (is_null($status) || empty($status)) {
            $statusSorts = $defaultStatusSort;
        } else {
            $statusSorts = array_intersect($defaultStatusSort, $status);
        }

        $ids = [];
        foreach ($statusSorts as $statusSort) {
            $offerIds = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseOffer')
                ->findOffersForPropertyClient(
                    $myBuildingIds,
                    $buildingId,
                    $statusSort,
                    $source,
                    $keyword,
                    $keywordSearch,
                    $createStart,
                    $createEnd,
                    $startDate,
                    $endDate
                );

            $ids = array_merge($ids, $offerIds);
        }

        $ids = array_unique($ids);

        $offers = $this->handleOfferData($ids, $limit, $offset);

        $view = new View();

        $view->setData($offers);

        return $view;
    }

    /**
     * Get offer info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/offers/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getOfferByIdAction(
        Request $request,
        $id
    ) {
        $offer = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\LeaseOffer')->find($id);
        $this->throwNotFoundIfNull($offer, self::NOT_FOUND_MESSAGE);

        $offer = $this->getMoreData($offer);

        $view = new View();
        $view->setData($offer);

        return $view;
    }

    private function handleOfferData(
        $offerIds,
        $limit,
        $offset
    ) {
        $ids = array();
        for ($i = $offset; $i < $offset + $limit; ++$i) {
            if (isset($offerIds[$i])) {
                $ids[] = $offerIds[$i];
            }
        }

        $result = [];
        foreach ($ids as $id) {
            $offer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseOffer')
                ->find($id);

            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($offer->getLesseeCustomer());

            $status = $this->get('translator')
                ->trans(LeaseConstants::TRANS_LEASE_OFFER_STATUS.$offer->getStatus());

            $building = '';
            if ($offer->getBuildingId()) {
                $building = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->find($offer->getBuildingId());
            }

            $roomName = '';
            $attachment = '';
            if ($offer->getProductId()) {
                $product = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Product\Product')
                    ->find($offer->getProductId());

                /** @var Room $room */
                $room = $product->getRoom();
                $roomName = $room->getName();

                $attachment = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
                    ->findAttachmentsByRoom($room->getId(), 1);
            }

            $result[] = [
                'id' => $id,
                'serial_number' => $offer->getSerialNumber(),
                'creation_date' => $offer->getCreationDate(),
                'status' => $status,
                'room_name' => $roomName,
                'attachment' => $attachment,
                'building_name' => $building ? $building->getName() : '',
                'start_date' => $offer->getStartDate(),
                'end_date' => $offer->getEndDate(),
                'customer' => array(
                    'id' => $offer->getLesseeCustomer(),
                    'name' => $customer ? $customer->getName() : '',
                    'avatar' => $customer ? $customer->getAvatar() : '',
                ),
            ];
        }

        return $result;
    }

    /**
     * @param LeaseOffer $offer
     *
     * @return mixed
     */
    private function getMoreData(
        $offer
    ) {
        if ($offer->getProductId()) {
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->find($offer->getProductId());

            /** @var Room $room */
            $room = $product->getRoom();

            $typeTagDescription = $this->get('translator')->trans(RoomTypeTags::TRANS_PREFIX.$room->getTypeTag());

            $rentSet = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\ProductRentSet')
                ->findOneBy(array('product' => $product));

            $attachment = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
                ->findAttachmentsByRoom($room->getId());

            $productData = array(
                'id' => $offer->getProductId(),
                'rent_set' => [
                    'id' => $rentSet->getId(),
                    'base_price' => (float) $rentSet->getBasePrice(),
                    'unit_price' => $rentSet->getUnitPrice(),
                    'earliest_rent_date' => $rentSet->getEarliestRentDate(),
                    'deposit' => $rentSet->getDeposit(),
                    'rental_info' => $rentSet->getRentalInfo(),
                    'status' => $rentSet->isStatus(),
                ],
                'room' => array(
                    'id' => $room->getId(),
                    'name' => $room->getName(),
                    'type' => $room->getType(),
                    'type_tag' => $room->getTypeTag(),
                    'type_tag_description' => $typeTagDescription,
                    'allowed_people' => $room->getAllowedPeople(),
                    'area' => $room->getArea(),
                    'attachment' => $attachment,
                ),
            );
            $offer->setProduct($productData);
        }

        if ($offer->getBuildingId()) {
            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($offer->getBuildingId());

            $buildingData = array(
                'id' => $offer->getBuildingId(),
                'name' => $building->getName(),
                'address' => $building->getAddress(),
            );
            $offer->setBuilding($buildingData);
        }

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($offer->getLesseeCustomer());

        $offer->setCustomer($customer);

        return $offer;
    }
}
