<?php

namespace Sandbox\ClientApiBundle\Controller\Buddy;

use Sandbox\ApiBundle\Controller\Buddy\BuddyController;
use Sandbox\ApiBundle\Entity\Buddy\Buddy;
use Sandbox\ApiBundle\Entity\Buddy\BuddyRequest;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use JMS\Serializer\SerializationContext;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * Rest controller for UserProfile.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientBuddyController extends BuddyController
{
    /**
     * Search buddies.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    default=null,
     *    description="search query"
     * )
     *
     * @Route("/buddies/search")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBuddiesSearchAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // if user is not authorized, respond empty list
        $cardNo = $this->getCardNoIfUserAuthorized();
        if (is_null($cardNo)) {
            return new View(array());
        }

        // get my user
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // get user
        $query = $paramFetcher->get('query');
        if (filter_var($query, FILTER_VALIDATE_EMAIL)) {
            $user = $this->getRepo('User\User')->findOneBy(array(
                'email' => $query,
                'authorized' => true,
                'banned' => false,
                ));
        } else {
            $user = $this->getRepo('User\User')->findOneBy(array(
                'phone' => $query,
                'authorized' => true,
                'banned' => false,
                ));
        }

        if (is_null($user) || $user === $myUser) {
            return new View(array());
        }

        // get profile
        $profile = $this->getRepo('User\UserProfile')->findOneByUser($user);
        if (is_null($profile)) {
            return new View(array());
        }

        // set status
        // if the search target is my buddy, then stauts = accepted
        // else if the search target have a pending buddy request from me, then status = pending
        $myBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
            'user' => $myUser,
            'buddy' => $user,
        ));

        if (!is_null($myBuddy)) {
            $profile->setStatus(BuddyRequest::BUDDY_REQUEST_STATUS_ACCEPTED);
            $profile->setBuddyId($myBuddy->getId());

            // if both user is buddy with each other
            // then show user jid
            $otherBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                'user' => $user,
                'buddy' => $myUser,
            ));

            if (!is_null($otherBuddy)) {
                // get globals
                $twig = $this->container->get('twig');
                $globals = $twig->getGlobals();

                $jid = $user->getXmppUsername().'@'.$globals['xmpp_domain'];
                $profile->setJid($jid);
            }
        } else {
            $myBuddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
                'askUser' => $myUser,
                'recvUser' => $user,
                'status' => BuddyRequest::BUDDY_REQUEST_STATUS_PENDING,
            ));

            if (!is_null($myBuddyRequest)) {
                $profile->setStatus(BuddyRequest::BUDDY_REQUEST_STATUS_PENDING);
            }
        }

        // response
        $buddy = array(
            'profile' => $profile,
            'match' => $query,
        );

        // set view
        $view = new View(array($buddy));
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('buddy')));

        return $view;
    }

    /**
     * Get my buddy request.
     *
     * @param Request $request the request object
     *
     * @Route("/buddies")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBuddiesAction(
        Request $request
    ) {
        // if user is not authorized, respond empty list
        $cardNo = $this->getCardNoIfUserAuthorized();
        if (is_null($cardNo)) {
            return new View(array());
        }

        // get my user
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // get buddies
        $buddies = $this->getRepo('Buddy\Buddy')->getBuddies($myUser);
        if (is_null($buddies) || empty($buddies)) {
            return new View(array());
        }

        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $myBuddies = array();

        foreach ($buddies as $buddy) {
            try {
                $buddyId = $buddy->getBuddyId();

                $user = $this->getRepo('User\User')->findOneById($buddyId);
                if (is_null($user)) {
                    continue;
                }

                $profile = $this->getRepo('User\UserProfile')->findOneByUser($user);
                if (!is_null($profile)) {
                    // if both user is buddy with each other
                    // then show user jid
                    $otherBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                        'user' => $buddy->getBuddy(),
                        'buddy' => $myUser,
                    ));

                    if (!is_null($otherBuddy)) {
                        $jid = $user->getXmppUsername().'@'.$globals['xmpp_domain'];
                        $profile->setJid($jid);
                    }
                }

                $myBuddy = array(
                    'id' => $buddy->getId(),
                    'user' => $user,
                    'profile' => $profile,
                );

                array_push($myBuddies, $myBuddy);
            } catch (\Exception $e) {
                continue;
            }
        }

        // set view
        $view = new View($myBuddies);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('buddy')));

        return $view;
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/buddies/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteBuddyAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        // get buddy
        $buddy = $this->getRepo('Buddy\Buddy')->find($id);

        if (!is_null($buddy)) {
            // check user is allowed to delete
            if ($userId != $buddy->getUserId()) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }

            // remove from db
            $em = $this->getDoctrine()->getManager();
            $em->remove($buddy);

            $buddyOther = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                'userId' => $buddy->getBuddyId(),
                'buddyId' => $userId,
            ));
            if (!is_null($buddyOther) || !empty($buddyOther)) {
                $em->remove($buddyOther);
            }

            $em->flush();
        }

        $fromUser = $this->getRepo('User\User')->find($userId);
        $recvUser = $this->getRepo('User\User')->find($buddy->getBuddyId());

        // send buddy notification by xmpp
        $this->sendXmppBuddyNotification(
            $fromUser,
            $recvUser,
            'remove'
        );

        return new View();
    }
}
