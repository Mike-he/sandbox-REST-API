<?php

namespace Sandbox\SalesApiBundle\Controller\Log;

use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Log\LogController;
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
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
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
                $salesCompanyId,
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
     * @param $id
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
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $em = $this->getDoctrine()->getManager();

        $log = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Log\Log')
            ->findOneBy([
                'salesCompanyId' => $salesCompanyId,
                'id' => $id,
            ]);
        $this->throwNotFoundIfNull($log, self::NOT_FOUND_MESSAGE);

        $logJson = $this->container->get('serializer')->serialize($log, 'json');
        $patch = new Patch($logJson, $request->getContent());
        $logJson = $patch->apply();

        $form = $this->createForm(new LogPatchType(), $log);
        $form->submit(json_decode($logJson, true));

        if (!$log->isMark()) {
            $log->setRemarks(null);
        }

        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/logs/{id}/diff")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLogJsonDiffAction(
        Request $request,
        $id
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $log = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Log\Log')
            ->findOneBy([
                'salesCompanyId' => $salesCompanyId,
                'id' => $id,
            ]);
        $this->throwNotFoundIfNull($log, self::NOT_FOUND_MESSAGE);

        $view = new View();

        $previousLog = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Log\Log')
            ->getPreviousLog(
                $id,
                $log->getLogModule(),
                $log->getLogObjectKey(),
                $log->getLogObjectId()
            );
        if (is_null($previousLog)) {
            return $view;
        }

        $currentJson = $log->getLogObjectJson();
        $oldJson = $previousLog->getLogObjectJson();
        $currentArray = json_decode($currentJson, true);
        $oldArray = json_decode($oldJson, true);

        // compare old against new log
        $result = $this->arrayRecursiveDiff($currentArray, $oldArray);

        // compare new against new log
        $currentResult = $this->arrayRecursiveCompare($currentArray, $oldArray);

        $result = array_merge($result, $currentResult);

        $view->setData($result);

        return $view;
    }

    /**
     * get current element that does not exist in old.
     *
     * @param $currentArray
     * @param $oldArray
     *
     * @return array
     */
    private function arrayRecursiveCompare($currentArray, $oldArray)
    {
        $diffArray = [];

        foreach ($currentArray as $key => $val) {
            if (!array_key_exists($key, $oldArray)) {
                $diffArray[$key] = '';
            }
        }

        return $diffArray;
    }

    /**
     * get old elements that were changed and don't exist in current log.
     *
     * @param $currentArray
     * @param $oldArray
     *
     * @return array
     */
    private function arrayRecursiveDiff($currentArray, $oldArray)
    {
        $diffArray = [];

        foreach ($oldArray as $key => $val) {
            if (array_key_exists($key, $currentArray)) {
                if (is_array($val)) {
                    $aRecursiveDiff = $this->arrayRecursiveDiff($currentArray[$key], $val);

                    if (!empty($aRecursiveDiff)) {
                        $diffArray[$key] = $aRecursiveDiff;
                    }
                } elseif (strcmp($val, $currentArray[$key]) !== 0) {
                    $diffArray[$key] = $val;
                }
            } else {
                $diffArray[$key] = $val;
            }
        }

        return $diffArray;
    }
}
