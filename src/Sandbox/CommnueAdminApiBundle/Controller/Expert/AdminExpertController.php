<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Expert;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Form\Expert\ExpertPatchType;
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

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $experts,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/experts/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getExpertAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->find($id);

        return new View($expert);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/experts/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchExpertAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->find($id);
        $this->throwNotFoundIfNull($expert, self::NOT_FOUND_MESSAGE);

        $expertJson = $this->container->get('serializer')->serialize($expert, 'json');
        $patch = new Patch($expertJson, $request->getContent());
        $expertJson = $patch->apply();

        $form = $this->createForm(new ExpertPatchType(), $expert);
        $form->submit(json_decode($expertJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }
}
