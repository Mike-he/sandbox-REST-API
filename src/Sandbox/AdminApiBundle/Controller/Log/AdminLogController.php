<?php

namespace Sandbox\AdminApiBundle\Controller\Log;

use Rs\Json\Patch;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\Log\LogController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Form\Log\LogPatchType;
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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="admin_id",
     *     array=false,
     *     default=null,
     *     nullable=false,
     *     strict=true,
     *     description="admin id"
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
     * @Route("/logs/specify_admin")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLogsBySpecifyAdminAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminLogPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminId = $paramFetcher->get('admin_id');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');

        $logsQuery = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Log\Log')
            ->getAdminLogs(
                $adminId,
                $startDate,
                $endDate
            );

        return new View($logsQuery->getResult());
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
        $this->checkAdminLogPermission(AdminPermission::OP_LEVEL_VIEW);

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
     * @Route("/logs/{id}/mark")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchLogMarkAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLogPermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        $log = $em->getRepository('SandboxApiBundle:Log\Log')->find($id);
        $this->throwNotFoundIfNull($log, self::NOT_FOUND_MESSAGE);

        $logJson = $this->container->get('serializer')->serialize($log, 'json');
        $patch = new Patch($logJson, $request->getContent());
        $logJson = $patch->apply();

        $form = $this->createForm(new LogPatchType(), $log);
        $form->submit(json_decode($logJson, true));

        $mark = $log->isMark();
        if ($mark == false) {
            $log->setRemarks(null);
        }

        $em->persist($log);
        $em->flush();
    }

    /**
     * Check user permission.
     */
    private function checkAdminLogPermission(
        $OpLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LOG],
            ],
            $OpLevel
        );
    }
}
