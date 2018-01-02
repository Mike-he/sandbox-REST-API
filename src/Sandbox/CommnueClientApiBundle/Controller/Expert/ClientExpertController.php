<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Expert;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Entity\Expert\Expert;
use Sandbox\ApiBundle\Form\Expert\ExpertPostType;
use Sandbox\ApiBundle\Traits\UserIdCardTraits;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClientExpertController extends SalesRestController
{
    use UserIdCardTraits;

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

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->findOneBy(array('userId' => $user->getUserId()));

        if ($expert) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_EXPERT_HAS_CREATED_CODE,
                CustomErrorMessagesConstants::ERROR_EXPERT_HAS_CREATED_MESSAGE
            );
        }

        $expert = new Expert();
        $expert->setUserId($user->getUserId());

        $form = $this->createForm(new ExpertPostType(), $expert);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $requestContent = json_decode($request->getContent(), true);

        $userInfo = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($user->getUserId());
        if ($userInfo->getCredentialNo()) {
            $expert->setCredentialNo($userInfo->getCredentialNo());
        } else {
            $check = $this->checkIDCardValidation(
                $expert->getName(),
                $expert->getCredentialNo()
            );

            if (!$check) {
                return $this->customErrorView(
                    400,
                    CustomErrorMessagesConstants::ERROR_ID_CARD_AUTHENTICATION_FAILURE_CODE,
                    CustomErrorMessagesConstants::ERROR_ID_CARD_AUTHENTICATION_FAILURE_MESSAGE
                );
            }
        }

        $fieldIds = $requestContent['field_ids'];

        foreach ($fieldIds as $fieldId) {
            $field = $this->getDoctrine()->getRepository('SandboxApiBundle:Expert\ExpertField')->find($fieldId);
            if ($field) {
                $expert->addExpertFields($field);
            }
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
