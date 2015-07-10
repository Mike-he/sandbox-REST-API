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
        // get userId
        $userId = $this->getUserId();

        // get user
        $query = $paramFetcher->get('query');
        if (filter_var($query, FILTER_VALIDATE_EMAIL)) {
            $user = $this->getRepo('User\User')->findOneByEmail($query);
        } else {
            $user = $this->getRepo('User\User')->findOneByPhone($query);
        }

        $buddies = array();

        if (!is_null($user)) {
            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($user->getId());

            // set status
            // if the search target is my buddy, then stauts = accepted
            // else if the search target have a pending buddy request from me, then status = pending
            $status = null;

            $myBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                'userId' => $userId,
                'buddyId' => $user->getId(),
            ));

            if (!is_null($myBuddy)) {
                $status = BuddyRequest::BUDDY_REQUEST_STATUS_ACCEPTED;
            } else {
                $myBuddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
                    'askUserId' => $userId,
                    'recvUserId' => $user->getId(),
                    'status' => BuddyRequest::BUDDY_REQUEST_STATUS_PENDING,
                ));

                if (!is_null($myBuddyRequest)) {
                    $status = BuddyRequest::BUDDY_REQUEST_STATUS_PENDING;
                }
            }

            // TODO set user's company

            $buddy = array(
                'profile' => $profile,
                'company' => '',
                'match' => $query,
                'status' => $status,
            );

            array_push($buddies, $buddy);
        }

        // set view
        $view = new View($buddies);
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
        // get user
        $userId = $this->getUserId();

        // TODO check user is authorized


        // get buddies
        $buddies = $this->getRepo('Buddy\Buddy')->findByUserId($userId);

        $myBuddies = array();

        foreach ($buddies as $buddy) {
            $buddyId = $buddy->getBuddyId();
            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($buddyId);

            // TODO set user's company

            $myBuddy = array(
                'id' => $buddy->getId(),
                'profile' => $profile,
                'company' => '',
            );

            array_push($myBuddies, $myBuddy);
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
            $em->flush();
        }

        return new View();
    }
}
