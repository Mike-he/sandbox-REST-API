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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Rs\Json\Patch;

/**
 * Rest controller for UserEducation
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 * @Route("/profile")
 */
class ClientUserEducationController extends UserProfileController
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
     * @Route("/education")
     * @Method({"GET"})
     *
     * @return array
     */
    public function getUserEducationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = (int) $paramFetcher->get('user_id');
        if (is_null($userId)) {
            $userId = $this->getUserid();
        }

        $userEducation = $this->getRepo('User\UserEducation')->findByUserId($userId);

        return new View($userEducation);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Route("/education")
     * @Method({"POST"})
     * @return View
     */
    public function postUserEducationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher

    ) {
        $userId = $this->getUserid();
        $userEducation = new UserEducation();

        $form = $this->createForm(new UserEducationType(), $userEducation);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $userEducation->setUserId($userId);
            $em = $this->getDoctrine()->getManager();
            $em->persist($userEducation);
            $em->flush();

            return new View($userEducation->getId());
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/education/{id}")
     * @Method({"PATCH"})
     * @return View
     */
    public function patchUserEducationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $userEducation = $this->getRepo('User\UserEducation')->findOneById($id);
        $this->throwNotFoundIfNull($userEducation, self::NOT_FOUND_MESSAGE);

        $userEducationJSON = $this->container->get('serializer')->serialize($userEducation, 'json');
        $patch = new Patch($userEducationJSON, $request->getContent());
        $userEducationJSON = $patch->apply();

        $form = $this->createForm(new UserEducationType(), $userEducation);
        $form->submit(json_decode($userEducationJSON, true));

        if ($form->isValid()) {
            $userEducation->setModificationDate(new \DateTime("now"));
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
     * @Route("/education")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteUserEducationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userEducationIds = $paramFetcher->get('id');
        $this->getRepo('User\UserEducation')->deleteUserEducationsByIds($userEducationIds);

        return new View();
    }
}
