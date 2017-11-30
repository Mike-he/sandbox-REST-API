<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Advertising;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Advertising\CommnueAdvertisingMiddle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Rs\Json\Patch;

class AdminAdvertisingMiddleController extends SandboxRestController
{
    /**
     * Get Advertising Middle List
     *
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many banners to return per page"
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
     * @Route("/advertising/middiles")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdvertisingMiddlesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');

        $middles = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:CommnueAdvertisingMiddle')
            ->findAll();

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $middles,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get Advertising Middle By Id
     *
     * @param $id
     *
     * @Route("/advertising/middles/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdvertisingMiddleByIdAction(
        $id
    ) {
        $middle = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMiddle')
            ->find($id);

        $this->throwNotFoundIfNull($middle,self::NOT_FOUND_MESSAGE);

        return new View($middle);
    }

    public function postAdvertisingMiddleAction(
        Request $request
    ) {
        $middle = new CommnueAdvertisingMiddle();

    }
}