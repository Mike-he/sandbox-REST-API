<?php

namespace Sandbox\ClientApiBundle\Controller\Buddy;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\Buddy\BuddyRequest;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;

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
class ClientBuddyRequestController extends UserProfileController
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
        $user = $this->getRepo('User\User')->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $myRequests = array();

        $requests = $user->getRecvBuddyRequests();
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
        // get askUser
        $userId = $this->getUserId();
        $askUser = $this->getRepo('User\User')->find($userId);
        $this->throwNotFoundIfNull($askUser, self::NOT_FOUND_MESSAGE);

        // get recvUser
        $requestContent = $request->getContent();
        if (is_null($requestContent)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $payload = json_decode($requestContent, true);
        $recvUserId = $payload['user_id'];

        $recvUser = $this->getRepo('User\User')->find($recvUserId);
        $this->throwNotFoundIfNull($recvUser, self::NOT_FOUND_MESSAGE);

        // find pending buddy request
        $buddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
            'askUser' => $askUser,
            'recvUser' => $recvUser,
            'status' => BuddyRequest::BUDDY_REQUEST_STATUS_PENDING,
        ));

        $em = $this->getDoctrine()->getManager();

        if (is_null($buddyRequest)) {
            // save new buddy request
            $buddyRequest = new BuddyRequest();
            $buddyRequest->setAskUser($askUser);
            $buddyRequest->setRecvUser($recvUser);

            $em->persist($buddyRequest);
        } else {
            // update modification date
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
}
