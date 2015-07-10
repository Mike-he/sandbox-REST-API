<?php

namespace Sandbox\ClientApiBundle\Controller\Buddy;

use Sandbox\ApiBundle\Controller\Buddy\BuddyRequestController;
use Sandbox\ApiBundle\Entity\Buddy\Buddy;
use Sandbox\ApiBundle\Entity\Buddy\BuddyRequest;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Form\Buddy\BuddyRequestType;
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
        // get user
        $userId = $this->getUserId();
        $requests = $this->getRepo('Buddy\BuddyRequest')->findByRecvUserId($userId);

        $myRequests = array();

        foreach ($requests as $request) {
            $askUserId = $request->getAskUserId();
            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($askUserId);

            // TODO set user's company

            $myRequest = array(
                'id' => $request->getId(),
                'ask_user_id' => $askUserId,
                'status' => $request->getStatus(),
                'profile' => $profile,
                'company' => '',
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
        // get userId
        $userId = $this->getUserId();

        // get recvUser
        $requestContent = $request->getContent();
        if (is_null($requestContent)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $payload = json_decode($requestContent, true);
        $recvUserId = $payload['user_id'];

        // check user exist
        $recvUser = $this->getRepo('User\User')->find($recvUserId);
        $this->throwNotFoundIfNull($recvUser, self::NOT_FOUND_MESSAGE);

        // if the user is already my buddy, don't proceed
        $buddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
            'userId' => $userId,
            'buddyId' => $recvUserId,
        ));
        if (!is_null($buddy)) {
            return new View();
        }

        // if buddy request exist, then change status back to pending
        $buddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
            'askUserId' => $userId,
            'recvUserId' => $recvUserId,
        ));

        $em = $this->getDoctrine()->getManager();

        if (is_null($buddyRequest)) {
            // save new buddy request
            $buddyRequest = new BuddyRequest();
            $buddyRequest->setAskUserId($userId);
            $buddyRequest->setRecvUserId($recvUserId);

            $em->persist($buddyRequest);
        } else {
            // update buddy request
            $buddyRequest->setStatus(BuddyRequest::BUDDY_REQUEST_STATUS_PENDING);
            $buddyRequest->setModificationDate(new \DateTime('now'));
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
        $userId = $this->getUserId();

        // get buddy request
        $buddyRequest = $this->getRepo('Buddy\BuddyRequest')->find($id);
        $this->throwNotFoundIfNull($buddyRequest, self::NOT_FOUND_MESSAGE);

        // check user is allowed to modify
        if ($userId != $buddyRequest->getRecvUserId()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // check status is pending
        if ($buddyRequest->getStatus() != BuddyRequest::BUDDY_REQUEST_STATUS_PENDING) {
            throw new ConflictHttpException(self::CONFLICT_MESSAGE);
        }

        // bind data
        $buddyRequestJson = $this->container->get('serializer')->serialize($buddyRequest, 'json');
        $patch = new Patch($buddyRequestJson, $request->getContent());
        $buddyRequestJson = $patch->apply();

        $form = $this->createForm(new BuddyRequestType(), $buddyRequest);
        $form->submit(json_decode($buddyRequestJson, true));

        // set profile
        $buddyRequest->setModificationDate(new \DateTime('now'));

        // update to db
        $em = $this->getDoctrine()->getManager();

        if ($buddyRequest->getStatus() === BuddyRequest::BUDDY_REQUEST_STATUS_ACCEPTED) {
            // save my buddy
            $this->saveBuddy($em, $userId, $buddyRequest->getAskUserId());

            // save others' buddy
            $this->saveBuddy($em, $buddyRequest->getAskUserId(), $userId);
        }

        $em->flush();

        return new View();
    }

    /**
     * @param $em
     * @param $userId
     * @param $buddyId
     */
    private function saveBuddy(
        $em,
        $userId,
        $buddyId
    ) {
        $myBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
            'userId' => $userId,
            'buddyId' => $buddyId,
        ));

        if (is_null($myBuddy)) {
            $myBuddy = new Buddy();
            $myBuddy->setUserId($userId);
            $myBuddy->setBuddyId($buddyId);

            $em->persist($myBuddy);
        }
    }
}
