<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Expert;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ClientExpertController extends SalesRestController
{
    /**
     * Check A Expert.
     *
     * @param $request
     *
     * @Route("/experts/check")
     * @Method({"GET"})
     *
     * @return View
     */
    public function checkExpertAction(

    ) {

    }

    /**
     * Create A Expert.
     *
     * @param $request
     *
     * @Route("/experts")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postExpertAction(
        Request $request
    ) {

        $view = new View();
        $view->setStatusCode(201);

        return $view;
    }
}