<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
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

        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        //filters
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $amountStart = $paramFetcher->get('amount_start');
        $amountEnd = $paramFetcher->get('amount_end');
        $status = $paramFetcher->get('status');

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')
            ->getBillLists(
                $salesCompanyId,
                $status,
                $createStart,
                $createEnd,
                $amountStart,
                $amountEnd
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

        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $bill = new FinanceLongRentBill();

        $form = $this->createForm(new FinanceBillPostType(), $bill);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        //TODO: check long rent service fee limit

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
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_BILL_STATUS_NOT_CORRECT_MESSAGE);
        }

        $billJson = $this->container->get('serializer')->serialize($bill, 'json');
        $patch = new Patch($billJson, $request->getContent());
        $billJson = $patch->apply();
        $form = $this->createForm(new FinanceBillPatchType(), $bill);
        $form->submit(json_decode($billJson, true));

        if ($bill->getStatus() != FinanceLongRentBill::STATUS_CANCELLED) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_BILLS_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE);
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

        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        //TODO: get total fee
        $totalFee = 1000;

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

        $invoiceInfo = new FinanceBillInvoiceInfo();
        $invoiceInfo->setBill($bill);
        if ($companyInvoice) {
            $invoice = $this->transferToJsonWithViewGroup(
                $companyInvoice,
                'finance'
            );
            $invoiceInfo->setInvoiceJson($invoice);
        }

        if ($companyExpress) {
            $express = $this->transferToJsonWithViewGroup(
                $companyExpress,
                'finance'
            );
            $invoiceInfo->setExpressJson($express);
        }

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
     * @param $adminId
     * @param $level
     */
    private function checkAdminSalesLongTermBillPermission(
        $adminId,
        $level
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
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
