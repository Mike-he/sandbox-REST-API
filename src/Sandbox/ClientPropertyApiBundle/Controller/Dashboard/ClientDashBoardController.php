<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Dashboard;


use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class ClientDashBoardController extends SandboxRestController
{

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/dashboard")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBuildingsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
       $result = ['id'=> 1];

        $view = new View();
        $view->setData($result);

        return $view;
    }

}
