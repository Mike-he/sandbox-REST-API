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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Rs\Json\Patch;

/**
 * Rest controller for UserExperience
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 * @Route("/profile")
 */
class ClientUserExperienceController extends UserProfileController
{
    /**
     * Get a single Profile.
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
     * @Route("/experience")
     * @Method({"GET"})
     *
     * @return array
     */
    public function getUserExperienceAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = (int) $paramFetcher->get('user_id');
        if (is_null($userId)) {
            $userId = $this->getUserid();
        }

        $userExperience = $this->getRepo('User\UserExperience')->findByUserId($userId);

        return new View($userExperience);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Route("/experience")
     * @Method({"POST"})
     * @return View
     */
    public function postUserExperienceAction(
        Request $request,
        ParamFetcherInterface $paramFetcher

    ) {
        $userId = $this->getUserid();
        $userExperience = new UserExperience();

        $form = $this->createForm(new UserExperienceType(), $userExperience);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $userExperience->setUserId($userId);
            $em = $this->getDoctrine()->getManager();
            $em->persist($userExperience);
            $em->flush();

            return new View($userExperience->getId());
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/experience/{id}")
     * @Method({"PATCH"})
     * @return View
     */
    public function patchUserExperienceAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $userExperience = $this->getRepo('User\UserExperience')->findOneById($id);
        $this->throwNotFoundIfNull($userExperience, self::NOT_FOUND_MESSAGE);

        $userExperienceJSON = $this->container->get('serializer')->serialize($userExperience, 'json');
        $patch = new Patch($userExperienceJSON, $request->getContent());
        $userExperienceJSON = $patch->apply();

        $form = $this->createForm(new UserExperienceType(), $userExperience);
        $form->submit(json_decode($userExperienceJSON, true));

        if ($form->isValid()) {
            $userExperience->setModificationDate(new \DateTime("now"));
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return new View();
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
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
     * @Route("/experience")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteUserExperienceAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userExperienceIds = $paramFetcher->get('id');
        $this->getRepo('User\UserExperience')->deleteUserExperiencesByIds($userExperienceIds);

        return new View();
    }
}
