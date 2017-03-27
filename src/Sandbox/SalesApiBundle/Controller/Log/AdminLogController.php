<?php

namespace Sandbox\SalesApiBundle\Controller\Log;

use Knp\Component\Pager\Paginator;
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
 * @author   Mike He <mike.he@sandbox3.cn>
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
        $this->checkAdminLogPermission(AdminPermission::OP_LEVEL_VIEW);

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

        $logsQuery = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Log\Log')
            ->getAdminLogs(
                null,
                $startDate,
                $endDate,
                $companyId,
                $module,
                $search,
                $key,
                $objectId,
                $mark
            );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $logsQuery,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Check user permission.
     */
    private function checkAdminLogPermission(
        $OpLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE],
            ],
            $OpLevel
        );
    }
}
