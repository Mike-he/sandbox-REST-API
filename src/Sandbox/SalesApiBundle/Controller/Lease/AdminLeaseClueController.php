<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use FOS\RestBundle\View\View;
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

class AdminLeaseClueController extends SalesRestController
{
    use GenerateSerialNumberTrait;

    /**
     * Get Lease Clues.
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
     * @Annotations\QueryParam(
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
     * @Route("/lease/clues")
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
        // check user permission
        $this->checkAdminLeaseCluePermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

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

        $clues = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseClue')
            ->findClues(
                $salesCompanyId,
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
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseClue')
            ->countClues(
                $salesCompanyId,
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

        foreach ($clues as $clue) {
            $this->handleClueData($clue);
        }

        $view = new View();

        $view->setData(
            array(
                'current_page_number' => (int) $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $clues,
                'total_count' => (int) $count,
            ));

        return $view;
    }

    /**
     * Get clue info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/lease/clues/{id}")
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
        // check user permission
        $this->checkAdminLeaseCluePermission(AdminPermission::OP_LEVEL_VIEW);

        $clue = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\LeaseClue')->find($id);
        $this->throwNotFoundIfNull($clue, self::NOT_FOUND_MESSAGE);

        $clue = $this->handleClueData($clue);

        $view = new View();
        $view->setData($clue);

        return $view;
    }

    /**
     * Create a new lease clue.
     *
     * @param $request
     *
     * @Route("/lease/clues")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postLeaseCluesAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminLeaseCluePermission(AdminPermission::OP_LEVEL_EDIT);

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
     * @Route("/lease/clues/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putLeaseCluesAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeaseCluePermission(AdminPermission::OP_LEVEL_EDIT);

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
     * Patch Lease Status.
     *
     * @param $request
     * @param $id
     *
     * @Route("/lease/clues/{id}")
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
        $this->checkAdminLeaseCluePermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        $payload = json_decode($request->getContent(), true);

        $clue = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseClue')
            ->findOneBy(array('id' => $id, 'status' => LeaseClue::LEASE_CLUE_STATUS_CLUE));
        $this->throwNotFoundIfNull($clue, self::NOT_FOUND_MESSAGE);

        $newStatus = $payload['status'];
        $clue->setStatus($newStatus);

        $em->flush();

        if ($newStatus == LeaseClue::LEASE_CLUE_STATUS_CLOSED) {
            $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
            $salesCompanyId = $adminPlatform['sales_company_id'];
            $platform = $adminPlatform['platform'];
            $message = '关闭线索';

            $this->get('sandbox_api.admin_remark')->autoRemark(
                $this->getAdminId(),
                $platform,
                $salesCompanyId,
                $message,
                AdminRemark::OBJECT_LEASE_CLUE,
                $clue->getId()
            );

            $logMessage = '关闭线索';
            $this->get('sandbox_api.admin_status_log')->autoLog(
                $this->getAdminId(),
                $newStatus,
                $logMessage,
                AdminStatusLog::OBJECT_LEASE_CLUE,
                $clue->getId()
            );
        }

        return new View();
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
        if ($status != LeaseClue::LEASE_CLUE_STATUS_CLUE) {
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

        if ($method == 'POST') {
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

        if ($method == 'POST') {
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
    private function handleClueData(
        $clue
    ) {
        if ($clue->getProductId()) {
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->find($clue->getProductId());

            /** @var Room $room */
            $room = $product->getRoom();

            $typeTagDescription = $this->get('translator')->trans(RoomTypeTags::TRANS_PREFIX.$room->getTypeTag());
            $productData = array(
                'id' => $clue->getProductId(),
                'room' => array(
                    'id' => $room->getId(),
                    'name' => $room->getName(),
                    'type_tag' => $room->getTypeTag(),
                    'type_tag_description' => $typeTagDescription,
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
     * @param $opLevel
     */
    private function checkAdminLeaseCluePermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_PLATFORM_LEASE_CLUE],
            ],
            $opLevel
        );
    }
}
