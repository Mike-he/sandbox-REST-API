<?php

namespace Sandbox\ApiBundle\Controller\RpcSample;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class RpcSampleController extends SandboxRestController
{
    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/rpc_sample")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getRpcSampleAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $result = $this->get('sandbox_rpc.client')->callRpcServer(
            $this->getParameter('rpc_server_test'),
            'TestService.sayHelloName',
            ['Mike']
        );

        return new View($result);
    }
}