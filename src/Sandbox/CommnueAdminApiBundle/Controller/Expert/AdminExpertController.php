<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Expert;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminExpertController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="banned",
     *     array=false,
     *     nullable=true,
     *     default=null,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="name",
     *     array=false,
     *     nullable=true,
     *     default=null,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="phone",
     *     array=false,
     *     nullable=true,
     *     default=null,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true
     * )
     *
     * @Route("/experts")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getExpertsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $banned = (bool) $paramFetcher->get('banned');
        $name = $paramFetcher->get('name');
        $phone = $paramFetcher->get('phone');
        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');

        $experts = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->getAdminExperts(
                $banned,
                $name,
                $phone
            );

        return new View($experts);
    }
}
