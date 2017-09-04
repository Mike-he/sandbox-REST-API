<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Paginator;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class AdminFinanceSalesWalletController extends SalesRestController
{
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
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Route("/finance/wallet_flows")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getWalletFlowsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');

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

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $flows,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/wallet_flows/dashboard")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesWalletFlowsDashboardAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $now = new \DateTime('now');

        $todayStartDate = clone $now;
        $todayStartDate = $todayStartDate->setTime('00', '00', '01');
        $todayEndDate = $now;

        $today = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSalesWalletFlow')
            ->getAdminWalletInput(
                $salesCompanyId,
                $todayStartDate,
                $todayEndDate
            );

        $yesterdayStartDate = clone $now;
        $yesterdayStartDate = $yesterdayStartDate->modify('-1 day');
        $yesterdayEndDate = clone $yesterdayStartDate;
        $yesterdayStartDate = $yesterdayStartDate->setTime('00', '00', '01');
        $yesterdayEndDate = $yesterdayEndDate->setTime('23', '59', '59');

        $yesterday = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSalesWalletFlow')
            ->getAdminWalletInput(
                $salesCompanyId,
                $yesterdayStartDate,
                $yesterdayEndDate
            );

        $lastMonthDate = clone $now;
        $year = $lastMonthDate->format('Y');
        $month = $lastMonthDate->format('m');

        $startString = $year.'-'.$month.'-01';
        $startDate = new \DateTime($startString);

        $currentMonth = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSalesWalletFlow')
            ->getAdminWalletInput(
                $salesCompanyId,
                $startDate,
                $now
            );

        return new View([
            'today' => $today,
            'yesterday' => $yesterday,
            'current_month' => $currentMonth,
        ]);
    }
}
