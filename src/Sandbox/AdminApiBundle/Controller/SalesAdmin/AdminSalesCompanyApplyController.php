<?php

namespace Sandbox\AdminApiBundle\Controller\SalesAdmin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyApply;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesCompanyApplyPostType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Controller\Annotations;

class AdminSalesCompanyApplyController extends SalesRestController
{
    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/company/application")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postSalesCompanyApplyAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $salesCompanyApply = new SalesCompanyApply();
        $form = $this->createForm(new SalesCompanyApplyPostType(), $salesCompanyApply);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $salesCompanyApply->setUserId($userId);

        $em = $this->getDoctrine()->getManager();
        $em->persist($salesCompanyApply);
        $em->flush();

        return new View([
            'id' => $salesCompanyApply->getId(),
        ], '201');
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="status",
     *     array=false,
     *     nullable=false,
     *     strict=true
     * )
     *
     * @Route("/company/applications")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesCompanyApplicationsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $status = $paramFetcher->get('status');

        $salesCompanyApply = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyApply')
            ->findBy([
                'status' => $status,
            ]);

        return new View($salesCompanyApply);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/company/applications/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesCompanyApplicationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $salesCompanyApply = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyApply')
            ->find($id);

        return new View($salesCompanyApply);
    }
}