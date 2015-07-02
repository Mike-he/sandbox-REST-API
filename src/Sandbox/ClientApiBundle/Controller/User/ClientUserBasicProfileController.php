<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Form\User\UserProfileType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Rs\Json\Patch;

/**
 * Rest controller for user`s basic profile.
 *
 * @category Sandbox
 *
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 * @Route("/profile")
 */
class ClientUserBasicProfileController extends UserProfileController
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
     * @Route("/basic")
     * @Method({"GET"})
     *
     * @return array
     */
    public function getUserBasicProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = (int) $paramFetcher->get('user_id');
        if ($userId === 0) {
            $userId = $this->getUserid();
        }

        $userBasic = $this->getRepo('User\UserProfile')->findByUserId($userId);

        return new View($userBasic);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/basic/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchUserBasicProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $userBasicProfile = $this->getRepo('User\UserProfile')->findOneById($id);
        $this->throwNotFoundIfNull($userBasicProfile, self::NOT_FOUND_MESSAGE);

        $userBasicProfileJSON = $this->container->get('serializer')->serialize($userBasicProfile, 'json');
        $patch = new Patch($userBasicProfileJSON, $request->getContent());
        $userBasicProfileJSON = $patch->apply();

        $form = $this->createForm(new UserProfileType(), $userBasicProfileJSON);
        $form->submit(json_decode($userBasicProfileJSON, true));

        if ($form->isValid()) {
            $userBasicProfile->setModificationDate(new \DateTime('now'));
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return new View();
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }
}
