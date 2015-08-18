<?php

namespace Sandbox\ClientApiBundle\Controller\Buddy;

use Sandbox\ApiBundle\Controller\Buddy\BuddyRequestController;
use Sandbox\ApiBundle\Entity\Buddy\Buddy;
use Sandbox\ApiBundle\Entity\Buddy\BuddyRequest;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Form\Buddy\BuddyRequestPatchType;
use Sandbox\ApiBundle\Form\Buddy\BuddyRequestPostType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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
class ClientBuddyRequestController extends BuddyRequestController
{
    /**
     * Get my buddy request.
     *
     * @param Request $request the request object
     *
     * @Route("/requests")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBuddyRequestsAction(
        Request $request
    ) {
        // get my user
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // get my buddy requests
        $buddyRequests = $this->getRepo('Buddy\BuddyRequest')->findByRecvUser($myUser);

        $myRequests = array();

        foreach ($buddyRequests as $buddyRequest) {
            $profile = $this->getRepo('User\UserProfile')->findOneByUser(
                $buddyRequest->getAskUser()
            );

            // get user's company
            $company = $this->getCompanyIfMember($buddyRequest->getAskUserId());

            $myRequest = array(
                'id' => $buddyRequest->getId(),
                'ask_user_id' => $buddyRequest->getAskUserId(),
                'message' => $buddyRequest->getMessage(),
                'status' => $buddyRequest->getStatus(),
                'profile' => $profile,
                'company' => $company,
            );

            array_push($myRequests, $myRequest);
        }

        // set view
        $view = new View($myRequests);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('buddy')));

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Route("/requests")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postBuddyRequestAction(
        Request $request
    ) {
        // get my user
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // get incoming data
        $buddyRequestData = new BuddyRequest();
        $form = $this->createForm(new BuddyRequestPostType(), $buddyRequestData);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // check user exist
        $recvUserId = $form['user_id']->getData();
        if (is_null($recvUserId) || $recvUserId == $myUserId) {
            // or user is trying to adding him/her self
            throw new ConflictHttpException(self::CONFLICT_MESSAGE);
        }

        $recvUser = $this->getRepo('User\User')->find($recvUserId);
        $this->throwNotFoundIfNull($recvUser, self::NOT_FOUND_MESSAGE);

        // if the user is already my buddy, don't proceed
        $buddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
            'user' => $myUser,
            'buddy' => $recvUser,
        ));
        if (!is_null($buddy)) {
            return new View();
        }

        // if buddy request exist, then change status back to pending
        $buddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
            'askUser' => $myUser,
            'recvUser' => $recvUser,
        ));

        $em = $this->getDoctrine()->getManager();

        if (is_null($buddyRequest)) {
            // save new buddy request
            $buddyRequest = new BuddyRequest();
            $buddyRequest->setAskUser($myUser);
            $buddyRequest->setRecvUser($recvUser);

            $em->persist($buddyRequest);
        } else {
            // update buddy request
            $buddyRequest->setStatus(BuddyRequest::BUDDY_REQUEST_STATUS_PENDING);
            $buddyRequest->setModificationDate(new \DateTime('now'));
        }

        $message = $buddyRequestData->getMessage();
        if (!is_null($message)) {
            $buddyRequest->setMessage($message);
        }

        $em->flush();

        // set view
        $view = new View();
        $view->setData(
            array('id' => $buddyRequest->getId())
        );

        return $view;
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/requests/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchBuddyRequestAction(
        Request $request,
        $id
    ) {
        // get my user
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // get buddy request
        $buddyRequest = $this->getRepo('Buddy\BuddyRequest')->find($id);
        $this->throwNotFoundIfNull($buddyRequest, self::NOT_FOUND_MESSAGE);

        // check user is allowed to modify
        if ($myUserId != $buddyRequest->getRecvUserId()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // check user is trying to add him/her self
        $askUserId = $buddyRequest->getAskUserId();
        if ($myUserId === $askUserId) {
            throw new ConflictHttpException(self::CONFLICT_MESSAGE);
        }

        // check status is pending
        if ($buddyRequest->getStatus() === BuddyRequest::BUDDY_REQUEST_STATUS_PENDING) {
            // bind data
            $buddyRequestJson = $this->container->get('serializer')->serialize($buddyRequest, 'json');
            $patch = new Patch($buddyRequestJson, $request->getContent());
            $buddyRequestJson = $patch->apply();

            $form = $this->createForm(new BuddyRequestPatchType(), $buddyRequest);
            $form->submit(json_decode($buddyRequestJson, true));

            // set modification date
            $buddyRequest->setModificationDate(new \DateTime('now'));

            // update to db
            $em = $this->getDoctrine()->getManager();

            if ($buddyRequest->getStatus() === BuddyRequest::BUDDY_REQUEST_STATUS_ACCEPTED) {
                $askUser = $this->getRepo('User\User')->find($askUserId);

                // save my buddy
                $this->saveBuddy($em, $myUser, $askUser);

                // save others' buddy
                $this->saveBuddy($em, $askUser, $myUser);

                // find my pending buddy request to the other user
                // update the status to accepted
                $buddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
                    'askUser' => $myUser,
                    'recvUser' => $askUser,
                    'status' => BuddyRequest::BUDDY_REQUEST_STATUS_PENDING,
                ));
                if (!is_null($buddyRequest)) {
                    $buddyRequest->setStatus(BuddyRequest::BUDDY_REQUEST_STATUS_ACCEPTED);
                }
            }

            $em->flush();
        }

        return new View();
    }

    /**
     * @param object $em
     * @param User   $user
     * @param User   $buddy
     */
    private function saveBuddy(
        $em,
        $user,
        $buddy
    ) {
        $myBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
            'user' => $user,
            'buddy' => $buddy,
        ));

        if (is_null($myBuddy)) {
            $myBuddy = new Buddy();
            $myBuddy->setUser($user);
            $myBuddy->setBuddy($buddy);

            $em->persist($myBuddy);
        }
    }
}
