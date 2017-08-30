<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
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
            $startString,
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

        return $this->getFinanceSummaryExport(
            $startDate,
            $language,
            $events,
            $shortOrders,
            $membershipOrders
        );
    }
}
