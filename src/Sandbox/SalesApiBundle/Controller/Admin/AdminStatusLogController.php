<?php

namespace Sandbox\SalesApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * AdminStatusLog controller.
 *
 * @category Sandbox
 *
 * @author   Feng Li <feng.li@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminStatusLogController extends SandboxRestController
{
    /**
     * List admin status logs.
     *
     * @param Request $request the request object
     * @param $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="object",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    description="object name"
     * )
     *
     * @Annotations\QueryParam(
     *    name="object_id",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    description="object id"
     * )
     *
     * @Method({"GET"})
     * @Route("/status/logs")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getSalesAdminStatusLogsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $object = $paramFetcher->get('object');
        $objectId = $paramFetcher->get('object_id');

        $adminStatusLogs = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminStatusLog')
            ->findBy(
                [
                    'object' => $object,
                    'objectId' => $objectId,
                ],
                ['creationDate' => 'ASC']
            );

        return new View($adminStatusLogs);
    }
}
