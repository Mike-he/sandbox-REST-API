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
use Sandbox\ApiBundle\Traits\BuddyNotification;

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
    use BuddyNotification;

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
            $profile->setStatus(BuddyRequest::STATUS_ACCEPTED);
            $profile->setBuddyId($myBuddy->getId());

            // if both user is buddy with each other
            // then show user jid
            $otherBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                'user' => $user,
                'buddy' => $myUser,
            ));

            if (!is_null($otherBuddy)) {
                $jid = $user->getXmppUsername();
                $profile->setJid($jid);
            }
        } else {
            $myBuddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
                'askUser' => $myUser,
                'recvUser' => $user,
                'status' => BuddyRequest::STATUS_PENDING,
            ));

            if (!is_null($myBuddyRequest)) {
                $profile->setStatus(BuddyRequest::STATUS_PENDING);
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
        // get my user
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // get buddies
        $buddies = $this->getRepo('Buddy\Buddy')->getBuddies($myUser);
        if (is_null($buddies) || empty($buddies)) {
            return new View(array());
        }
        

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
                        $jid = $user->getXmppUsername();
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
        $user = $this->getRepo('User\User')->find($userId);

        // not allowed to delete buddy if user is sandbox service account
        if ($user->getXmppUsername() == User::XMPP_SERVICE) {
            return new View();
        }

        // get buddy
        $buddy = $this->getRepo('Buddy\Buddy')->find($id);

        if (!is_null($buddy)) {
            $userBuddy = $buddy->getBuddy();

            // not allowed to delete user if buddy is sandbox service account
            if ($userBuddy->getXmppUsername() == User::XMPP_SERVICE) {
                return new View();
            }

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

            $fromUser = $this->getRepo('User\User')->find($userId);
            $recvUser = $this->getRepo('User\User')->find($buddy->getBuddyId());

            // send buddy notification by xmpp
            $this->sendXmppBuddyNotification(
                $fromUser,
                $recvUser,
                'remove'
            );
        }

        return new View();
    }

    /**
     * Get contact recommend buddies.
     *
     * @param Request $request
     *
     * @Route("/buddies/contacts")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postBuddyContactAction(
        Request $request
    ) {
        // get my user
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // get request data
        $contactsData = json_decode($request->getContent(), true);

        // check request data
        if (is_null($contactsData) || empty($contactsData)) {
            return new View(array());
        }

        $contactBuddies = array();
        foreach ($contactsData as $contact) {
            try {
                $user = null;

                if (array_key_exists('phone', $contact)) {
                    $user = $this->getRepo('User\User')->findOneByPhone($contact['phone']);
                }

                if (array_key_exists('email', $contact)) {
                    $user = $this->getRepo('User\User')->findOneByEmail($contact['email']);
                }

                if (is_null($user) || $user == $myUser) {
                    continue;
                }

                // get contact buddy profile
                $buddyProfile = $this->getContactBuddyProfile($user, $myUser);

                if (!is_null($buddyProfile) && !empty($buddyProfile)) {
                    $matchArray = array_merge($contact, $buddyProfile);
                    array_push($contactBuddies, $matchArray);
                }
            } catch (\Exception $e) {
                error_log('Buddy contact match went wrong');
                continue;
            }
        }

        // set view
        $view = new View($contactBuddies);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('buddy')));

        return $view;
    }

    /**
     * @param User $buddy
     * @param User $myUser
     *
     * @return array
     */
    private function getContactBuddyProfile(
        $buddy,
        $myUser
    ) {
        $myBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
            'user' => $myUser,
            'buddy' => $buddy,
        ));

        // return if is my buddy
        if (!is_null($myBuddy)) {
            return array();
        }

        // check buddy request
        $recvBuddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
            'askUser' => $buddy,
            'recvUser' => $myUser,
            'status' => BuddyRequest::STATUS_PENDING,
        ));

        $askBuddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
            'askUser' => $myUser,
            'recvUser' => $buddy,
            'status' => BuddyRequest::STATUS_PENDING,
        ));

        $profile = $this->getRepo('User\UserProfile')->findOneByUser($buddy);

        // generate content
        $request = null;

        if (!is_null($recvBuddyRequest)) {
            $request = $recvBuddyRequest;
        } elseif (!is_null($askBuddyRequest)) {
            $request = $askBuddyRequest;
        }

        return $this->generateRequestArray($profile, $request);
    }

    /**
     * @param UserProfile  $profile
     * @param BuddyRequest $request
     *
     * @return array
     */
    private function generateRequestArray(
        $profile,
        $request = null
    ) {
        $content = array('profile' => $profile);

        if (!is_null($request)) {
            $content['id'] = $request->getId();
            $content['ask_user_id'] = $request->getAskUserId();
            $content['message'] = $request->getMessage();
            $content['status'] = $request->getStatus();
        }

        return $content;
    }
}
