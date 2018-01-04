<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminStatusLog;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Traits\FinanceTrait;
use Sandbox\ApiBundle\Traits\LeaseTrait;
use Sandbox\ApiBundle\Traits\SendNotification;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Form\Lease\LeaseBillPatchType;
use Sandbox\ApiBundle\Form\Lease\LeaseBillPostType;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminLeaseBillController extends SalesRestController
{
    use GenerateSerialNumberTrait;
    use SendNotification;
    use FinanceTrait;
    use LeaseTrait;

    /**
     * Get Lease Bills.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     *    name="channel",
     *    default=null,
     *    nullable=true,
     *    array=true,
     *    description="payment channel"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="search query"
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
     *    name="send_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="send start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="send_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="send end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pay_start_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="pay_end_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
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
     *    description="bill start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_date",
     *    default=null,
     *    nullable=true,
     *    description="bill end date"
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
     * @Route("/lease/bills")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAllBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_VIEW);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

        $channels = $paramFetcher->get('channel');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $sendStart = $paramFetcher->get('send_start');
        $sendEnd = $paramFetcher->get('send_end');
        $payStartDate = $paramFetcher->get('pay_start_date');
        $payEndDate = $paramFetcher->get('pay_end_date');
        $status = $paramFetcher->get('status');
        $building = $paramFetcher->get('building');

        $rentFilter = $paramFetcher->get('rent_filter');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        //sort
        $sortColumn = $paramFetcher->get('sort_column');
        $direction = $paramFetcher->get('direction');

        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_LEASE_BILL,
            )
        );

        $leaseStatus = array(
            Lease::LEASE_STATUS_PERFORMING,
            Lease::LEASE_STATUS_TERMINATED,
            Lease::LEASE_STATUS_MATURED,
            Lease::LEASE_STATUS_END,
            Lease::LEASE_STATUS_CLOSED,
        );

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBillsForSales(
                $myBuildingIds,
                $building,
                $status,
                $channels,
                $keyword,
                $keywordSearch,
                $sendStart,
                $sendEnd,
                $payStartDate,
                $payEndDate,
                $leaseStatus,
                $rentFilter,
                $startDate,
                $endDate,
                $limit,
                $offset,
                $sortColumn,
                $direction
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBillsForSales(
                $myBuildingIds,
                $building,
                $status,
                $channels,
                $keyword,
                $keywordSearch,
                $sendStart,
                $sendEnd,
                $payStartDate,
                $payEndDate,
                $leaseStatus,
                $rentFilter,
                $startDate,
                $endDate
            );

        $bills = $this->get('serializer')->serialize(
            $bills,
            'json',
            SerializationContext::create()->setGroups(['lease_bill'])
        );
        $bills = json_decode($bills, true);

        $view = new View();

        $view->setData(
            array(
                'current_page_number' => (int) $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $bills,
                'total_count' => (int) $count,
            ));

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="ids",
     *     array=true
     * )
     *
     * @Route("/leases/bills/numbers")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBillsNumbersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $ids = $paramFetcher->get('ids');

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->getBillsNumbers(
                $ids
            );

        $response = array();
        foreach ($bills as $bill) {
            array_push($response, array(
                'id' => $bill->getId(),
                'bill_number' => $bill->getSerialNumber(),
                'company_name' => $bill->getLease()->getProduct()->getRoom()->getBuilding()->getCompany()->getName(),
                'building_id' => $bill->getLease()->getProduct()->getRoom()->getBuilding()->getId(),
            ));
        }

        return new View($response);
    }

    /**
     * Get Sale offline Bills lists.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     *    name="status",
     *    default=null,
     *    nullable=true,
     *    description="status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="search query"
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
     *    name="send_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="send start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="send_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="send end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="amount_start",
     *    default=null,
     *    nullable=true,
     *    description="amount start query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="amount_end",
     *    default=null,
     *    nullable=true,
     *    description="amount end query"
     * )
     *
     * @Route("/leases/bills/lists")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBillsListsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_PLATFORM_AUDIT],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $company = $adminPlatform['sales_company_id'];
        $this->throwNotFoundIfNull($company, CustomErrorMessagesConstants::ERROR_SALES_COMPANY_NOT_FOUND_MESSAGE);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $status = $paramFetcher->get('status');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $sendStart = $paramFetcher->get('send_start');
        $sendEnd = $paramFetcher->get('send_end');
        $amountStart = $paramFetcher->get('amount_start');
        $amountEnd = $paramFetcher->get('amount_end');

        if (is_null($status)) {
            $status = [
                LeaseBill::STATUS_VERIFY,
                LeaseBill::STATUS_PAID,
            ];
        }

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBillsByCompany(
                $company,
                LeaseBill::CHANNEL_SALES_OFFLINE,
                $status,
                $keyword,
                $keywordSearch,
                $sendStart,
                $sendEnd,
                $amountStart,
                $amountEnd
            );

        $bills = $this->get('serializer')->serialize(
            $bills,
            'json',
            SerializationContext::create()->setGroups(['lease_bill'])
        );
        $bills = json_decode($bills, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $bills,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get Lease Bills.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     *    name="type",
     *    default=null,
     *    nullable=true,
     *    description="bill type"
     * )
     *
     * @Route("/leases/{id}/bills")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_VIEW);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $lease = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\Lease')->find($id);
        $this->throwNotFoundIfNull($lease, CustomErrorMessagesConstants::ERROR_LEASE_NOT_FOUND_MESSAGE);

        $type = $paramFetcher->get('type');
        $status = [];

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBills(
                $lease,
                $status,
                $type
            );

        $bills = $this->get('serializer')->serialize(
            $bills,
            'json',
            SerializationContext::create()->setGroups(['lease_bill'])
        );
        $bills = json_decode($bills, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $bills,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get bill info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/leases/bills/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getBillByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_VIEW);

        $bill = $this->getDoctrine()->getRepository("SandboxApiBundle:Lease\LeaseBill")->find($id);
        $this->throwNotFoundIfNull($bill, CustomErrorMessagesConstants::ERROR_BILL_NOT_FOUND_MESSAGE);

        $bill->setCategory($this->getBillInvoiceCategory($bill));

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['lease_bill']));
        $view->setData($bill);

        return $view;
    }

    /**
     * @param LeaseBill $bill
     *
     * @return mixed
     */
    private function getBillInvoiceCategory(
        $bill
    ) {
        $roomType = $bill->getLease()->getProduct()->getRoom()->getType();
        $salesCompany = $bill->getLease()->getProduct()->getRoom()->getBuilding()->getCompany();

        $info = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->findOneBy(array(
                'company' => $salesCompany,
                'tradeTypes' => $roomType,
            ));

        if (is_null($info)) {
            return '';
        }

        return $info->getInvoicingSubjects();
    }

    /**
     * Post Other Bill.
     *
     * @param Request $request
     *
     * @Route("/leases/bills")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postBillAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $bill = new LeaseBill();
        $form = $this->createForm(new LeaseBillPostType(), $bill);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $lease = $this->getDoctrine()->getRepository("SandboxApiBundle:Lease\Lease")->find($bill->getLeaseId());
        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        return $this->handleBillPost(
            $lease,
            $bill
        );
    }

    /**
     * Update Bill.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/leases/bills/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchBillAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $bill = $this->getDoctrine()->getRepository("SandboxApiBundle:Lease\LeaseBill")->find($id);
        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        $oldStatus = $bill->getStatus();

        $status = array(
            LeaseBill::STATUS_PENDING,
            LeaseBill::STATUS_UNPAID,
        );

        if (!in_array($oldStatus, $status)) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_CODE,
                CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_MESSAGE
            );
        }

        $billJson = $this->container->get('serializer')->serialize($bill, 'json');
        $patch = new Patch($billJson, $request->getContent());
        $billJson = $patch->apply();
        $form = $this->createForm(new LeaseBillPatchType(), $bill);
        $form->submit(json_decode($billJson, true));

        if (LeaseBill::STATUS_UNPAID != $bill->getStatus()) {
            if (LeaseBill::STATUS_UNPAID != $bill->getStatus()) {
                return $this->customErrorView(
                    400,
                    CustomErrorMessagesConstants::ERROR_PAYLOAD_FORMAT_NOT_CORRECT_CODE,
                    CustomErrorMessagesConstants::ERROR_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE
                );
            }
        }

        if (is_null($bill->getRevisedAmount())) {
            $bill->setRevisedAmount($bill->getAmount());
        }
        $bill->setReviser($this->getUserId());
        $bill->setSendDate(new \DateTime());
        $bill->setSender($this->getUserId());
        $bill->setSalesInvoice(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($bill);
        $em->flush();

        if (LeaseBill::STATUS_PENDING == $oldStatus &&
            LeaseBill::STATUS_UNPAID == $bill->getStatus()
        ) {
            $this->pushBillMessage($bill);

            /** @var Lease $lease */
            $lease =  $bill->getLease();

            $logMessage = '推送账单';
            $this->get('sandbox_api.admin_status_log')->autoLog(
                $this->getAdminId(),
                LeaseBill::STATUS_UNPAID,
                $logMessage,
                AdminStatusLog::OBJECT_LEASE_BILL,
                $id,
                AdminStatusLog::TYPE_SALES_ADMIN,
                $lease->getCompanyId()
            );
        }

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_LEASE_BILL,
            'logObjectId' => $bill->getId(),
        ));

        return new View();
    }

    /**
     * Get Unpaid Bills.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/leases/{id}/bills/unpaid")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getUnpaidBillsAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_VIEW);

        $lease = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\Lease')->find($id);
        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $status = array(
            LeaseBill::STATUS_PENDING,
            LeaseBill::STATUS_UNPAID,
        );

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBills(
                $lease,
                $status
            );

        return new View($bills);
    }

    /**
     * Batch Push Bills.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/leases/{id}/bills/batch/push")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postBatchPushAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $lease = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\Lease')->find($id);
        $this->throwNotFoundIfNull($lease, CustomErrorMessagesConstants::ERROR_LEASE_NOT_FOUND_MESSAGE);

        $payload = json_decode($request->getContent(), true);

        $this->handleBatchPush($payload);
    }

    /**
     * Get Sale offline Bills lists.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/bills/my_bills")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getMyBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $company = $adminPlatform['sales_company_id'];

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findNumbersForSalesInvoice(
                $company,
                LeaseBill::STATUS_PAID
            );

        return new View($bills);
    }

    /**
     * @param $payloads
     */
    private function handleBatchPush(
        $payloads
    ) {
        $em = $this->getDoctrine()->getManager();

        foreach ($payloads as $payload) {
            $bill = $this->getDoctrine()
                ->getRepository("SandboxApiBundle:Lease\LeaseBill")
                ->findOneBy(
                    array(
                        'id' => $payload['id'],
                        'status' => LeaseBill::STATUS_PENDING,
                        'type' => LeaseBill::TYPE_LEASE,
                    )
                );
            if (!$bill) {
                continue;
            }
            if (isset($payload['revised_amount']) && !is_null($payload['revised_amount'])) {
                $bill->setRevisedAmount($payload['revised_amount']);
                if (is_null($payload['revision_note'])) {
                    throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE);
                }
                $bill->setRevisionNote($payload['revision_note']);
            } else {
                $bill->setRevisedAmount($bill->getAmount());
            }

            $bill->setStatus(LeaseBill::STATUS_UNPAID);
            $bill->setSendDate(new \DateTime());
            $bill->setSender($this->getUserId());
            $bill->setReviser($this->getUserId());
            $bill->setSalesInvoice(true);

            $em->persist($bill);
            $em->flush();

            $this->pushBillMessage($bill);

            // generate log
            $this->generateAdminLogs(array(
                'logModule' => Log::MODULE_LEASE,
                'logAction' => Log::ACTION_EDIT,
                'logObjectKey' => Log::OBJECT_LEASE_BILL,
                'logObjectId' => $bill->getId(),
            ));
        }
    }

    /**
     * @param Lease     $lease
     * @param LeaseBill $bill
     *
     * @return View
     */
    private function handleBillPost(
        $lease,
        $bill
    ) {
        $serialNumber = $this->generateSerialNumber(LeaseBill::LEASE_BILL_LETTER_HEAD);
        $startDate = new \DateTime($bill->getStartDate());
        $endDate = new \DateTime($bill->getEndDate());

        $bill->setSerialNumber($serialNumber);
        $bill->setStartDate($startDate);
        $bill->setEndDate($endDate);
        $bill->setType(LeaseBill::TYPE_OTHER);
        $bill->setSendDate(new \DateTime());
        $bill->setStatus(LeaseBill::STATUS_UNPAID);
        $bill->setSender($this->getUserId());
        $bill->setRevisedAmount($bill->getAmount());
        $bill->setLease($lease);
        $bill->setSalesInvoice(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($bill);
        $em->flush();

        $response = array(
            'id' => $bill->getId(),
        );

        // push message
        $this->pushBillMessage($bill);

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_CREATE,
            'logObjectKey' => Log::OBJECT_LEASE_BILL,
            'logObjectId' => $bill->getId(),
        ));

        return new View($response, 201);
    }

    /**
     * @param $opLevel
     */
    private function checkAdminLeasePermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE],
                ['key' => AdminPermission::KEY_SALES_BUILDING_LEASE_BILL],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_AUDIT],
            ],
            $opLevel
        );
    }
}
