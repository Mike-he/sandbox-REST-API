<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Form\User\UserProfileType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Rs\Json\Patch;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        // my user
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // get user id
        $userId = $paramFetcher->get('user_id');
        if (is_null($userId)) {
            $userId = $this->getUserId();
        }

        // get xmpp username
        $xmppUsername = $paramFetcher->get('xmpp_username');

        // get request user
        $user = null;

        if (is_null($xmppUsername)) {
            $user = $this->getRepo('User\User')->find($userId);
        } else {
            $user = $this->getRepo('User\User')->findOneByXmppUsername($xmppUsername);
        }

        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        // check the other user is banned or unauthorized
        if ($myUserId != $userId &&
            ($user->isBanned() || !$user->isAuthorized())) {
            return new View();
        }

        // get profile
        $profile = $this->getRepo('User\UserProfile')->findOneByUser($user);
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        $viewGroup = 'profile_basic';

        // set profile with view group
        if ($this->getUserId() != $userId) {
            $viewGroup = $this->setProfileWithViewGroup(
                $myUser,
                $user,
                $profile,
                $viewGroup
            );
        }

        // set view
        $view = new View($profile);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array($viewGroup))
        );

        return $view;
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
        // get profile
        $profile = $this->getRepo('User\UserProfile')->findOneByUserId(
            $this->getUserId()
        );
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        return $this->handleUserBasicProfilePatch(
            $request,
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
        // get profile
        $profile = $this->getRepo('User\UserProfile')->find($id);
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        // check user is allowed to modify
        if ($this->getUserId() != $profile->getUserId()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        return $this->handleUserBasicProfilePatch(
            $request,
            $profile
        );
    }

    /**
     * @param Request     $request
     * @param UserProfile $profile
     *
     * @return View
     *
     * @throws Patch\FailedTestException
     */
    private function handleUserBasicProfilePatch(
        Request $request,
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

        return new View();
    }
}
