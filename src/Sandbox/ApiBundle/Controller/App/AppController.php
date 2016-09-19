<?php

namespace Sandbox\ApiBundle\Controller\App;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * APP Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AppController extends SandboxRestController
{
    /**
     * List all APP Info.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     *  @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(
     *    name="version",
     *    default=null,
     *    nullable=true,
     *    description="app version"
     * )
     *
     * @Annotations\QueryParam(
     *    name="app",
     *    default="sandbox",
     *    nullable=true,
     *    description="app key"
     * )
     *
     * @Method({"GET"})
     * @Route("/apps")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAppsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $version = $paramFetcher->get('version');
        $app = $paramFetcher->get('app');

        if (!is_null($version) && !empty($version)) {
            $apps = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:App\AppInfo')
                ->findBy(array(
                    'version' => $version,
                    'app' => $app,
                ));
        } else {
            $apps = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:App\AppInfo')
                ->findBy(array(
                    'app' => $app,
                ));
        }

        return new View($apps);
    }
}
