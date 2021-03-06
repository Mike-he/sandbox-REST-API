<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminRemark;
use Sandbox\ApiBundle\Entity\Admin\AdminStatusLog;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;
use Sandbox\ApiBundle\Entity\Lease\LeaseOffer;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomTypeTags;
use Sandbox\ApiBundle\Entity\User\EnterpriseCustomerContacts;
use Sandbox\ApiBundle\Form\Lease\LeaseOfferType;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;

class AdminLeaseOfferController extends SalesRestController
{
    use GenerateSerialNumberTrait;

    /**
     * Get Lease Offers.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by building id"
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
     *    name="rent_filter",
     *    default=null,
     *    nullable=true,
     *    description="rent time filter keywords"
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
     *    array=false,
     *    default="",
     *    nullable=true,
     *    strict=true,
     *    description="status of lease"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort_column",
     *    default=null,
     *    nullable=true,
     *    description="sort column"
     * )
     *
     * @Annotations\QueryParam(
     *    name="direction",
     *    default=null,
     *    nullable=true,
     *    description="sort direction"
     * )
     *
     * @Route("/lease/offers")
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
        // check user permission
        $this->checkAdminLeaseOfferPermission(AdminPermission::OP_LEVEL_VIEW);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

        $buildingId = $paramFetcher->get('building');
        $status = $paramFetcher->get('status');

        // search keyword and query
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');

        // rent date filter
        $rentFilter = $paramFetcher->get('rent_filter');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        //sort
        $sortColumn = $paramFetcher->get('sort_column');
        $direction = $paramFetcher->get('direction');

        //get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_LEASE_OFFER,
            )
        );

        $offers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseOffer')
            ->findOffers(
                $myBuildingIds,
                $buildingId,
                $status,
                $keyword,
                $keywordSearch,
                $createStart,
                $createEnd,
                $rentFilter,
                $startDate,
                $endDate,
                $limit,
                $offset,
                $sortColumn,
                $direction
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseOffer')
            ->countOffers(
                $myBuildingIds,
                $buildingId,
                $status,
                $keyword,
                $keywordSearch,
                $createStart,
                $createEnd,
                $rentFilter,
                $startDate,
                $endDate
            );

        foreach ($offers as $offer) {
            $this->handleOfferData($offer);
        }

        $view = new View();

        $view->setData(
            array(
                'current_page_number' => (int) $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $offers,
                'total_count' => (int) $count,
            ));

        return $view;
    }

    /**
     * Get offer info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/lease/offers/{id}")
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
        // check user permission
        $this->checkAdminLeaseOfferPermission(AdminPermission::OP_LEVEL_VIEW);

        $offer = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\LeaseOffer')->find($id);
        $this->throwNotFoundIfNull($offer, self::NOT_FOUND_MESSAGE);

        $offer = $this->handleOfferData($offer);

        $view = new View();
        $view->setData($offer);

        return $view;
    }

    /**
     * Create a new lease clue.
     *
     * @param $request
     *
     * @Route("/lease/offers")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postLeaseOffersAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminLeaseOfferPermission(AdminPermission::OP_LEVEL_EDIT);

        $offer = new LeaseOffer();
        $form = $this->createForm(new LeaseOfferType(), $offer);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $leaseRentTypeIds = $form['rent_type_ids']->getData();

        return $this->saveLeaseOffer(
            $offer,
            $leaseRentTypeIds,
            'POST'
        );
    }

    /**
     * Update a lease clue.
     *
     * @param $request
     *
     * @Route("/lease/offers/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putLeaseOffersAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeaseOfferPermission(AdminPermission::OP_LEVEL_EDIT);

        $offer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseOffer')
            ->findOneBy(array('id' => $id, 'status' => LeaseOffer::LEASE_OFFER_STATUS_OFFER));
        $this->throwNotFoundIfNull($offer, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new LeaseOfferType(),
            $offer,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $leaseRentTypeIds = $form['rent_type_ids']->getData();

        return $this->saveLeaseOffer(
            $offer,
            $leaseRentTypeIds,
            'PUT'
        );
    }

    /**
     * Patch Lease Offer Status.
     *
     * @param $request
     * @param $id
     *
     * @Route("/lease/offers/{id}")
     * @Method({"PATCH"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function patchStatusAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeaseOfferPermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        $payload = json_decode($request->getContent(), true);

        $offer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseOffer')
            ->findOneBy(array('id' => $id, 'status' => LeaseOffer::LEASE_OFFER_STATUS_OFFER));
        $this->throwNotFoundIfNull($offer, self::NOT_FOUND_MESSAGE);

        $newStatus = $payload['status'];
        $offer->setStatus($newStatus);

        $em->flush();

        if (LeaseOffer::LEASE_OFFER_STATUS_CLOSED == $newStatus) {
            $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
            $salesCompanyId = $adminPlatform['sales_company_id'];
            $platform = $adminPlatform['platform'];
            $message = '关闭报价';

            $this->get('sandbox_api.admin_remark')->autoRemark(
                $this->getAdminId(),
                $platform,
                $salesCompanyId,
                $message,
                AdminRemark::OBJECT_LEASE_OFFER,
                $offer->getId()
            );

            $logMessage = '关闭报价';
            $this->get('sandbox_api.admin_status_log')->autoLog(
                $this->getAdminId(),
                $newStatus,
                $logMessage,
                AdminStatusLog::OBJECT_LEASE_OFFER,
                $offer->getId(),
                AdminStatusLog::TYPE_SALES_ADMIN,
                $salesCompanyId
            );
        }

        return new View();
    }

    /**
     * @param LeaseOffer $offer
     * @param $leaseRentTypeIds
     * @param $method
     *
     * @return View
     */
    private function saveLeaseOffer(
        $offer,
        $leaseRentTypeIds,
        $method
    ) {
        $em = $this->getDoctrine()->getManager();

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $platform = $adminPlatform['platform'];

        $customerId = $offer->getLesseeCustomer();
        if (is_null($customerId)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        } else {
            $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')->find($customerId);
            $this->throwNotFoundIfNull($customer, self::NOT_FOUND_MESSAGE);
        }

        if (LeaseOffer::LEASE_OFFER_LESSEE_TYPE_ENTERPRISE == $offer->getLesseeType()) {
            $enterpriseId = $offer->getLesseeEnterprise();
            if (is_null($enterpriseId)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            } else {
                // check salse enterprise
                $enterprise = $em->getRepository('SandboxApiBundle:User\EnterpriseCustomer')->find($enterpriseId);
                $this->throwNotFoundIfNull($enterprise, self::NOT_FOUND_MESSAGE);

                $enterpriseContacts = $em->getRepository('SandboxApiBundle:User\EnterpriseCustomerContacts')
                    ->findOneBy(array('enterpriseCustomerId' => $enterpriseId, 'customerId' => $customerId));
                if (!$enterpriseContacts) {
                    $enterpriseContacts = new EnterpriseCustomerContacts();
                    $enterpriseContacts->setCustomerId($customerId);
                    $enterpriseContacts->setEnterpriseCustomerId($enterpriseId);

                    $em->persist($enterpriseContacts);
                }
            }
        }

        $buildingId = $offer->getBuildingId();
        if ($buildingId) {
            $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);
            $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);
        }

        $productId = $offer->getProductId();
        if ($productId) {
            $product = $em->getRepository('SandboxApiBundle:Product\Product')->find($productId);
            $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);
        }

        $leaseClueId = $offer->getLeaseClueId();
        if ($leaseClueId) {
            $leaseClue = $em->getRepository('SandboxApiBundle:Lease\LeaseClue')->find($leaseClueId);
            $this->throwNotFoundIfNull($leaseClue, self::NOT_FOUND_MESSAGE);
        }

        $startDate = $offer->getStartDate();
        if ($startDate) {
            $offer->setStartDate(new \DateTime($startDate));
        }

        $endDate = $offer->getEndDate();
        if ($endDate) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);
            $offer->setEndDate($endDate);
        }

        if ('POST' == $method) {
            $serialNumber = $this->generateSerialNumber(LeaseOffer::LEASE_OFFER_LETTER_HEAD);
            $offer->setSerialNumber($serialNumber);
            $offer->setCompanyId($salesCompanyId);

            $message = '创建报价';
        } else {
            $message = '更新报价';
        }

        $leaseRentTypes = $offer->getLeaseRentTypes();
        foreach ($leaseRentTypes as $leaseRentType) {
            $offer->removeLeaseRentTypes($leaseRentType);
        }

        foreach ($leaseRentTypeIds as $leaseRentTypeId) {
            $leaseRentType = $em->getRepository('SandboxApiBundle:Lease\LeaseRentTypes')->find($leaseRentTypeId);
            if ($leaseRentType) {
                $offer->addLeaseRentTypes($leaseRentType);
            }
        }

        $em->persist($offer);
        $em->flush();

        $this->get('sandbox_api.admin_remark')->autoRemark(
            $this->getAdminId(),
            $platform,
            $salesCompanyId,
            $message,
            AdminRemark::OBJECT_LEASE_OFFER,
            $offer->getId()
        );

        if ('POST' == $method) {
            if ($leaseClueId) {
                $leaseClue = $em->getRepository('SandboxApiBundle:Lease\LeaseClue')->find($leaseClueId);
                $leaseClue->setStatus(LeaseClue::LEASE_CLUE_STATUS_OFFER);
                $em->flush();

                $clueMessage = '转为报价: '.$offer->getSerialNumber();

                $this->get('sandbox_api.admin_remark')->autoRemark(
                    $this->getAdminId(),
                    $platform,
                    $salesCompanyId,
                    $clueMessage,
                    AdminRemark::OBJECT_LEASE_CLUE,
                    $leaseClueId
                );

                $this->get('sandbox_api.admin_remark')->inheritRemark(
                    AdminRemark::OBJECT_LEASE_CLUE,
                    $leaseClueId,
                    AdminRemark::OBJECT_LEASE_OFFER,
                    $offer->getId()
                );

                $leaseLogMessage = '转为报价：'.$offer->getSerialNumber();
                $this->get('sandbox_api.admin_status_log')->autoLog(
                    $this->getAdminId(),
                    LeaseClue::LEASE_CLUE_STATUS_OFFER,
                    $leaseLogMessage,
                    AdminStatusLog::OBJECT_LEASE_CLUE,
                    $leaseClueId,
                    AdminStatusLog::TYPE_SALES_ADMIN,
                    $salesCompanyId
                );

                $logMessage = '从线索：'.$leaseClue->getSerialNumber().' 转为报价';
            } else {
                $logMessage = '创建报价';
            }

            $this->get('sandbox_api.admin_status_log')->autoLog(
                $this->getAdminId(),
                $offer->getStatus(),
                $logMessage,
                AdminStatusLog::OBJECT_LEASE_OFFER,
                $offer->getId(),
                AdminStatusLog::TYPE_SALES_ADMIN,
                $salesCompanyId
            );

            $response = array(
                'id' => $offer->getId(),
            );

            return new View($response, 201);
        }
    }

    /**
     * @param LeaseOffer $offer
     *
     * @return mixed
     */
    private function handleOfferData(
        $offer
    ) {
        if ($offer->getProductId()) {
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->find($offer->getProductId());

            /** @var Room $room */
            $room = $product->getRoom();

            $typeTagDescription = $this->get('translator')->trans(RoomTypeTags::TRANS_PREFIX.$room->getTypeTag());

            $productData = array(
                'id' => $offer->getProductId(),
                'room' => array(
                    'id' => $room->getId(),
                    'name' => $room->getName(),
                    'type_tag' => $room->getTypeTag(),
                    'type_tag_description' => $typeTagDescription,
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

    /**
     * @param $opLevel
     */
    private function checkAdminLeaseOfferPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_BUILDING_LEASE_OFFER],
            ],
            $opLevel
        );
    }
}
