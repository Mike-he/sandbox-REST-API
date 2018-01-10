<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\User\UserEducation;
use Sandbox\ApiBundle\Form\User\UserEducationType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Rs\Json\Patch;
use JMS\Serializer\SerializationContext;

/**
 * Rest controller for UserEducation.
 *
 * @category Sandbox
 *
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 * @Route("/profile")
 */
class ClientUserEducationController extends UserProfileController
{
    /**
     * Get user's education.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="user_id",
     *    default=null,
     *    description="userId"
     * )
     *
     * @Route("/educations")
     * @Method({"GET"})
     *
     * @return array
     */
    public function getUserEducationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $paramFetcher->get('user_id');
        if (is_null($userId)) {
            $userId = $this->getUserId();
        }

        $educations = $this->getRepo('User\UserEducation')->findByUserId($userId);

        $view = new View($educations);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('profile')));

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Route("/educations")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postUserEducationAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $user = $this->getRepo('User\User')->find($userId);

        $em = $this->getDoctrine()->getManager();

        $educations = json_decode($request->getContent(), true);
        foreach ($educations as $education) {
            $userEducation = $this->generateUserEducation($user, $education);
            $em->persist($userEducation);
        }

        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/educations/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchUserEducationAction(
        Request $request,
        $id
    ) {
        // get education
        $education = $this->getRepo('User\UserEducation')->find($id);
        $this->throwNotFoundIfNull($education, self::NOT_FOUND_MESSAGE);

        // check user is allowed to modify
        if ($this->getUserId() != $education->getUser()->getId()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // bind data
        $educationJson = $this->container->get('serializer')->serialize($education, 'json');
        $patch = new Patch($educationJson, $request->getContent());
        $educationJson = $patch->apply();

        $form = $this->createForm(new UserEducationType(), $education);
        $form->submit(json_decode($educationJson, true));

        // set education
        $education->setModificationDate(new \DateTime('now'));

        // update to db
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    strict=true,
     *    description=""
     * )
     *
     * @Route("/educations")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteUserEducationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->getRepo('User\UserEducation')->deleteUserEducations(
            $paramFetcher->get('id'),
            $this->getUserId()
        );

        return new View();
    }
}
