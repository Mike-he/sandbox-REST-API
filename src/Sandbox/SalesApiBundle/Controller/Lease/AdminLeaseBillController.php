<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentServiceBill;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Room\RoomTypes;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Sandbox\ApiBundle\Traits\FinanceTrait;
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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

class AdminLeaseBillController extends SalesRestController
{
    use GenerateSerialNumberTrait;
    use SendNotification;
    use FinanceTrait;

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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/leases/bills/export")
     * @Method({"GET"})
     *
     * @return View
     */
    public function exportBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //authenticate with web browser cookie
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();
        $token = $_COOKIE[self::ADMIN_COOKIE_NAME];

        $userToken = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserToken')
            ->findOneBy([
                'userId' => $adminId,
                'token' => $token,
            ]);
        $this->throwNotFoundIfNull($userToken, self::NOT_FOUND_MESSAGE);

        $adminPlatform = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPlatform')
            ->findOneBy(array(
                'userId' => $adminId,
                'clientId' => $userToken->getClientId(),
            ));
        if (is_null($adminPlatform)) {
            throw new PreconditionFailedHttpException(self::PRECONDITION_NOT_SET);
        }

        $companyId = $adminPlatform->getSalesCompanyId();

        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            [
                ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE],
            ],
            AdminPermission::OP_LEVEL_VIEW,
            AdminPermission::PERMISSION_PLATFORM_SALES,
            $companyId
        );

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findEffectiveBills($companyId);

        return $this->getBillExport($bills);
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

        $status = array(
            LeaseBill::STATUS_UNPAID,
            LeaseBill::STATUS_PAID,
            LeaseBill::STATUS_CANCELLED,
            LeaseBill::STATUS_VERIFY,
        );

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBills(
                $lease,
                $status
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

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['lease_bill']));
        $view->setData($bill);

        return $view;
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
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_BILL_STATUS_NOT_CORRECT_MESSAGE);
        }

        $billJson = $this->container->get('serializer')->serialize($bill, 'json');
        $patch = new Patch($billJson, $request->getContent());
        $billJson = $patch->apply();
        $form = $this->createForm(new LeaseBillPatchType(), $bill);
        $form->submit(json_decode($billJson, true));

        if ($bill->getStatus() != LeaseBill::STATUS_UNPAID) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE);
        }

        if (is_null($bill->getRevisedAmount())) {
            $bill->setRevisedAmount($bill->getAmount());
        }
        $bill->setReviser($this->getUserId());
        $bill->setSendDate(new \DateTime());
        $bill->setSender($this->getUserId());

        if ($oldStatus == LeaseBill::STATUS_PENDING) {
            $billsAmount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $bill->getLease(),
                    null,
                    LeaseBill::STATUS_UNPAID
                );

            $leaseId = $bill->getLease()->getId();
            $urlParam = 'ptype=billsList&status=unpaid&leasesId='.$leaseId;
            $contentArray = $this->generateLeaseContentArray($urlParam);
            // send Jpush notification
            $this->generateJpushNotification(
                [
                    $bill->getLease()->getDraweeId(),
                ],
                LeaseConstants::LEASE_BILL_UNPAID_MESSAGE_PART1,
                LeaseConstants::LEASE_BILL_UNPAID_MESSAGE_PART2,
                $contentArray,
                ' '.$billsAmount.' '
            );

            // set sales invoice
            $this->setLeaseBillInvoice($bill);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($bill);
        $em->flush();

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
     * Bill Collection.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/leases/bills/{id}/collection")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function PatchCollectionAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_PLATFORM_AUDIT],
            ],
            AdminPermission::OP_LEVEL_EDIT
        );

        $bill = $this->getDoctrine()->getRepository("SandboxApiBundle:Lease\LeaseBill")
            ->findOneBy(
                array(
                    'id' => $id,
                    'status' => LeaseBill::STATUS_VERIFY,
                    'payChannel' => LeaseBill::CHANNEL_SALES_OFFLINE,
                )
            );
        $this->throwNotFoundIfNull($bill, CustomErrorMessagesConstants::ERROR_BILL_NOT_FOUND_MESSAGE);

        $billJson = $this->container->get('serializer')->serialize($bill, 'json');
        $patch = new Patch($billJson, $request->getContent());
        $billJson = $patch->apply();
        $form = $this->createForm(new LeaseBillPatchType(), $bill);
        $form->submit(json_decode($billJson, true));

        if ($bill->getStatus() != LeaseBill::STATUS_PAID) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_BILL_STATUS_NOT_CORRECT_MESSAGE);
        }

        if (is_null($bill->getRemark())) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($bill);
        $em->flush();

        $this->generateLongRentServiceFee(
            $bill,
            FinanceLongRentServiceBill::TYPE_BILL_SERVICE_FEE
        );

        // add invoice balance
        if (!$bill->isSalesInvoice()) {
            $this->postConsumeBalance(
                $bill->getDrawee(),
                $bill->getRevisedAmount(),
                $bill->getSerialNumber()
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
            if (!is_null($payload['revised_amount'])) {
                $bill->setRevisedAmount($payload['revised_amount']);
                if (is_null($payload['revision_note'])) {
                    throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE);
                }
                $bill->setRevisionNote($payload['revision_note']);
            } else {
                $bill->setRevisedAmount($bill->getAmount());
            }

            $bill->setStatus(LeaseBill::STATUS_UNPAID);
            $bill->setSendDate(new \DateTime());
            $bill->setSender($this->getUserId());
            $bill->setReviser($this->getUserId());

            // set sales invoice
            $this->setLeaseBillInvoice($bill);

            $em->persist($bill);
            $em->flush();

            // generate log
            $this->generateAdminLogs(array(
                'logModule' => Log::MODULE_LEASE,
                'logAction' => Log::ACTION_EDIT,
                'logObjectKey' => Log::OBJECT_LEASE_BILL,
                'logObjectId' => $bill->getId(),
            ));
        }

        $billsAmount = $em->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBills(
                $bill->getLease(),
                null,
                LeaseBill::STATUS_UNPAID
            );

        $leaseId = $bill->getLease()->getId();
        $urlParam = 'ptype=billsList&status=unpaid&leasesId='.$leaseId;
        $contentArray = $this->generateLeaseContentArray($urlParam);
        // send Jpush notification
        $this->generateJpushNotification(
            [
                $bill->getLease()->getDraweeId(),
            ],
            LeaseConstants::LEASE_BILL_UNPAID_MESSAGE_PART1,
            LeaseConstants::LEASE_BILL_UNPAID_MESSAGE_PART2,
            $contentArray,
            ' '.$billsAmount.' '
        );
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

        // set sales invoice
        $this->setLeaseBillInvoice($bill);

        $em = $this->getDoctrine()->getManager();
        $em->persist($bill);
        $em->flush();

        $response = array(
            'id' => $bill->getId(),
        );

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_CREATE,
            'logObjectKey' => Log::OBJECT_LEASE_BILL,
            'logObjectId' => $bill->getId(),
        ));

        $billsAmount = $em->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBills(
                $bill->getLease(),
                null,
                LeaseBill::STATUS_UNPAID
            );

        $leaseId = $bill->getLease()->getId();
        $urlParam = 'ptype=billsList&status=unpaid&leasesId='.$leaseId;
        $contentArray = $this->generateLeaseContentArray($urlParam);
        // send Jpush notification
        $this->generateJpushNotification(
            [
                $bill->getLease()->getDraweeId(),
            ],
            LeaseConstants::LEASE_BILL_UNPAID_MESSAGE_PART1,
            LeaseConstants::LEASE_BILL_UNPAID_MESSAGE_PART2,
            $contentArray,
            ' '.$billsAmount.' '
        );

        return new View($response, 201);
    }

    /**
     * @param LeaseBill $bill
     */
    private function setLeaseBillInvoice(
        $bill
    ) {
        $lease = $bill->getLease();
        $salesCompany = $lease->getProduct()->getRoom()->getBuilding()->getCompany();
        $serviceInfo = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->findOneBy(array(
                'company' => $salesCompany,
                'tradeTypes' => RoomTypes::TYPE_NAME_LONGTERM,
                'status' => true,
            ));

        if (!is_null($serviceInfo) &&
            $serviceInfo->getDrawer() == SalesCompanyServiceInfos::DRAWER_SALES
        ) {
            $bill->setSalesInvoice(true);
        }

        return;
    }

    /**
     * @param array $bills
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws \PHPExcel_Exception
     */
    private function getBillExport(
        $bills
    ) {
        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Sandbox Orders');
        $excelBody = array();

        $status = array(
            LeaseBill::STATUS_UNPAID => '未付款',
            LeaseBill::STATUS_PAID => '已付款',
            LeaseBill::STATUS_VERIFY => '待确认',
            LeaseBill::STATUS_CANCELLED => '已取消',
        );

        // set excel body
        foreach ($bills as $bill) {
            $room = $bill->getLease()->getProduct()->getRoom();
            $building = $room->getBuilding();
            $company = $building->getCompany();

            $companyService = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
                ->findOneBy(array('company' => $company));

            if ($companyService->getCollectionMethod() == SalesCompanyServiceInfos::COLLECTION_METHOD_SANDBOX) {
                $collectionMethod = '创合收款';
            } else {
                $collectionMethod = $company->getName();
            }

            $payments = $this->getDoctrine()->getRepository('SandboxApiBundle:Payment\Payment')->findAll();
            $payChannel = array();
            foreach ($payments as $payment) {
                $payChannel[$payment->getChannel()] = $payment->getName();
            }
            $payChannel[LeaseBill::CHANNEL_SALES_OFFLINE] = '线下支付';

            $drawee = null;
            $account = null;
            if ($bill->getDrawee()) {
                $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView')->find($bill->getDrawee());
                $drawee = $this->filterEmoji($user->getName());
                $account = $user->getPhone() ? $user->getPhone() : $user->getEmail();
            }

            // set excel body
            $body = array(
                'lease_serial_number' => $bill->getLease()->getSerialNumber(),
                'serial_number' => $bill->getSerialNumber(),
                'name' => $bill->getName(),
                'bill_date' => $bill->getStartDate()->format('Y-m-d').' - '.$bill->getEndDate()->format('Y-m-d'),
                'send_date' => $bill->getSendDate() ? $bill->getSendDate()->format('Y-m-d H:i:s') : '',
                'payment_date' => $bill->getPaymentDate() ? $bill->getPaymentDate()->format('Y-m-d H:i:s') : '',
                'amount' => '￥'.$bill->getAmount(),
                'revised_amount' => $bill->getRevisedAmount() ? '￥'.$bill->getRevisedAmount() : '',
                'status' => $status[$bill->getStatus()],
                'collection_method' => $collectionMethod,
                'pay_channel' => $bill->getPayChannel() ? $payChannel[$bill->getPayChannel()] : '',
                'remark' => $bill->getRemark(),
                'sales_invoice' => $bill->isSalesInvoice() ? $company->getName() : '创合开票',
                'sales_company' => $company->getName(),
                'building_name' => $building->getName(),
                'room_name' => $room->getName(),
                'room_type' => '长租办公室',
                'drawee' => $drawee,
                'account' => $account,
            );

            $excelBody[] = $body;
        }

        $headers = [
            '合同号',
            '账单号',
            '账单名称',
            '账单时间段',
            '账单推送时间',
            '付款时间',
            '账单原价',
            '实收款',
            '账单状态',
            '收款方',
            '支付渠道',
            '收款备注（销售方）',
            '开票方',
            '销售方',
            '社区',
            '空间名称',
            '空间类型',
            '付款人昵称',
            '付款人账号',
        ];

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($excelBody, ' ', 'A2');

        $phpExcelObject->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);

        //set column dimension
        for ($col = ord('a'); $col <= ord('s'); ++$col) {
            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension(chr($col))->setAutoSize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('账单导表');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        $date = new \DateTime('now');
        $stringDate = $date->format('Y-m-d H:i:s');

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'bills_'.$stringDate.'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
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
                ['key' => AdminPermission::KEY_SALES_PLATFORM_AUDIT],
            ],
            $opLevel
        );
    }
}
