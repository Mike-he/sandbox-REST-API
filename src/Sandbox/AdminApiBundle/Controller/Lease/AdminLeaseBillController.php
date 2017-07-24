<?php

namespace Sandbox\AdminApiBundle\Controller\Lease;

use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Controller\Lease\LeaseController;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentServiceBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseBillOfflineTransfer;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Sandbox\ApiBundle\Form\Lease\LeaseBillOfflineTransferPatch;
use Sandbox\ApiBundle\Traits\FinanceTrait;
use Sandbox\ApiBundle\Traits\LeaseTrait;
use Sandbox\ApiBundle\Traits\SendNotification;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class AdminLeaseBillController extends LeaseController
{
    const WRONG_BILL_STATUS_CODE = 400015;
    const WRONG_BILL_STATUS_MESSAGE = 'Wrong Bill Status';

    use SendNotification;
    use FinanceTrait;
    use LeaseTrait;

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

        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_LEASE],
            ],
            AdminPermission::OP_LEVEL_VIEW,
            AdminPermission::PERMISSION_PLATFORM_OFFICIAL
        );

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findEffectiveBills();

        return $this->getBillExport($bills);
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
        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

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
            SerializationContext::create()->setGroups(['main'])
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
        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['lease_bill']));
        $view->setData($bill);

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/leases/bills/{id}/transfer")
     * @Method({"PATCH"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function patchTransferStatusAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $bill = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findOneBy(
                array(
                    'id' => $id,
                    'payChannel' => LeaseBill::CHANNEL_OFFLINE,
                )
            );

        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        $existTransfer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBillOfflineTransfer')
            ->findOneBy(array('bill' => $id), array('id' => 'DESC'));
        $this->throwNotFoundIfNull($existTransfer, self::NOT_FOUND_MESSAGE);

        $oldStatus = $existTransfer->getTransferStatus();

        // bind data
        $transferJson = $this->container->get('serializer')->serialize($existTransfer, 'json');
        $patch = new Patch($transferJson, $request->getContent());
        $transferJson = $patch->apply();

        $form = $this->createForm(new LeaseBillOfflineTransferPatch(), $existTransfer);
        $form->submit(json_decode($transferJson, true));

        $status = $existTransfer->getTransferStatus();
        $now = new \DateTime();

        switch ($status) {
            case LeaseBillOfflineTransfer::STATUS_PAID:
                if ($oldStatus != LeaseBillOfflineTransfer::STATUS_PENDING) {
                    return $this->customErrorView(
                        400,
                        self::WRONG_BILL_STATUS_CODE,
                        self::WRONG_BILL_STATUS_MESSAGE
                    );
                }

                $bill->setStatus(LeaseBill::STATUS_PAID);
                $bill->setPaymentDate($now);

                // closed old transfer
                $oldTransfers = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Lease\LeaseBillOfflineTransfer')
                    ->findBy(array('bill' => $id, 'transferStatus' => LeaseBillOfflineTransfer::STATUS_PENDING));

                foreach ($oldTransfers as $oldTransfer) {
                    if ($oldTransfer->getId() == $existTransfer->getId()) {
                        continue;
                    }

                    $oldTransfer->setTransferStatus(LeaseBillOfflineTransfer::STATUS_CLOSED);
                }

                $this->get('sandbox_api.bean')->postBeanChange(
                    $bill->getDrawee(),
                    $bill->getRevisedAmount(),
                    $bill->getSerialNumber(),
                    Parameter::KEY_BEAN_PAY_BILL
                );

                //update invitee bean
                $user = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:User\User')
                    ->find($bill->getDrawee());

                if ($user->getInviterId()) {
                    $this->get('sandbox_api.bean')->postBeanChange(
                        $user->getInviterId(),
                        $bill->getRevisedAmount(),
                        $bill->getSerialNumber(),
                        Parameter::KEY_BEAN_INVITEE_PAY_BILL
                    );
                }

                break;
//            case LeaseBillOfflineTransfer::STATUS_RETURNED:
//                if ($oldStatus != LeaseBillOfflineTransfer::STATUS_PENDING) {
//                    return $this->customErrorView(
//                        400,
//                        self::WRONG_BILL_STATUS_CODE,
//                        self::WRONG_BILL_STATUS_MESSAGE
//                    );
//                }

//                $leaseId = $bill->getLease()->getId();
//                $urlParam = 'ptype=billsList&status=unpaid&leasesId='.$leaseId;
//                $contentArray = $this->generateLeaseContentArray($urlParam);
//                // send Jpush notification
//                $this->generateJpushNotification(
//                    [
//                        $bill->getLease()->getDraweeId(),
//                    ],
//                    LeaseConstants::LEASE_BILL_TRANSFER_RETURNED_MESSAGE,
//                    null,
//                    $contentArray
//                );

//                break;
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->generateLongRentServiceFee(
            $bill,
            FinanceLongRentServiceBill::TYPE_BILL_SERVICE_FEE
        );

        // add invoice amount
        if (!$bill->isSalesInvoice()) {
            $invoiced = $this->checkBillShouldInvoiced($bill->getLease());

            $this->postConsumeBalance(
                $bill->getLease()->getDraweeId(),
                $bill->getRevisedAmount(),
                $bill->getLease()->getSerialNumber(),
                $invoiced
            );

            $bill->setInvoiced(true);
            $em->flush();
        }

        return new View();
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
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_LEASE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_TRANSFER_CONFIRM],
            ],
            $opLevel
        );
    }
}
