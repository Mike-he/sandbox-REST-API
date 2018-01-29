<?php

namespace Sandbox\ClientApiBundle\Controller\Buddy;

use FOS\RestBundle\Request\ParamFetcherInterface;
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
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
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
class ClientBuddyRequestController extends BuddyRequestController
{
    use BuddyNotification;
    const ERROR_ACCOUNT_BANNED_CODE = 401001;
    const ERROR_ACCOUNT_BANNED_MESSAGE = '该请求的账户已经被冻结或未认证，暂时无法添加此账户!';

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
        $buddyRequests = $this->getRepo('Buddy\BuddyRequest')->getRequestBuddies($myUser);

        $myRequests = array();

        foreach ($buddyRequests as $buddyRequest) {
            try {
                $askUser = $buddyRequest->getAskUser();

                // get current status with the ask user
                $status = $buddyRequest->getStatus();

                if ($status == BuddyRequest::STATUS_ACCEPTED) {
                    $buddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                        'user' => $myUser,
                        'buddy' => $askUser,
                    ));
                    if (is_null($buddy)) {
                        $status = null;
                    }
                }

                // get ask user profile
                $profile = $this->getRepo('User\UserProfile')->findOneByUser($askUser);

                $myRequest = array(
                    'id' => $buddyRequest->getId(),
                    'ask_user_id' => $buddyRequest->getAskUserId(),
                    'message' => $buddyRequest->getMessage(),
                    'creation_date' => $buddyRequest->getCreationDate(),
                    'profile' => $profile,
                );

                if (!is_null($status)) {
                    $myRequest['status'] = $status;
                }

                array_push($myRequests, $myRequest);
            } catch (\Exception $e) {
                error_log('Get buddy request went wrong');
                continue;
            }
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
        /** @var User $myUser */
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
            $buddyRequest->setStatus(BuddyRequest::STATUS_PENDING);
            $buddyRequest->setModificationDate(new \DateTime('now'));
        }

        $message = $buddyRequestData->getMessage();
        if (!is_null($message)) {
            $buddyRequest->setMessage($message);
        }

        $this->get('sandbox_api.jmessage')
            ->addFriends(
                $myUser->getXmppUsername(),
                [$recvUser->getXmppUsername()]
            );

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

        // check user that request me is not banned
        $user = $this->getRepo('User\User')->findOneById($askUserId);
        if ($user->isBanned()) {
            // user of the request is banned
            return $this->customErrorView(
                401,
                self::ERROR_ACCOUNT_BANNED_CODE,
                self::ERROR_ACCOUNT_BANNED_MESSAGE);
        }

        // check status is pending
        if ($buddyRequest->getStatus() === BuddyRequest::STATUS_PENDING) {
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

            if ($buddyRequest->getStatus() === BuddyRequest::STATUS_ACCEPTED) {
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
                    'status' => BuddyRequest::STATUS_PENDING,
                ));
                if (!is_null($buddyRequest)) {
                    $buddyRequest->setStatus(BuddyRequest::STATUS_ACCEPTED);
                }
            }

            $em->flush();
        }

        return new View();
    }

    /**
     * Delete my buddy requests.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description="buddy requests id"
     * )
     *
     * @Route("/requests")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteBuddyRequestsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $em = $this->getDoctrine()->getManager();

        $ids = $paramFetcher->get('id');
        foreach ($ids as $id) {
            $buddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
                'id' => $id,
                'recvUserId' => $userId,
            ));

            if (is_null($buddyRequest)) {
                continue;
            }

            $em->remove($buddyRequest);
        }

        $em->flush();

        return new View();
    }
}
