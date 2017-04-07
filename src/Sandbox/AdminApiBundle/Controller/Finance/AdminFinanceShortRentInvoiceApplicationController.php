<?php

namespace Sandbox\AdminApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Finance\FinanceShortRentInvoice;
use Sandbox\ApiBundle\Entity\Finance\FinanceShortRentInvoiceApplication;
use Sandbox\ApiBundle\Form\Finance\FinanceShortRentInvoiceApplicationPatchType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Finance Short Rent Invoice Application Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminFinanceShortRentInvoiceApplicationController extends PaymentController
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
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Status"
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
     * @Annotations\QueryParam(
     *    name="invoice_no",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="invoice number"
     * )
     *
     * @Method({"GET"})
     * @Route("/applications")
     *
     * @return View
     */
    public function getShortRentInvoiceApplicationsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->checkAdminInvoicePermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $status = $paramFetcher->get('status');
        $amountStart = $paramFetcher->get('amount_start');
        $amountEnd = $paramFetcher->get('amount_end');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $companyId = $paramFetcher->get('company_id');
        $invoiceNo = $paramFetcher->get('invoice_no');

        $offset = ($pageIndex - 1) * $pageLimit;

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoiceApplication')
            ->countShortRentInvoiceApplications(
                $createStart,
                $createEnd,
                $amountStart,
                $amountEnd,
                $status,
                $companyId,
                $invoiceNo
            );

        $invoices = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoiceApplication')
            ->getShortRentInvoiceApplications(
                $createStart,
                $createEnd,
                $amountStart,
                $amountEnd,
                $status,
                $pageLimit,
                $offset,
                $companyId,
                $invoiceNo
            );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['sales_admin_list', 'admin_detail']));
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
     * @Route("/applications/{id}")
     *
     * @return View
     */
    public function getShortRentInvoiceApplicationByIdAction(
        Request $request,
        $id
    ) {
        $this->checkAdminInvoicePermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $application = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoiceApplication')
            ->find($id);
        $this->throwNotFoundIfNull($application, self::NOT_FOUND_MESSAGE);

        $ids = $application->getInvoiceIds();
        if (is_null($ids) || empty($ids)) {
            $this->throwNotFoundIfNull($ids, self::NOT_FOUND_MESSAGE);
        }
        $idArray = explode(',', $ids);

        $invoices = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoice')
            ->getShortRentInvoicesByIds(
                $idArray
            );

        $application->setInvoices($invoices);

        $view = new View($application);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['sales_admin_detail', 'admin_detail']));

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/applications/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchShortRentInvoiceApplicationAction(
        Request $request,
        $id
    ) {
        $this->checkAdminInvoicePermission($this->getAdminId(), AdminPermission::OP_LEVEL_EDIT);

        $application = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoiceApplication')
            ->find($id);

        $currentStatus = $application->getStatus();
        if ($currentStatus != FinanceShortRentInvoiceApplication::STATUS_PENDING) {
            return $this->customErrorView(
                400,
                self::SHORT_RENT_INVOICE_APPLICATION_WRONG_STATUS_CODE,
                self::SHORT_RENT_INVOICE_APPLICATION_WRONG_STATUS_MESSAGE
            );
        }

        $applicationJson = $this->container->get('serializer')->serialize($application, 'json');
        $patch = new Patch($applicationJson, $request->getContent());
        $applicationJson = $patch->apply();

        $form = $this->createForm(new FinanceShortRentInvoiceApplicationPatchType(), $application);
        $form->submit(json_decode($applicationJson, true));

        $status = $application->getStatus();
        if ($status != FinanceShortRentInvoiceApplication::STATUS_CONFIRMED &&
            $status != FinanceShortRentInvoiceApplication::STATUS_REVOKED
        ) {
            return $this->customErrorView(
                400,
                self::SHORT_RENT_INVOICE_APPLICATION_WRONG_STATUS_CODE,
                self::SHORT_RENT_INVOICE_APPLICATION_WRONG_STATUS_MESSAGE
            );
        }

        $ids = $application->getInvoiceIds();
        if (is_null($ids) || empty($ids)) {
            $this->throwNotFoundIfNull($ids, self::NOT_FOUND_MESSAGE);
        }
        $idArray = explode(',', $ids);

        $invoices = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoice')
            ->getShortRentInvoicesByIds($idArray);

        $now = new \DateTime();
        $invoiceStatus = FinanceShortRentInvoice::STATUS_COMPLETED;

        if ($status == FinanceShortRentInvoiceApplication::STATUS_REVOKED) {
            $invoiceStatus = FinanceShortRentInvoice::STATUS_PENDING;
            $application->setRevokeDate($now);
        } else {
            $application->setConfirmDate($now);

            $wallet = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Finance\FinanceSalesWallet')
                ->findOneBy(['companyId' => $application->getCompanyId()]);

            if (!is_null($wallet)) {
                $shortRentAmount = $wallet->getShortRentInvoiceAmount();
                $withdrawAmount = $wallet->getWithdrawableAmount();

                $wallet->setShortRentInvoiceAmount($shortRentAmount - $application->getAmount());
                $wallet->setWithdrawableAmount($withdrawAmount + $application->getAmount());
            }
        }

        foreach ($invoices as $invoice) {
            $invoice->setStatus($invoiceStatus);
        }

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
                array(
                    'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES_INVOICE_CONFIRM,
                ),
            ),
            $level
        );
    }
}
