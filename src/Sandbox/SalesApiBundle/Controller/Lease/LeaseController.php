<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;
use Sandbox\ApiBundle\Form\Lease\LeasePatchType;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations;

class LeaseController extends SalesRestController
{
    use GenerateSerialNumberTrait;

    /**
     * Get Lease Detail.
     *
     * @param $id
     *
     * @Route("/leases/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLeaseAction(
        $id
    ) {
        // check user permission
//        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')->find($id);

        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        // TODO: To get necessary fields of drawee, contact
        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['main'])
        );
        $view->setData($lease);

        return $view;
    }

    /**
     * Get List of Lease.
     *
     * @Route("/leases")
     * @Method({"GET"})
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="pending, withdrawn, accepted, rejected"
     * )
     *
     * @Annotations\QueryParam(
     *    name="buildingId",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
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
     *    name="create_date_range",
     *    default=null,
     *    nullable=true,
     *    description="last_week, last_month"
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
     * @return View
     */
    public function getLeasesAction(
        ParamFetcherInterface $paramFetcher,
        Request $request
    ) {
        // check user permission
//        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

        $status = $paramFetcher->get('status');

        // search keyword and query
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        // creation date filter
        $createRange = $paramFetcher->get('create_date_range');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');

        // appointment date filter
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        //get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
            )
        );

        $leases = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->findLeases(
                $myBuildingIds,
                $status,
                $keyword,
                $keywordSearch,
                $createRange,
                $createStart,
                $createEnd,
                $startDate,
                $endDate,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->countLeasesAmount(
                $myBuildingIds,
                $status,
                $keyword,
                $keywordSearch,
                $createRange,
                $createStart,
                $createEnd,
                $startDate,
                $endDate,
                $limit,
                $offset
            );

        foreach ($leases as $lease) {
            $totalLeaseBills = $this->getLeaseBillRepo()->findBy(array(
                'lease' => $lease->getId(),
                'type' => LeaseBill::TYPE_LEASE
            ));
            $lease->setTotalLeaseBillsAmount(count($totalLeaseBills));

            $paidLeaseBills = $this->getLeaseBillRepo()->findBy(array(
                'lease' => $lease->getId(),
                'type' => LeaseBill::TYPE_LEASE,
                'status' => LeaseBill::STATUS_PAID
            ));
            $lease->setPaidLeaseBillsAmount(count($paidLeaseBills));

            $otherBills = $this->getLeaseBillRepo()->findBy(array(
                'lease' => $lease->getId(),
                'type' => LeaseBill::TYPE_OTHER
            ));

            $lease->setOtherBillsAmount(count($otherBills));
        }

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['lease_list'])
        );

        $view->setData(
            array(
            'current_page_number' => (int) $pageIndex,
            'num_items_per_page' => (int) $pageLimit,
            'items' => $leases,
            'total_count' => (int) $count,
        ));

        return $view;
    }

    /**
     * Create a new lease.
     *
     * @param $request
     *
     * @Route("/leases")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postLeaseAction(
        Request $request
    ) {
        // check user permission
//        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $payload = json_decode($request->getContent(), true);

        return $this->handleLeasePost($payload);
    }

    /**
     * Edit a lease.
     *
     * @param $request
     * @param $id
     *
     * @Route("/leases/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putLeaseAction(
        Request $request,
        $id
    ) {
        // check user permission
//        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $payload = json_decode($request->getContent(), true);

        return $this->handleLeasePut($payload, $id);
    }

    /**
     * Patch Lease Status.
     *
     * @param $request
     * @param $id
     *
     * @Route("/leases/{id}/status")
     * @Method({"PATCH"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function patchLeaseStatusAction(
        Request $request,
        $id
    ) {
        // check user permission
//        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')->find($id);

        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $leaseJson = $this->container
            ->get('serializer')
            ->serialize($lease, 'json');

        $patch = new Patch($leaseJson, $request->getContent());
        $leaseJson = $patch->apply();

        $form = $this->createForm(new LeasePatchType(), $lease);
        $form->submit(json_decode($leaseJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));

        return new View();
    }

    /**
     * Delete Draft of Lease.
     *
     * @Route("/leases/{id}")
     * @Method({"DELETE"})
     *
     * @param int $id
     *
     * @throws \Exception
     */
    public function deleteLeaseAction(
        $id
    ) {
        // check user permission
//        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);
        $em = $this->getDoctrine()->getManager();

        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')->findOneBy(
                array(
                    'id' => $id,
                    'status' => Lease::LEASE_STATUS_DRAFTING,
                )
            );

        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $em->remove($lease);
        $em->flush();

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_DELETE,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));
    }

    /**
     * @param $payload
     *
     * @return View
     */
    private function handleLeasePost(
        $payload
    ) {
        $this->checkLeaseAttributesIsValid($payload);

        $em = $this->getDoctrine()->getManager();
        $lease = new Lease();

        if (!empty($payload['drawee'])) {
            $drawee = $this->getUserRepo()->find($payload['drawee']);
            $this->throwNotFoundIfNull($drawee, self::NOT_FOUND_MESSAGE);
            $lease->setDrawee($drawee);
        }

        if (!empty($payload['supervisor'])) {
            $supervisor = $this->getUserRepo()->find($payload['drawee']);
            $this->throwNotFoundIfNull($supervisor, self::NOT_FOUND_MESSAGE);
            $lease->setDrawee($supervisor);
        }

        $product = $this->getProductRepo()->find($payload['product']);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);
        $lease->setProduct($product);

        $startDate = new \DateTime($payload['start_date']);
        $endDate = new \DateTime($payload['end_date']);

        $lease->setDeposit($payload['deposit']);
        $lease->setEndDate($endDate);
        $lease->setLesseeAddress($payload['lessee_address']);
        $lease->setLesseeContact($payload['lessee_contact']);
        $lease->setLesseeEmail($payload['lessee_email']);
        $lease->setLesseeName($payload['lessee_name']);
        $lease->setLesseePhone($payload['lessee_phone']);
        $lease->setLessorAddress($payload['lessor_address']);
        $lease->setLessorName($payload['lessor_name']);
        $lease->setLessorPhone($payload['lessor_phone']);
        $lease->setLessorEmail($payload['lessor_email']);
        $lease->setLessorContact($payload['lessor_contact']);
        $lease->setMonthlyRent($payload['monthly_rent']);
        $lease->setPurpose($payload['purpose']);
        $lease->setStatus($payload['status']);
        $lease->setStartDate($startDate);
        $lease->setSerialNumber($this->generateLeaseSerialNumber());
        $lease->setTotalRent($payload['total_rent']);

        if (
            isset($payload['other_expenses']) &&
            gettype($payload['other_expenses'] == 'string')
        ) {
            $lease->setOtherExpenses($payload['other_expenses']);
        }

        if (
            isset($payload['supplementary_terms']) &&
            gettype($payload['supplementary_terms'] == 'string')
        ) {
            $lease->setSupplementaryTerms($payload['supplementary_terms']);
        }

        // If lease create from product appointment
        if (
            isset($payload['product_appointment']) &&
            gettype($payload['product_appointment'] == 'doulbe')
        ) {
            $productAppointment = $this->getProductAppointmentRepo()
                ->find($payload['product_appointment']);

            $this->throwNotFoundIfNull($productAppointment, self::NOT_FOUND_MESSAGE);

            if ($productAppointment->getStatus() != ProductAppointment::STATUS_ACCEPTED) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $lease->setProductAppointment($productAppointment);
        }

        $this->handleLeaseRentTypesPost($payload['lease_rent_types'], $lease);
        $this->handleLeaseBillPost($payload['bills'], $lease, $em);

        $em->persist($lease);
        $em->flush();

        $response = array(
            'id' => $lease->getId(),
        );

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_CREATE,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));

        return new View($response, 201);
    }

    private function checkLeaseAttributesIsValid($payload)
    {
        if (
            !key_exists('lessee_address', $payload) ||
            !key_exists('lessee_contact', $payload) ||
            !key_exists('lessee_email', $payload) ||
            !key_exists('lessee_name', $payload) ||
            !key_exists('lessee_phone', $payload) ||
            !key_exists('lessor_address', $payload) ||
            !key_exists('lessor_name', $payload) ||
            !key_exists('lessor_phone', $payload) ||
            !key_exists('lessor_email', $payload) ||
            !key_exists('lessor_contact', $payload) ||
            !key_exists('deposit', $payload) ||
            !key_exists('monthly_rent', $payload) ||
            !key_exists('total_rent', $payload) ||
            !key_exists('status', $payload) ||
            !key_exists('purpose', $payload) ||
            !key_exists('start_date', $payload) ||
            !key_exists('end_date', $payload) ||
            !key_exists('drawee', $payload) ||
            !key_exists('supervisor', $payload) ||
            !key_exists('product', $payload) ||
            !key_exists('product', $payload) ||
            !key_exists('product', $payload) ||
            !key_exists('lease_rent_types', $payload) ||
            !key_exists('bills', $payload)
        ) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        if ($payload['status'] == Lease::LEASE_STATUS_REVIEWING) {
            if (
                gettype($payload['lessee_address']) != 'string' ||
                gettype($payload['lessee_contact']) != 'string' ||
                gettype($payload['lessee_email']) != 'string' ||
                gettype($payload['lessee_name']) != 'string' ||
                gettype($payload['lessee_phone']) != 'string' ||
                gettype($payload['lessor_address']) != 'string' ||
                gettype($payload['lessor_name']) != 'string' ||
                gettype($payload['lessor_phone']) != 'string' ||
                gettype($payload['lessor_email']) != 'string' ||
                gettype($payload['lessor_contact']) != 'string' ||
                (gettype($payload['deposit']) != 'double' && gettype($payload['deposit']) != 'integer') ||
                (gettype($payload['monthly_rent']) != 'double' && gettype($payload['deposit']) != 'integer') ||
                (gettype($payload['total_rent']) != 'double' && gettype($payload['deposit']) != 'integer') ||
                gettype($payload['status']) != 'string' ||
                gettype($payload['purpose']) != 'string' ||
                gettype($payload['start_date']) != 'string' ||
                gettype($payload['end_date']) != 'string' ||
                gettype($payload['drawee']) != 'integer' ||
                gettype($payload['supervisor']) != 'integer' ||
                gettype($payload['product']) != 'integer' ||
                gettype($payload['lease_rent_types']) != 'array' ||
                gettype($payload['bills']) != 'array' ||
                empty($payload['lessee_address']) ||
                empty($payload['lessee_contact']) ||
                empty($payload['lessee_email']) ||
                empty($payload['lessee_name']) ||
                empty($payload['lessee_phone']) ||
                empty($payload['lessor_address']) ||
                empty($payload['lessor_contact']) ||
                empty($payload['lessor_name']) ||
                empty($payload['lessor_phone']) ||
                empty($payload['lessor_email']) ||
                empty($payload['deposit']) ||
                empty($payload['monthly_rent']) ||
                empty($payload['total_rent']) ||
                empty($payload['status']) ||
                empty($payload['purpose']) ||
                empty($payload['start_date']) ||
                empty($payload['end_date']) ||
                empty($payload['drawee']) ||
                empty($payload['supervisor']) ||
                empty($payload['product']) ||
                empty($payload['lease_rent_types']) ||
                empty($payload['bills'])
            ) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
        }

        foreach ($payload['bills'] as $billAttributes) {
            if (
                !key_exists('name', $billAttributes) ||
                !key_exists('amount', $billAttributes) ||
                !key_exists('description', $billAttributes) ||
                !key_exists('start_date', $billAttributes) ||
                !key_exists('end_date', $billAttributes) ||
                gettype($billAttributes['name']) != 'string' ||
                (gettype($billAttributes['amount']) != 'double' && gettype($billAttributes['amount']) != 'integer') ||
                gettype($billAttributes['description']) != 'string' ||
                gettype($billAttributes['start_date']) != 'string' ||
                gettype($billAttributes['end_date']) != 'string' ||
                empty($billAttributes['name']) ||
                empty($billAttributes['amount']) ||
                empty($billAttributes['description']) ||
                empty($billAttributes['start_date']) ||
                empty($billAttributes['end_date'])
            ) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
        }

        return;
    }

    private function getUserRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User');
    }

    private function getProductRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product');
    }

    private function getLeaseRentTypesRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseRentTypes');
    }

    private function getLeaseRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease');
    }

    private function getProductAppointmentRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment');
    }

    private function handleLeaseRentTypesPost(
        $leaseRentTypeIds,
        $lease
    ) {
        foreach ($leaseRentTypeIds as $leaseRentTypeId) {
            $leaseRentType = $this->getLeaseRentTypesRepo()->find($leaseRentTypeId);
            if (is_null($leaseRentType)) {
                throw new NotFoundHttpException(self::NOT_FOUND_MESSAGE);
            }
            $lease->addLeaseRentTypes($leaseRentType);
        }
    }

    private function handleLeaseBillPost(
        $payloadBills,
        $lease,
        $em
    ) {
        foreach ($payloadBills as $billAttributes) {
            $bill = new LeaseBill();

            $startDate = new \DateTime($billAttributes['start_date']);
            $endDate = new \DateTime($billAttributes['end_date']);

            $bill->setName($billAttributes['name']);
            $bill->setAmount($billAttributes['amount']);
            $bill->setDescription($billAttributes['description']);
            $bill->setSerialNumber($this->generateSerialNumber(LeaseBill::LEASE_BILL_LETTER_HEAD));
            $bill->setStartDate($startDate);
            $bill->setEndDate($endDate);
            $bill->setType(LeaseBill::TYPE_LEASE);
            $bill->setStatus(LeaseBill::STATUS_PENDING);
            $bill->setLease($lease);

            $em->persist($bill);
        }
    }

    /**
     * @param $payload
     * @param $leaseId
     *
     * @return View
     */
    private function handleLeasePut($payload, $leaseId)
    {
        $this->checkLeaseAttributesIsValid($payload);

        $em = $this->getDoctrine()->getManager();
        $lease = $this->getLeaseRepo()->find($leaseId);

        if (!empty($payload['drawee'])){
            $drawee = $this->getUserRepo()->find($payload['drawee']);
            $this->throwNotFoundIfNull($drawee, self::NOT_FOUND_MESSAGE);
            $lease->setDrawee($drawee);
        }

        if (!empty($payload['supervisor'])){
            $supervisor = $this->getUserRepo()->find($payload['drawee']);
            $this->throwNotFoundIfNull($supervisor, self::NOT_FOUND_MESSAGE);
            $lease->setDrawee($supervisor);
        }

        $product = $this->getProductRepo()->find($payload['product']);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);
        $lease->setProduct($product);

        $startDate = new \DateTime($payload['start_date']);
        $endDate = new \DateTime($payload['end_date']);

        $lease->setDeposit($payload['deposit']);
        $lease->setEndDate($endDate);
        $lease->setLesseeAddress($payload['lessee_address']);
        $lease->setLesseeContact($payload['lessee_contact']);
        $lease->setLesseeEmail($payload['lessee_email']);
        $lease->setLesseeName($payload['lessee_name']);
        $lease->setLesseePhone($payload['lessee_phone']);
        $lease->setLessorAddress($payload['lessor_address']);
        $lease->setLessorName($payload['lessor_name']);
        $lease->setLessorPhone($payload['lessor_phone']);
        $lease->setLessorEmail($payload['lessor_email']);
        $lease->setLessorContact($payload['lessor_contact']);
        $lease->setMonthlyRent($payload['monthly_rent']);
        $lease->setPurpose($payload['purpose']);
        $lease->setStatus($payload['status']);
        $lease->setStartDate($startDate);
        $lease->setSerialNumber($this->generateLeaseSerialNumber());
        $lease->setTotalRent($payload['total_rent']);

        if (
            isset($payload['other_expenses']) &&
            gettype($payload['other_expenses'] == 'string')
        ) {
            $lease->setOtherExpenses($payload['other_expenses']);
        }

        if (
            isset($payload['supplementary_terms']) &&
            gettype($payload['supplementary_terms'] == 'string')
        ) {
            $lease->setSupplementaryTerms($payload['supplementary_terms']);
        }

        // If lease from product appointment
        if (
            isset($payload['product_appointment']) &&
            gettype($payload['product_appointment'] == 'doulbe')
        ) {
            $productAppointment = $this->getProductAppointmentRepo()
                ->find($payload['product_appointment']);

            $this->throwNotFoundIfNull($productAppointment, self::NOT_FOUND_MESSAGE);

            if ($productAppointment->getStatus() != ProductAppointment::STATUS_ACCEPTED) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $lease->setProductAppointment($productAppointment);
        }

        $this->handleLeaseRentTypesPost($payload['lease_rent_types'], $lease);
        $this->handleLeaseBillPut($payload['bills'], $lease, $em);

        $em->persist($lease);
        $em->flush();

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));

        return new View();
    }

    private function handleLeaseBillPut(
        $payloadBills,
        $lease,
        $em
    ) {
        $bills = $this->getLeaseBillRepo()->findBy(array(
            'lease' => $lease->getId(),
            'status' => LeaseBill::STATUS_PENDING,
            'type' => LeaseBill::TYPE_LEASE,
        ));

        foreach ($bills as $bill) {
            $em->remove($bill);
        }

        foreach ($payloadBills as $billAttributes) {
            $bill = new LeaseBill();

            $startDate = new \DateTime($billAttributes['start_date']);
            $endDate = new \DateTime($billAttributes['end_date']);

            $bill->setName($billAttributes['name']);
            $bill->setAmount($billAttributes['amount']);
            $bill->setDescription($billAttributes['description']);
            $bill->setSerialNumber($this->generateSerialNumber(LeaseBill::LEASE_BILL_LETTER_HEAD));
            $bill->setStartDate($startDate);
            $bill->setEndDate($endDate);
            $bill->setType(LeaseBill::TYPE_LEASE);
            $bill->setStatus(LeaseBill::STATUS_PENDING);
            $bill->setLease($lease);

            $em->persist($bill);
        }
    }

    private function getLeaseBillRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill');
    }
}
