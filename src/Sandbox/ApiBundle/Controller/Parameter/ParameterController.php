<?php

namespace Sandbox\ApiBundle\Controller\Parameter;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Form\Parameter\ParameterPatchType;

/**
 * Parameter Controller.
 *
 * @category Sandbox
 *
 * @author   Feng Li <feng.li@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ParameterController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Route("/parameter/all")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAllParameterAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $parameter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findAll();

        $view = new View();
        $view->setData($parameter);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="key",
     *    nullable=false,
     *    strict=true,
     *    description=""
     * )
     *
     * @Route("/parameter/by/key")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getParameterByKeyAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $key = $paramFetcher->get('key');

        $parameter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array('key' => $key));

        $view = new View();
        $view->setData($parameter);

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Annotations\QueryParam(
     *    name="key",
     *    nullable=false,
     *    strict=true,
     *    description=""
     * )
     *
     * @Route("/parameter/by/key")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchParameterAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $key = $paramFetcher->get('key');

        $parameter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array('key' => $key));

        $this->throwNotFoundIfNull($parameter, self::NOT_FOUND_MESSAGE);

        $parameterJson = $this->container->get('serializer')->serialize($parameter, 'json');
        $patch = new Patch($parameterJson, $request->getContent());
        $parameterJson = $patch->apply();

        $form = $this->createForm(new ParameterPatchType(), $parameter);
        $form->submit(json_decode($parameterJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }
}
