<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
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
 * @link     http://www.Sandbox.cn/
 */
class AdminFinanceSummaryController extends PaymentController
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
     * @Route("/finance/summary/years")
     *
     * @return View
     */
    public function getAction(
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
