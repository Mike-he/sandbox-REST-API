<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\Buddy\BuddyRequest;
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
        $userId = $paramFetcher->get('user_id');
        if (is_null($userId)) {
            $userId = $this->getUserId();
        }
        $user = $this->getRepo('User\User')->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        // get profile
        $profile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $viewGroup = 'profile_basic';

        // if user is not my buddy, then do not show email, phone or birthday
        if ($this->getUserId() != $userId) {
            $myBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                'userId' => $this->getUserId(),
                'buddyId' => $userId,
            ));

            if (!is_null($myBuddy)) {
                $profile->setStatus(BuddyRequest::BUDDY_REQUEST_STATUS_ACCEPTED);

                // if both user is buddy with each other
                // then show user jid
                $otherBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                    'userId' => $userId,
                    'buddyId' => $this->getUserId(),
                ));

                if (!is_null($otherBuddy)) {
                    $jid = $user->getXmppUsername().'@'.$globals['xmpp_domain'];
                    $profile->setJid($jid);
                }
            } else {
                $viewGroup = $viewGroup.'_stranger';

                $myBuddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
                    'askUserId' => $userId,
                    'recvUserId' => $this->getUserId(),
                    'status' => BuddyRequest::BUDDY_REQUEST_STATUS_PENDING,
                ));

                if (!is_null($myBuddyRequest)) {
                    $profile->setStatus(BuddyRequest::BUDDY_REQUEST_STATUS_PENDING);
                }
            }
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
     * @param int     $id
     *
     * @Route("/basic/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchUserBasicProfileAction(
        Request $request,
        $id
    ) {
        // get profile
        $profile = $this->getRepo('User\UserProfile')->find($id);
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        // check user is allowed to modify
        if ($this->getUserId() != $profile->getUser()->getId()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

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
