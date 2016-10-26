<?php

namespace Sandbox\AdminApiBundle\Controller\Log;

use Sandbox\ApiBundle\Controller\Log\LogController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Log Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminLogController extends LogController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="module",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true,
     *     description="module name"
     * )
     *
     * @Annotations\QueryParam(
     *     name="search",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true,
     *     description="search"
     * )
     *
     * @Annotations\QueryParam(
     *     name="sales_company",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true,
     *     description="sales company id"
     * )
     *
     * @Annotations\QueryParam(
     *     name="object_key",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true,
     *     description="object key"
     * )
     *
     * @Annotations\QueryParam(
     *     name="object_id",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true,
     *     description="object id"
     * )
     *
     * @Annotations\QueryParam(
     *     name="mark",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true,
     *     description="mark"
     * )
     *
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="startDate"
     * )
     *
     * @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="endDate"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many admins to return "
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
     * @Route("/logs")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLogsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminLogPermission();

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $companyId = $paramFetcher->get('sales_company');
        $module = $paramFetcher->get('module');
        $search = $paramFetcher->get('search');
        $key = $paramFetcher->get('object_key');
        $objectId = $paramFetcher->get('object_id');
        $mark = $paramFetcher->get('mark');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');

        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Log\Log')
            ->getLogCount(
                $companyId,
                $module,
                $search,
                $key,
                $objectId,
                $mark,
                $startDate,
                $endDate
            );

        $logs = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Log\Log')
            ->getLogList(
                $companyId,
                $module,
                $search,
                $key,
                $objectId,
                $mark,
                $startDate,
                $endDate,
                $limit,
                $offset
            );

        foreach ($logs as $log) {
            $salesCompanyId = $log->getSalesCompanyId();

            if (is_null($salesCompanyId)) {
                continue;
            }

            $company = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
                ->find($salesCompanyId);

            if (is_null($company)) {
                continue;
            }

            $log->setSalesCompanyName($company->getName());
        }

        $view = new View();
        $view->setData(
            [
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $logs,
                'total_count' => (int) $count,
            ]
        );

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Route("/logs/modules")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLogModulesAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminLogPermission();

        $modules = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Log\LogModules')
            ->findBy(
                [],
                ['id' => 'DESC']
            );

        return new View($modules);
    }

    /**
     * @param Request $request
     *
     * @Route("/logs/mark")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postLogMarkAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminLogPermission();

        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);

        $id = $data['id'];
        $mark = $data['mark'];

        $log = $em->getRepository('SandboxApiBundle:Log\Log')->find($id);
        $this->throwNotFoundIfNull($log, self::NOT_FOUND_MESSAGE);

        $log->setMark($mark);
        if ($mark == true) {
            $log->setRemarks($data['remarks']);
        } else {
            $log->setRemarks(null);
        }

        $em->persist($log);
        $em->flush();
    }

    /**
     * Check user permission.
     */
    private function checkAdminLogPermission()
    {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LOG],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );
    }
}
