<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Traits\FinanceSalesExportTraits;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Finance Summary Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class AdminFinanceSummaryController extends PaymentController
{
    use FinanceSalesExportTraits;

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
     *    name="year",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="year"
     * )
     *
     * @Method({"GET"})
     * @Route("/finance/summary")
     *
     * @return View
     */
    public function getFinanceSummaryAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->checkAdminSalesFinanceSummaryPermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $year = $paramFetcher->get('year');
        if (is_null($year) || empty($year)) {
            $now = new \DateTime();
            $year = $now->format('Y');
        }

        $yearStart = new \DateTime("$year-01-01 00:00:00");
        $yearEnd = new \DateTime("$year-12-31 23:59:59");

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $offset = ($pageIndex - 1) * $pageLimit;

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSummary')
            ->countFinanceSummary(
                $salesCompanyId,
                $yearStart,
                $yearEnd
            );

        $summary = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSummary')
            ->getFinanceSummary(
                $salesCompanyId,
                $yearStart,
                $yearEnd,
                $pageLimit,
                $offset
            );

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $summary,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Method({"GET"})
     * @Route("/finance/summary/current")
     *
     * @return View
     */
    public function getCurrentFinanceSummaryAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->checkAdminSalesFinanceSummaryPermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $now = new \DateTime();
        $start = clone $now;
        $start->modify('first day of this month');
        $start->setTime(0, 0, 0);

        $summary = $this->getShortRentAndLongRentArray(
            $salesCompanyId,
            $start,
            $now
        );

        $summary['current_month'] = $now->format('m');

        $view = new View();
        $view->setData($summary);

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Method({"GET"})
     * @Route("/finance/summary/years")
     *
     * @return View
     */
    public function getSummaryYearsAction(
        Request $request
    ) {
        $this->checkAdminSalesFinanceSummaryPermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $years = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSummary')
            ->getFinanceSummaryYear($salesCompanyId);

        $yearArray = [];
        foreach ($years as $year) {
            $yearString = $year['creationDate']->format('Y');

            if (in_array($yearString, $yearArray)) {
                continue;
            }
            array_push($yearArray, $yearString);
        }

        return new View(['years' => $yearArray]);
    }

    /**
     * @param Request $request
     *
     * @Method({"GET"})
     * @Route("/finance/summary/counts")
     *
     * @return View
     */
    public function getSummaryNumberCountsAction(
        Request $request
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $url = $this->getParameter('crm_api_url').'/sales/admin/invoices/count?status[]=pending&status[]=cancelled_wait';
        $invoiceCount = $this->getPendingInvoiceCount($url);

        $billCount = (int) $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBillByCompany(
                LeaseBill::STATUS_VERIFY,
                $salesCompanyId
            );

        $shortRentAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoice')
            ->sumPendingShortRentInvoices($salesCompanyId);
        if (is_null($shortRentAmount)) {
            $shortRentAmount = 0;
        }

        //get long rent amount
        $longRentAmount = 0;
        $longRent = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSalesWallet')
            ->findOneBy(['companyId' => $salesCompanyId]);
        if (!is_null($longRent)) {
            $longRentAmount = $longRent->getBillAmount();
        }

        $pendingLongRent = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')
            ->sumBillAmount(
                $salesCompanyId,
                FinanceLongRentBill::STATUS_PENDING
            );
        if (is_null($pendingLongRent)) {
            $pendingLongRent = 0;
        }

        $longRentAmount = $longRentAmount - $pendingLongRent;

        $view = new View();
        $view->setData([
            'long_rent_amount' => (float) $longRentAmount,
            'short_rent_amount' => (float) $shortRentAmount,
            'user_invoice_count' => $invoiceCount,
            'offline_verify_count' => $billCount,
        ]);

        return $view;
    }

    /**
     * @param $salesCompanyId
     * @param $start
     * @param $end
     *
     * @return array
     */
    private function getShortRentAndLongRentArray(
        $salesCompanyId,
        $start,
        $end
    ) {
        // short rent orders
        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getCompletedOrders(
                $start,
                $end,
                $salesCompanyId
            );

        $amount = 0;
        foreach ($orders as $order) {
            $amount += $order['discountPrice'] * (1 - $order['serviceFee'] / 100);
        }

        // long rent orders
        $longBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBillsByDates(
                $start,
                $end,
                $salesCompanyId
            );

        $serviceAmount = 0;
        $incomeAmount = 0;
        foreach ($longBills as $longBill) {
            $incomeAmount += $longBill->getRevisedAmount();

            $serviceBill = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
                ->findOneBy([
                    'orderNumber' => $longBill->getSerialNumber(),
                ]);
            if (!is_null($serviceBill)) {
                $serviceAmount += $serviceBill->getAmount();
            }
        }

        // event orders
        $events = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getSumEventOrders(
                $start,
                $end,
                $salesCompanyId
            );

        $eventBalance = 0;
        foreach ($events as $event) {
            $eventBalance += $event['price'];
        }

        $membershipOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getMembershipOrdersByDate(
                $start,
                $end,
                $salesCompanyId
            );

        $membershipBalance = 0;
        foreach ($membershipOrders as $membershipOrder) {
            $membershipBalance += $membershipOrder->getPrice() * (1 - $membershipOrder->getServiceFee() / 100);
        }

        $serviceOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->getServiceOrdersByDate(
                $start,
                $end,
                $salesCompanyId
            );

        $serviceOrderBalance = 0;
        foreach ($serviceOrders as $serviceOrder) {
            $serviceOrderBalance += $serviceOrder->getPrice();
        }

        $summaryArray = [
            'total_income' => $amount + $incomeAmount + $eventBalance + $membershipBalance + $serviceOrderBalance,
            'short_rent_balance' => $amount,
            'long_rent_balance' => $incomeAmount,
            'event_order_balance' => $eventBalance,
            'total_service_bill' => $serviceAmount,
            'long_rent_service_bill' => $serviceAmount,
            'membership_order_balance' => $membershipBalance,
            'service_order_balance' => $serviceOrderBalance,
        ];

        return $summaryArray;
    }

    /**
     * @param $adminId
     * @param $opLevel
     */
    private function checkAdminSalesFinanceSummaryPermission(
        $adminId,
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            [
                ['key' => AdminPermission::KEY_SALES_PLATFORM_FINANCIAL_SUMMARY],
            ],
            $opLevel
        );
    }

    /**
     * @param $url
     * @param null $auth
     *
     * @return mixed|void
     */
    private function getPendingInvoiceCount(
        $url,
        $auth = null
    ) {
        if (is_null($auth)) {
            // get auth
            $headers = array_change_key_case($_SERVER, CASE_LOWER);
            $auth = $headers['http_authorization'];
        }

        // init curl
        $ch = curl_init($url);

        $response = $this->callAPI(
            $ch,
            'GET',
            array('Authorization: '.$auth)
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (self::HTTP_STATUS_OK != $httpCode) {
            return;
        }

        $result = json_decode($response, true);

        return $result;
    }
}
