<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Lease;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminRemark;
use Sandbox\ApiBundle\Entity\Admin\AdminStatusLog;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomTypeTags;
use Sandbox\ApiBundle\Form\Lease\LeaseCluePostType;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;

class ClientClueController extends SalesRestController
{
    use GenerateSerialNumberTrait;

    /**
     * Get Lease Clues.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
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
     *    name="status",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="status of lease"
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
     *    name="cycle_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by cycle"
     * )
     *
     * @Annotations\QueryParam(
     *    name="cycle_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by cycle"
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
     * @Route("/clues")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getCluesListsAction(
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

        // rent date filter
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        $cycleStart = $paramFetcher->get('cycle_start');
        $cycleEnd = $paramFetcher->get('cycle_end');

        // creation date filter
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');

        //get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_LEASE_CLUE,
            )
        );

        $defaultStatusSort = [
            LeaseClue::LEASE_CLUE_STATUS_CLUE,
            LeaseClue::LEASE_CLUE_STATUS_OFFER,
            LeaseClue::LEASE_CLUE_STATUS_CONTRACT,
            LeaseClue::LEASE_CLUE_STATUS_CLOSED,
        ];

        if (is_null($status) || empty($status)) {
            $statusSorts = $defaultStatusSort;
        } else {
            $statusSorts = array_intersect($defaultStatusSort, $status);
        }
        $ids = [];
        foreach ($statusSorts as $statusSort) {
            $leaseIds = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseClue')
                ->findCluesForPropertyClient(
                    $myBuildingIds,
                    $buildingId,
                    $statusSort,
                    $keyword,
                    $keywordSearch,
                    $startDate,
                    $endDate,
                    $source,
                    $cycleStart,
                    $cycleEnd,
                    $createStart,
                    $createEnd
                );

            $ids = array_merge($ids, $leaseIds);
        }

        $ids = array_unique($ids);

        $clues = $this->handleClueData($ids, $limit, $offset);

        $view = new View();

        $view->setData($clues);

        return $view;
    }

    /**
     * Get clue info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/clues/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getClueByIdAction(
        Request $request,
        $id
    ) {
        $clue = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\LeaseClue')->find($id);
        $this->throwNotFoundIfNull($clue, self::NOT_FOUND_MESSAGE);

        $clue = $this->getMoreData($clue);

        $view = new View();
        $view->setData($clue);

        return $view;
    }

    /**
     * Create a new lease clue.
     *
     * @param $request
     *
     * @Route("/clues")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postLeaseCluesAction(
        Request $request
    ) {
        $clue = new LeaseClue();
        $form = $this->createForm(new LeaseCluePostType(), $clue);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->saveLeaseClue(
            $clue,
            'POST'
        );
    }

    /**
     * Update a lease clue.
     *
     * @param $request
     *
     * @Route("/clues/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putLeaseCluesAction(
        Request $request,
        $id
    ) {
        $clue = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseClue')
            ->findOneBy(array('id' => $id, 'status' => LeaseClue::LEASE_CLUE_STATUS_CLUE));
        $this->throwNotFoundIfNull($clue, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new LeaseCluePostType(),
            $clue,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->saveLeaseClue(
            $clue,
            'PUT'
        );
    }

    /**
     * @param LeaseClue $clue
     * @param $method
     *
     * @return View
     */
    private function saveLeaseClue(
        $clue,
        $method
    ) {
        $em = $this->getDoctrine()->getManager();
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $platform = $adminPlatform['platform'];

        $status = $clue->getStatus();
        if (LeaseClue::LEASE_CLUE_STATUS_CLUE != $status) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $customerId = $clue->getLesseeCustomer();
        if (is_null($customerId)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        } else {
            $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')->find($customerId);
            $this->throwNotFoundIfNull($customer, self::NOT_FOUND_MESSAGE);
        }

        $buildingId = $clue->getBuildingId();
        if ($buildingId) {
            $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);
            $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);
        }

        $productId = $clue->getProductId();
        if ($productId) {
            $product = $em->getRepository('SandboxApiBundle:Product\Product')->find($productId);
            $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);
        }

        $startDate = $clue->getStartDate();
        if ($startDate) {
            $clue->setStartDate(new \DateTime($startDate));
        }

        $endDate = $clue->getEndDate();
        if ($endDate) {
            $clue->setEndDate(new \DateTime($endDate));
        }

        if ('POST' == $method) {
            $serialNumber = $this->generateSerialNumber(LeaseClue::LEASE_CLUE_LETTER_HEAD);
            $clue->setSerialNumber($serialNumber);
            $clue->setCompanyId($salesCompanyId);

            $message = '创建线索';
        } else {
            $message = '更新线索';
        }

        $em->persist($clue);
        $em->flush();

        $this->get('sandbox_api.admin_remark')->autoRemark(
            $this->getAdminId(),
            $platform,
            $salesCompanyId,
            $message,
            AdminRemark::OBJECT_LEASE_CLUE,
            $clue->getId()
        );

        if ('POST' == $method) {
            $logMessage = '创建线索';
            $this->get('sandbox_api.admin_status_log')->autoLog(
                $this->getAdminId(),
                $status,
                $logMessage,
                AdminStatusLog::OBJECT_LEASE_CLUE,
                $clue->getId()
            );

            $response = array(
                'id' => $clue->getId(),
            );

            return new View($response, 201);
        }
    }

    /**
     * @param LeaseClue $clue
     *
     * @return mixed
     */
    private function getMoreData(
        $clue
    ) {
        if ($clue->getProductId()) {
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->find($clue->getProductId());

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
                'id' => $clue->getProductId(),
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
            $clue->setProduct($productData);
        }

        if ($clue->getBuildingId()) {
            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($clue->getBuildingId());

            $buildingData = array(
                'id' => $clue->getBuildingId(),
                'name' => $building->getName(),
                'address' => $building->getAddress(),
            );
            $clue->setBuilding($buildingData);
        }

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($clue->getLesseeCustomer());

        $clue->setCustomer($customer);

        if ($clue->getProductAppointmentId()) {
            $productAppointment = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\ProductAppointment')
                ->find($clue->getProductAppointmentId());

            $productAppointmentData = array(
                'id' => $clue->getProductAppointmentId(),
                'user_id' => $productAppointment->getUserId(),
            );
            $clue->setProductAppointment($productAppointmentData);
        }

        return $clue;
    }

    /**
     * @param $clueIds
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    private function handleClueData(
        $clueIds,
        $limit,
        $offset
    ) {
        $ids = array();
        for ($i = $offset; $i < $offset + $limit; ++$i) {
            if (isset($clueIds[$i])) {
                $ids[] = $clueIds[$i];
            }
        }

        $result = [];
        foreach ($ids as $id) {
            $clue = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseClue')
                ->find($id);

            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($clue->getLesseeCustomer());

            $status = $this->get('translator')
                ->trans(LeaseConstants::TRANS_LEASE_CLUE_STATUS.$clue->getStatus());

            $building = '';
            if ($clue->getBuildingId()) {
                $building = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->find($clue->getBuildingId());
            }

            $roomName = '';
            $attachment = '';
            if ($clue->getProductId()) {
                $product = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Product\Product')
                    ->find($clue->getProductId());

                /** @var Room $room */
                $room = $product->getRoom();
                $roomName = $room->getName();

                $attachment = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
                    ->findAttachmentsByRoom($room->getId(), 1);
            }

            $result[] = [
                'id' => $id,
                'serial_number' => $clue->getSerialNumber(),
                'creation_date' => $clue->getCreationDate(),
                'status' => $status,
                'room_name' => $roomName,
                'attachment' => $attachment,
                'building_name' => $building ? $building->getName() : '',
                'start_date' => $clue->getStartDate(),
                'cycle' => $clue->getCycle(),
                'source' => $clue->getProductAppointmentId() ? '客户申请' : '管理员创建',
                'customer' => array(
                    'id' => $clue->getLesseeCustomer(),
                    'name' => $customer ? $customer->getName() : '',
                    'avatar' => $customer ? $customer->getAvatar() : '',
                ),
            ];
        }

        return $result;
    }
}
