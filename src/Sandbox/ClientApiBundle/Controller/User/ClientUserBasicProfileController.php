<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Form\User\UserProfileType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Rs\Json\Patch;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="ids",
     *     array=true,
     *     strict=true
     * )
     *
     * @Route("/open_user")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getOpenUserAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $ids = $paramFetcher->get('ids');

        $users = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->getUsersByIds($ids);

        return new View($users);
    }

    /**
     * Get user's basic profile.
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
     * @Annotations\QueryParam(
     *    name="xmpp_username",
     *    default=null,
     *    description="xmppUsername"
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
        return $this->handleGetUserProfile($paramFetcher, false);
    }

    /**
     * @param Request $request
     *
     * @Route("/basic")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchUserBasicProfileAction(
        Request $request
    ) {
        // get user
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // get profile
        $profile = $this->getRepo('User\UserProfile')->findOneByUser($myUser);
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        return $this->handleUserBasicProfilePatch(
            $request,
            $myUser,
            $profile
        );
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/basic/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchUserBasicProfileWithIdAction(
        Request $request,
        $id
    ) {
        // get user
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // get profile
        $profile = $this->getRepo('User\UserProfile')->find($id);
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        // check user is allowed to modify
        if ($myUserId != $profile->getUserId()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        return $this->handleUserBasicProfilePatch(
            $request,
            $myUser,
            $profile
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/avatar")
     * @Method({"POST"})
     *
     * @return View
     */
    public function sendAvatarAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $auth = $request->headers->get(self::HTTP_HEADER_AUTH);

        $data = json_decode($request->getContent(), true);

        if (!array_key_exists('avatar_url', $data)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return new View();
    }



    /**
     * @param Request     $request
     * @param User        $user
     * @param UserProfile $profile
     *
     * @return View
     *
     * @throws Patch\FailedTestException
     */
    private function handleUserBasicProfilePatch(
        Request $request,
        $user,
        $profile
    ) {
        // bind data
        $profileJson = $this->container->get('serializer')->serialize($profile, 'json');
        $patch = new Patch($profileJson, $request->getContent());
        $profileJson = $patch->apply();

        $form = $this->createForm(new UserProfileType(), $profile);
        $form->submit(json_decode($profileJson, true));

        // set profile
        $profile->setModificationDate(new \DateTime('now'));

        // update to db
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->get('sandbox_api.jmessage')
            ->updateNickname(
                $user->getXmppUsername(),
                $profile->getName()
            );

        return new View();
    }
}
