<?php

namespace Sandbox\AdminApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Finance\FinanceShortRentInvoice;
use Sandbox\ApiBundle\Form\Finance\FinanceShortRentInvoicePatchType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Finance Short Rent Invoice Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminFinanceShortRentInvoiceController extends SandboxRestController
{
    /**
     * @param Request $request
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
     *    name="create_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
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
     *    name="company_id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="company"
     * )
     *
     *
     * @Method({"GET"})
     * @Route("/short/rent/invoices")
     *
     * @return View
     */
    public function getShortRentInvoicesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->checkAdminInvoicePermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $companyId = $paramFetcher->get('company_id');

        $offset = ($pageIndex - 1) * $pageLimit;

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoice')
            ->countShortRentInvoices(
                $createStart,
                $createEnd,
                null,
                null,
                null,
                $companyId
            );

        $invoices = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoice')
            ->getShortRentInvoices(
                $createStart,
                $createEnd,
                null,
                null,
                null,
                $companyId,
                $pageLimit,
                $offset
            );

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $invoices,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"GET"})
     * @Route("/short/rent/invoices/{id}")
     *
     * @return View
     */
    public function getShortRentInvoiceByIdAction(
        Request $request,
        $id
    ) {
        $this->checkAdminInvoicePermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $invoice = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoice')
            ->find($id);
        $this->throwNotFoundIfNull($invoice, self::NOT_FOUND_MESSAGE);

        $view = new View($invoice);

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/short/rent/invoices/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchShortRentInvoiceAction(
        Request $request,
        $id
    ) {
        $this->checkAdminInvoicePermission($this->getAdminId(), AdminPermission::OP_LEVEL_EDIT);

        $invoice = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoice')
            ->find($id);

        $currentStatus = $invoice->getStatus();
        if ($currentStatus == FinanceShortRentInvoice::STATUS_COMPLETED) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_FINANCE_SHORT_RENT_INVOICE_STATUS_NOT_CORRECT_CODE,
                CustomErrorMessagesConstants::ERROR_FINANCE_SHORT_RENT_INVOICE_STATUS_NOT_CORRECT_MESSAGE
            );
        }

        $invoiceJson = $this->container->get('serializer')->serialize($invoice, 'json');
        $patch = new Patch($invoiceJson, $request->getContent());
        $invoiceJson = $patch->apply();

        $form = $this->createForm(new FinanceShortRentInvoicePatchType(), $invoice);
        $form->submit(json_decode($invoiceJson, true));

        $status = $invoice->getStatus();
        if ($status != FinanceShortRentInvoice::STATUS_COMPLETED) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_FINANCE_SHORT_RENT_INVOICE_STATUS_NOT_CORRECT_CODE,
                CustomErrorMessagesConstants::ERROR_FINANCE_SHORT_RENT_INVOICE_STATUS_NOT_CORRECT_MESSAGE
            );
        }

        if (is_null($invoice->getInvoiceNo())) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_FINANCE_SHORT_RENT_INVOICE_PAYLOAD_FORMAT_NOT_CORRECT_CODE,
                CustomErrorMessagesConstants::ERROR_FINANCE_SHORT_RENT_INVOICE_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE
            );
        }

        $now = new \DateTime();
        $invoice->setConfirmDate($now);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param $adminId
     * @param $level
     */
    private function checkAdminInvoicePermission(
        $adminId,
        $level
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_INVOICE,
                ),
            ),
            $level
        );
    }
}
