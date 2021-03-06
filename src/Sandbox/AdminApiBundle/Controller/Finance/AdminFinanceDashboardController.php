<?php

namespace Sandbox\AdminApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentBill;
use Sandbox\ApiBundle\Entity\Finance\FinanceShortRentInvoice;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyWithdrawals;
use Sandbox\ApiBundle\Entity\Finance\FinanceDashboard;
use Sandbox\ApiBundle\Traits\FinanceOfficialExportTraits;
use Sandbox\ApiBundle\Traits\FinanceTrait;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;

/**
 * Class AdminFinanceDashboardController.
 */
class AdminFinanceDashboardController extends AdminRestController
{
    use FinanceTrait;
    use FinanceOfficialExportTraits;

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
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
     * @Route("/finance/cash_flow/export")
     *
     * @return View
     */
    public function getFinanceExportAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //authenticate with web browser cookie
        $this->authenticateAdminCookie();

        $year = $paramFetcher->get('year');
        $month = $paramFetcher->get('month');
        $language = $paramFetcher->get('language');

        $startString = $year.'-'.$month.'-01';
        $startDate = new \DateTime($startString);
        $startDate->setTime(0, 0, 0);

        $endString = $startDate->format('Y-m-t');
        $endDate = new \DateTime($endString);
        $endDate->setTime(23, 59, 59);

        // get orders
        $events = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getOfficialEventOrders(
                $startDate,
                $endDate
            );

        $shortOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOfficialAdminCompletedOrderSummary(
                $startDate,
                $endDate
            );

        $longBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->getOfficialAdminBills(
                $startDate,
                $endDate
            );

        $shopOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Shop\ShopOrder')
            ->getOfficialAdminShopOrders(
                $startDate,
                $endDate
            );

        $topUpOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\TopUpOrder')
            ->getOfficialTopUpOrders(
                $startDate,
                $endDate
            );

        $cardOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getOfficialCardOrders(
                $startDate,
                $endDate
            );

        $serviceOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->getServiceOrdersByDate(
                $startDate,
                $endDate
            );

        return $this->getFinanceSummaryExport(
            $startDate,
            $language,
            $events,
            $shortOrders,
            $longBills,
            $shopOrders,
            $topUpOrders,
            $cardOrders,
            $serviceOrders
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/cash_flow/dashboard")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFinanceCashFlowAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $now = new \DateTime();
        $startString = $now->format('Y-m').'-01';
        $startDate = new \DateTime($startString);
        $startDate->setTime(0, 0, 0);
        $endString = $now->format('Y-m-t');
        $endDate = new \DateTime($endString);
        $endDate->setTime(23, 59, 59);

        $response = $this->generateCashFlowArray($startDate, $endDate);

        return new View($response);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/cash_flow/dashboard/list")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFinanceCashFlowListAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $financeCashFlowDashboardTimePeriods = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceDashboard')
            ->getTimePeriods(
                FinanceDashboard::TYPE_CASH_FLOW
            );
        $financeCashFlowDashboardTimePeriods = array_reverse($financeCashFlowDashboardTimePeriods);

        $response = array();
        foreach ($financeCashFlowDashboardTimePeriods as $period) {
            $dashboardArray = array();

            $dashboard = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Finance\FinanceDashboard')
                ->findBy(array(
                    'timePeriod' => $period,
                    'type' => FinanceDashboard::TYPE_CASH_FLOW,
                ));

            foreach ($dashboard as $item) {
                $dashboardArray = array_merge($dashboardArray, array(
                    $item->getParameterKey() => $item->getParameterValue(),
                ));
            }

            array_push($response, array(
                'time_period' => $period,
                'cash_flows' => $dashboardArray,
            ));
        }

        return new View($response);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/balance_flow/dashboard")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFinanceBalanceDashboardAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $now = new \DateTime();
        $startString = $now->format('Y-m').'-01';
        $startDate = new \DateTime($startString);
        $startDate->setTime(0, 0, 0);

        $response = $this->generateBalanceFlowArray($startDate, $now);

        return new View($response);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/balance_flow/dashboard/list")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFinanceBalanceFlowListAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $financeBalanceFlowDashboardTimePeriods = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceDashboard')
            ->getTimePeriods(
                FinanceDashboard::TYPE_BALANCE_FLOW
            );
        $financeBalanceFlowDashboardTimePeriods = array_reverse($financeBalanceFlowDashboardTimePeriods);

        $response = array();
        foreach ($financeBalanceFlowDashboardTimePeriods as $period) {
            $dashboardArray = array();

            $dashboard = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Finance\FinanceDashboard')
                ->findBy(array(
                    'timePeriod' => $period,
                    'type' => FinanceDashboard::TYPE_BALANCE_FLOW,
                ));

            foreach ($dashboard as $item) {
                $dashboardArray = array_merge($dashboardArray, array(
                    $item->getParameterKey() => $item->getParameterValue(),
                ));
            }

            array_push($response, array(
                'time_period' => $period,
                'balance_flows' => $dashboardArray,
            ));
        }

        return new View($response);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/dashboard/pending/summary")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFinancePendingSummary(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $longRentBillsCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')
            ->countBills(
                null,
                FinanceLongRentBill::STATUS_PENDING
            );

        $shortRentInvoicesCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoice')
            ->countPendingShortRentInvoices(
                FinanceShortRentInvoice::STATUS_INCOMPLETE
            );

        $companyWithdrawalsCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyWithdrawals')
            ->countPendingSalesCompanyWithdrawals(
                SalesCompanyWithdrawals::STATUS_PENDING
            );

        $needToRefundedOrdersCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countNeedToRefundOrders();

        $transferConfirmCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countTransferConfirm();

        $globals = $this->getGlobals();
        $url = $globals['crm_api_url'].'/admin/dashboard/invoices/count?status[]=pending&status[]=cancelled_wait';
        $invoiceCount = $this->getPendingInvoiceCount($url);

        $response = array(
            'long_rent_bills_count' => (int) $longRentBillsCount,
            'short_rent_invoice_applications_count' => (int) $shortRentInvoicesCount,
            'sales_company_withdrawals_count' => (int) $companyWithdrawalsCount,
            'need_to_refund_orders_count' => (int) $needToRefundedOrdersCount,
            'transfer_comfirm_count' => (int) $transferConfirmCount,
            'pending_invoice_count' => (int) $invoiceCount,
        );

        return new View($response);
    }

    /**
     * @param $url
     *
     * @return mixed|void
     */
    private function getPendingInvoiceCount(
        $url
    ) {
        // init curl
        $ch = curl_init($url);

        $response = $this->callAPI(
            $ch,
            'GET'
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result;
    }
}
