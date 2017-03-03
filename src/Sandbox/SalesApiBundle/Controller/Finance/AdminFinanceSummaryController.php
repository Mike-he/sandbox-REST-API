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
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * Admin Finance Summary Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
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

        $adminPlatform = $this->getAdminPlatform();
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

        $adminPlatform = $this->getAdminPlatform();
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

        $adminPlatform = $this->getAdminPlatform();
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
        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $invoices = $this->getSalesAdminInvoices();
        $invoiceCount = (int) $invoices['total_count'];

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
     * @Annotations\QueryParam(
     *    name="summary_id",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="summary id"
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
     * @Route("/finance/summary/export")
     *
     * @return View
     */
    public function getFinanceSummaryExportAction(
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

        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            [
                ['key' => AdminPermission::KEY_SALES_PLATFORM_FINANCIAL_SUMMARY],
            ],
            AdminPermission::OP_LEVEL_VIEW,
            $adminPlatform->getPlatform(),
            $companyId
        );

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $companyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $language = $paramFetcher->get('language');
        $id = $paramFetcher->get('summary_id');

        $summary = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSummary')
            ->findOneBy([
                'id' => $id,
                'companyId' => $companyId,
            ]);
        $this->throwNotFoundIfNull($summary, self::NOT_FOUND_MESSAGE);

        $lastDate = $summary->getSummaryDate();
        $firstDate = clone $lastDate;
        $firstDate->modify('first day of this month');
        $firstDate->setTime(0, 0, 0);

        // event orders
        $events = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getEventOrderSummary(
                $firstDate,
                $lastDate,
                $companyId
            );

        $shortOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getCompletedOrderSummary(
                $firstDate,
                $lastDate,
                $companyId
            );

        $longBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBillsByDates(
                $firstDate,
                $lastDate,
                $companyId
            );

        return $this->getFinanceSummaryExport(
            $firstDate,
            $language,
            $events,
            $shortOrders,
            $longBills
        );
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
                    'bill' => $longBill,
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

        $summaryArray = [
            'total_income' => $amount + $incomeAmount + $eventBalance,
            'short_rent_balance' => $amount,
            'long_rent_balance' => $incomeAmount,
            'event_order_balance' => $eventBalance,
            'total_service_bill' => $serviceAmount,
            'long_rent_service_bill' => $serviceAmount,
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            [
                ['key' => AdminPermission::KEY_SALES_PLATFORM_FINANCIAL_SUMMARY],
            ],
            $opLevel
        );
    }
}
