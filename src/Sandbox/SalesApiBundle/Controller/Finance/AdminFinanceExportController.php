<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Traits\FinanceSalesExportTraits;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

class AdminFinanceExportController extends SalesRestController
{
    use FinanceSalesExportTraits;

    /**
     * @Annotations\QueryParam(
     *    name="year",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="month",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    default="zh",
     *    nullable=true,
     *    requirements="(zh|en)",
     *    strict=true,
     *    description="export language"
     * )
     *
     * @Method({"GET"})
     * @Route("/finance/export/poundage")
     *
     * @return View
     */
    public function getFinanceExportPoundageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_REPORT_DOWNLOAD,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $year = $paramFetcher->get('year');
        $month = $paramFetcher->get('month');
        $language = $paramFetcher->get('language');

        $startString = $year.'-'.$month.'-01';
        $endString = $year.'-'.$month.'-31';

        $serviceBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
            ->findServiceBillList(
                $data['company_id'],
                null,
                null,
                null,
                $startString,
                $endString,
                null,
                null
            );

        return $this->getFinanceExportPoundage(
            $serviceBills,
            $language
        );
    }

    /**
     * @Annotations\QueryParam(
     *    name="year",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="month",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    default="zh",
     *    nullable=true,
     *    requirements="(zh|en)",
     *    strict=true,
     *    description="export language"
     * )
     *
     * @Method({"GET"})
     * @Route("/finance/export/summary")
     *
     * @return View
     */
    public function getFinanceSummaryExportAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_REPORT_DOWNLOAD,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $year = $paramFetcher->get('year');
        $month = $paramFetcher->get('month');
        $language = $paramFetcher->get('language');

        $startString = $year.'-'.$month.'-01';
        $startDate = new \DateTime($startString);

        $endString = $startDate->format('Y-m-t');
        $endDate = new \DateTime($endString);
        $endDate->setTime(23, 59, 59);

        // event orders
        $events = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getEventOrderSummary(
                $startDate,
                $endDate,
                $data['company_id']
            );

        $orderTypes = array(
            ProductOrder::OWN_TYPE,
            ProductOrder::OFFICIAL_PREORDER_TYPE,
        );
        $shortOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getCompletedOrderSummary(
                $startDate,
                $endDate,
                $data['company_id'],
                $orderTypes
            );

        $membershipOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getMembershipOrdersByDate(
                $startDate,
                $endDate,
                $data['company_id']
            );

        $filename = '秒租平台订单报表';
        return $this->getFinanceSummaryExport(
            $filename,
            $language,
            $events,
            $shortOrders,
            $membershipOrders
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    array=false,
     *    nullable=true,
     * )
     *
     * @Route("/finance/export/wallet_flows")
     * @Method({"GET"})
     *
     * @return mixed
     */
    public function exportSalesWalletFlowsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_REPORT_DOWNLOAD,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );
        $salesCompanyId = $data['company_id'];

        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $language = $paramFetcher->get('language');

        $now = new \DateTime('now');
        $beginDate = clone $now;
        $beginDate = $beginDate->modify('-30 days');
        $startDate = is_null($startDate) ? $beginDate : $startDate;
        $endDate = is_null($endDate) ? $now : $endDate;

        $flows = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSalesWalletFlow')
            ->getAdminWalletFlows(
                $salesCompanyId,
                $startDate,
                $endDate
            );

        return $this->getFinanceSalesWalletFlowsExport(
            $flows,
            $language
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_date",
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    array=false,
     *    nullable=true,
     * )
     *
     * @Route("/finance/export/orders")
     * @Method({"GET"})
     *
     * @return View
     */
    public function exportSalesOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_REPORT_DOWNLOAD,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $startDate = new \DateTime($paramFetcher->get('startDate'));
        $endDate = new \DateTime($paramFetcher->get('endDate'));
        $endDate = $endDate->setTime('23', '59', '59');
        $language = $paramFetcher->get('language');

        // event orders
        $events = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getEventOrderSummary(
                $startDate,
                $endDate,
                $data['company_id']
            );

        $orderTypes = [
            ProductOrder::OWN_TYPE,
            ProductOrder::OFFICIAL_PREORDER_TYPE,
            ProductOrder::PREORDER_TYPE,
        ];
        $shortOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getCompletedOrderSummary(
                $startDate,
                $endDate,
                $data['company_id'],
                $orderTypes
            );

        $membershipOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getMembershipOrdersByDate(
                $startDate,
                $endDate,
                $data['company_id']
            );

        $filename = '订单明细导表';
        return $this->getFinanceSummaryExport(
            $filename,
            $language,
            $events,
            $shortOrders,
            $membershipOrders
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    array=false,
     *    default="zh",
     *    nullable=true
     * )
     *
     * @Route("/finance/export/cashiers")
     * @Method({"GET"})
     *
     * @return mixed
     */
    public function exportFianceCashierAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_REPORT_DOWNLOAD,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $language = $paramFetcher->get('language');

        $receivables = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceReceivables')
            ->getOrderLists(
                $data['company_id'],
                $startDate,
                $endDate
            );

        return $this->getFinanceCashierExport(
            $receivables,
            $language
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    array=false,
     *    nullable=true,
     * )
     *
     * @Route("/finance/export/bills")
     * @Method({"GET"})
     *
     * @return View
     */
    public function exportSalesBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_REPORT_DOWNLOAD,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $startDate = new \DateTime($paramFetcher->get('startDate'));
        $endDate = new \DateTime($paramFetcher->get('endDate'));
        $endDate = $endDate->setTime('23', '59', '59');
        $language = $paramFetcher->get('language');

        $billStatus = array(
            LeaseBill::STATUS_UNPAID,
            LeaseBill::STATUS_PAID,
        );

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->getExportSalesBills(
                $data['building_ids'],
                $startDate,
                $endDate,
                $billStatus
            );

        return $this->getFinanceExportBills(
            $language,
            $bills
        );
    }
}
