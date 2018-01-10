<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\User\UserExperience;
use Sandbox\ApiBundle\Form\User\UserExperienceType;
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
 * Rest controller for UserExperience.
 *
 * @category Sandbox
 *
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 * @Route("/profile")
 */
class ClientUserExperienceController extends UserProfileController
{
    /**
     * Get user's experience.
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
     * @Route("/experiences")
     * @Method({"GET"})
     *
     * @return array
     */
    public function getUserExperienceAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $paramFetcher->get('user_id');
        if (is_null($userId)) {
            $userId = $this->getUserId();
        }

        $experiences = $this->getRepo('User\UserExperience')->findByUserId($userId);

        $view = new View($experiences);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('profile')));

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Route("/experiences")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postUserExperienceAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $user = $this->getRepo('User\User')->find($userId);

        $em = $this->getDoctrine()->getManager();

        $experiences = json_decode($request->getContent(), true);
        foreach ($experiences as $experience) {
            $userExperience = $this->generateUserExperience($user, $experience);
            $em->persist($userExperience);
        }

        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/experiences/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchUserExperienceAction(
        Request $request,
        $id
    ) {
        // get experience
        $experience = $this->getRepo('User\UserExperience')->find($id);
        $this->throwNotFoundIfNull($experience, self::NOT_FOUND_MESSAGE);

        // check user is allowed to modify
        if ($this->getUserId() != $experience->getUser()->getId()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // bind data
        $experienceJson = $this->container->get('serializer')->serialize($experience, 'json');
        $patch = new Patch($experienceJson, $request->getContent());
        $experienceJson = $patch->apply();

        $form = $this->createForm(new UserExperienceType(), $experience);
        $form->submit(json_decode($experienceJson, true));

        // set experience
        $experience->setModificationDate(new \DateTime('now'));

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
     * @Route("/experiences")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteUserExperienceAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->getRepo('User\UserExperience')->deleteUserExperiences(
            $paramFetcher->get('id'),
            $this->getUserId()
        );

        return new View();
    }
}
