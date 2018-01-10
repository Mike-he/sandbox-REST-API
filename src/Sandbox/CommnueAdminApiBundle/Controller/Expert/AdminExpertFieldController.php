<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Expert;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Expert\ExpertField;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminExpertFieldController extends SandboxRestController
{
    const ERROR_CAN_NOT_DELETE_CODE = 400001;
    const ERROR_CAN_NOT_DELETE_MESSAGE = 'Can not delete';
    const ERROR_OVER_LIMIT_CODE = 400002;
    const ERROR_OVER_LIMIT_MESSAGE = 'Over limit';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/expert/fields")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postExpertFieldAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['description'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $currentExpertFields = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\ExpertField')
            ->findAll();

        if (count($currentExpertFields) >= 3) {
            return $this->customErrorView(
                400,
                self::ERROR_OVER_LIMIT_CODE,
                self::ERROR_OVER_LIMIT_MESSAGE
            );
        }

        $expertField = new ExpertField();
        $expertField->setName($data['name']);
        $expertField->setDescription($data['description']);

        $em = $this->getDoctrine()->getManager();
        $em->persist($expertField);
        $em->flush();

        return new View(['id' => $expertField->getId()], '201');
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/expert/fields")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getExpertFieldsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $currentExpertFields = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\ExpertField')
            ->findAll();

        return new View($currentExpertFields);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/expert/fields/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteExpertFieldAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $expertField = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\ExpertField')
            ->find($id);
        $this->throwNotFoundIfNull($expertField, self::NOT_FOUND_MESSAGE);

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->checkExpertField($expertField->getId());

        if (count($expert) > 0) {
            return $this->customErrorView(
                400,
                self::ERROR_CAN_NOT_DELETE_CODE,
                self::ERROR_CAN_NOT_DELETE_MESSAGE
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($expertField);
        $em->flush();

        return new View();
    }
}
