<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Finance\FinanceBillAttachment;
use Sandbox\ApiBundle\Entity\Finance\FinanceBillInvoiceInfo;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentBill;
use Sandbox\ApiBundle\Form\Finance\FinanceBillPatchType;
use Sandbox\ApiBundle\Form\Finance\FinanceBillPostType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Admin Finance Long Rent Bill Controller.
 */
class AdminFinanceLongRentBillController extends SalesRestController
{
    /**
     * Get Long Rent Bills.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
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
     *    name="amount_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="amount start"
     * )
     *
     * @Annotations\QueryParam(
     *    name="amount_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="amount end"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by status"
     * )
     *
     *
     * @Route("/finance/long/rent/bills")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getFinanceBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminSalesLongTermBillPermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        //filters
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $amountStart = $paramFetcher->get('amount_start');
        $amountEnd = $paramFetcher->get('amount_end');
        $status = $paramFetcher->get('status');

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $offset = ($pageIndex - 1) * $pageLimit;

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')
            ->getBillLists(
                $salesCompanyId,
                $status,
                $createStart,
                $createEnd,
                $amountStart,
                $amountEnd,
                $pageLimit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')
            ->countBills(
                $salesCompanyId,
                $status,
                $createStart,
                $createEnd,
                $amountStart,
                $amountEnd
            );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['main']));
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $bills,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/long/rent/bills")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postFinanceBillAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminSalesLongTermBillPermission($this->getAdminId(), AdminPermission::OP_LEVEL_EDIT);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $bill = new FinanceLongRentBill();

        $form = $this->createForm(new FinanceBillPostType(), $bill);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $totalFee = $this->getTotalServiceFee($salesCompanyId);

        $pendingFee = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')
            ->sumBillAmount(
                $salesCompanyId,
                FinanceLongRentBill::STATUS_PENDING
            );

        $amount = $bill->getAmount();

        if (($totalFee - $pendingFee - $amount) < 0) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_FINANCE_BILL_MORE_THAN_TOTAL_SERVICE_FEE_CODE,
                CustomErrorMessagesConstants::ERROR_FINANCE_BILL_MORE_THAN_TOTAL_SERVICE_FEE_MESSAGE
            );
        }

        $bill->setCompanyId($salesCompanyId);

        $em = $this->getDoctrine()->getManager();
        $em->persist($bill);

        $attachments = $bill->getAttachments();
        $this->addAttachments(
            $bill,
            $attachments
        );

        $this->addInvoiceInfo(
            $bill,
            $salesCompanyId
        );

        $em->flush();

        $response = array(
            'id' => $bill->getId(),
        );

        return new View($response, 201);
    }

    /**
     * Get bill info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/finance/long/rent/bills/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getFinanceBillByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminSalesLongTermBillPermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $bill = $this->getDoctrine()->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')->find($id);
        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        $billInvoice = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceBillInvoiceInfo')
            ->findOneBy(array('bill' => $bill));

        $bill->setBillInvoice($billInvoice);

        $view = new View();
        $view->setData($bill);

        return $view;
    }

    /**
     * Patch Bill.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/finance/long/rent/bills/{id}")
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
        $this->checkAdminSalesLongTermBillPermission($this->getAdminId(), AdminPermission::OP_LEVEL_EDIT);

        $bill = $this->getDoctrine()->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')->find($id);
        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        $oldStatus = $bill->getStatus();

        if ($oldStatus != FinanceLongRentBill::STATUS_PENDING) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_FINANCE_BILL_STATUS_NOT_CORRECT_CODE,
                CustomErrorMessagesConstants::ERROR_FINANCE_BILL_STATUS_NOT_CORRECT_MESSAGE
            );
        }

        $billJson = $this->container->get('serializer')->serialize($bill, 'json');
        $patch = new Patch($billJson, $request->getContent());
        $billJson = $patch->apply();
        $form = $this->createForm(new FinanceBillPatchType(), $bill);
        $form->submit(json_decode($billJson, true));

        if ($bill->getStatus() != FinanceLongRentBill::STATUS_CANCELLED) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_FINANCE_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_CODE,
                CustomErrorMessagesConstants::ERROR_FINANCE_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($bill);
        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/long/rent/bills/total/fee")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getTotalServiceFeeAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminSalesLongTermBillPermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $totalFee = $this->getTotalServiceFee($salesCompanyId);

        $pendingFee = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')
            ->sumBillAmount(
                $salesCompanyId,
                FinanceLongRentBill::STATUS_PENDING
            );

        $serviceFee = $totalFee - $pendingFee;

        return new View(array(
            'service_fee' => (float) $serviceFee,
        ));
    }

    /**
     * @param $bill
     * @param $attachments
     */
    private function addAttachments(
        $bill,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();

        if (!is_null($attachments) && !empty($attachments)) {
            $billAttachment = new FinanceBillAttachment();
            $billAttachment->setBill($bill);
            $billAttachment->setContent($attachments[0]['content']);
            $billAttachment->setAttachmentType($attachments[0]['attachment_type']);
            $billAttachment->setFilename($attachments[0]['filename']);
            $billAttachment->setPreview($attachments[0]['preview']);
            $billAttachment->setSize($attachments[0]['size']);
            $em->persist($billAttachment);
        }
    }

    /**
     * @param $bill
     * @param $salesCompanyId
     *
     * @return View
     */
    private function addInvoiceInfo(
        $bill,
        $salesCompanyId
    ) {
        $em = $this->getDoctrine()->getManager();

        $companyInvoice = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileInvoice')
            ->findOneBy(array('salesCompany' => $salesCompanyId));

        $companyExpress = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileExpress')
            ->findOneBy(array('salesCompany' => $salesCompanyId));

        if (!$companyInvoice) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_SALES_COMPANY_INVOICE_NOT_FOUND_CODE,
                CustomErrorMessagesConstants::ERROR_SALES_COMPANY_INVOICE_NOT_FOUND_MESSAGE
            );
        }

        $invoice = $this->transferToJsonWithViewGroup(
            $companyInvoice,
            'finance'
        );

        if (!$companyExpress) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_SALES_COMPANY_EXPRESS_NOT_FOUND_CODE,
                CustomErrorMessagesConstants::ERROR_SALES_COMPANY_EXPRESS_NOT_FOUND_MESSAGE
            );
        }
        $express = $this->transferToJsonWithViewGroup(
            $companyExpress,
            'finance'
        );

        $invoiceInfo = new FinanceBillInvoiceInfo();
        $invoiceInfo->setBill($bill);
        $invoiceInfo->setInvoiceJson($invoice);
        $invoiceInfo->setExpressJson($express);

        $em->persist($invoiceInfo);
    }

    /**
     * @param $input
     * @param $group
     *
     * @return mixed
     */
    private function transferToJsonWithViewGroup(
        $input,
        $group
    ) {
        return $this->getContainer()
            ->get('serializer')
            ->serialize(
                $input,
                'json',
                SerializationContext::create()->setGroups([$group])
            );
    }

    /**
     * @param $salesCompanyId
     *
     * @return mixed
     */
    private function getTotalServiceFee(
        $salesCompanyId
    ) {
        $wallet = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSalesWallet')
            ->findOneBy(['companyId' => $salesCompanyId]);

        $fee = $wallet ? $wallet->getBillAmount() : 0;

        return $fee;
    }

    /**
     * @param $adminId
     * @param $level
     */
    private function checkAdminSalesLongTermBillPermission(
        $adminId,
        $level
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_LONG_TERM_SERVICE_BILLS,
                ),
            ),
            $level
        );
    }
}
