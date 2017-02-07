<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Entity\Finance\FinanceBillAttachment;
use Sandbox\ApiBundle\Entity\Finance\FinanceBillInvoiceInfo;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentBill;
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
        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $bill = new FinanceLongRentBill();

        $form = $this->createForm(new FinanceBillPostType(), $bill);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
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
        $bill = $this->getDoctrine()->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')->find($id);
        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        $view = new View();
        $view->setData($bill);

        return $view;
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
            $invoiceInfo->setTitle($companyInvoice->getTitle());
            $invoiceInfo->setCategory($companyInvoice->getCategory());
        }

        if ($companyExpress) {
            $invoiceInfo->setAddress($companyExpress->getAddress());
            $invoiceInfo->setPhone($companyExpress->getPhone());
            $invoiceInfo->setRecipient($companyExpress->getRecipient());
            $invoiceInfo->setZipCode($companyExpress->getZipCode());
        }

        $em->persist($invoiceInfo);
    }
}
