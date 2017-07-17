<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;
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
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

        $buildingId = $paramFetcher->get('building');

        $clues = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseClue')
            ->findClues(
                $salesCompanyId,
                $buildingId,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseClue')
            ->countClues(
                $salesCompanyId,
                $buildingId
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

        $statusArray = array(
                LeaseClue::LEASE_CLUE_STATUS_CLUE,
                LeaseClue::LEASE_CLUE_STATUS_OFFER,
                LeaseClue::LEASE_CLUE_STATUS_CONTRACT,
            );
        $status = $clue->getStatus();
        if (is_null($status) || !in_array($status, $statusArray)) {
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

        $productAppointmentId = $clue->getProductAppointmentId();
        if ($productAppointmentId) {
            $productAppointment = $em->getRepository('SandboxApiBundle:Product\ProductAppointment')->find($productAppointmentId);
            $this->throwNotFoundIfNull($productAppointment, self::NOT_FOUND_MESSAGE);
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

            $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
            $salesCompanyId = $adminPlatform['sales_company_id'];
            $clue->setCompanyId($salesCompanyId);
        }

        $em->persist($clue);
        $em->flush();

        if ($method == 'POST') {
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

            $productData = array(
                'id' => $clue->getProductId(),
                'room' => array(
                    'id' => $product->getRoom()->getId(),
                    'name' => $product->getRoom()->getName(),
                    'type_tag' => $product->getRoom()->getTypeTag(),
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
}
