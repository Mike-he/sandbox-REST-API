<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Expert;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Expert\Expert;
use Sandbox\ApiBundle\Form\Expert\ExpertPostType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
        Request $request
    ) {
        $user = $this->getUser();

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->findOneBy(array('userId' => $user->getUserId()));

        $response = array();
        if ($expert) {
            $response['status'] = true;
            $response['banned'] = $expert->isBanned();
        } else {
            $response['status'] = false;
        }

        return new View($response);
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
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $expert = new Expert();

        $form = $this->createForm(new ExpertPostType(), $expert);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $requestContent = json_decode($request->getContent(), true);

        $fieldIds = $requestContent['field_ids'];

        foreach ($fieldIds as $fieldId) {

        }

        $expert->setUserId($user->getUserId());
        $em->persist($expert);

        $em->flush();

        $response = array(
            'id' => $expert->getId(),
        );

        return new View($response, 201);
    }
}
