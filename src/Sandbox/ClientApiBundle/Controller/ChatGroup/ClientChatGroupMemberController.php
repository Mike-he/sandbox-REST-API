<?php

namespace Sandbox\ClientApiBundle\Controller\ChatGroup;

use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup;
use Sandbox\ApiBundle\Entity\User\User;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Client Chat Group Member Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientChatGroupMemberController extends ChatGroupController
{
    const ERROR_IS_CREATOR_CODE = 400001;
    const ERROR_IS_CREATOR_MESSAGE = 'You are the creator, cannot quit!';

    /**
     * Get members.
     *
     * @param Request $request contains request info
     * @param int     $id      id of the company
     *
     * @Get("/chatgroups/{id}/members")
     *
     * @return array
     */
    public function getChatGroupMembersAction(
        Request $request,
        $id
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned and authorized
        if ($myUser->isBanned() || !$myUser->isAuthorized()) {
            return new View();
        }

        // get not banned and authorized members
        $chatGroup = $this->getRepo('ChatGroup\ChatGroup')->find($id);
        $this->throwNotFoundIfNull($chatGroup, self::NOT_FOUND_MESSAGE);

        $members = $this->getRepo('ChatGroup\ChatGroupMember')->getChatGroupMembers($chatGroup);

        if (is_null($members) || empty($members)) {
            return new View();
        }

        // get chat group members array
        $membersArray = array();

        foreach ($members as $member) {
            try {
                $memberArray = array();
                $memberArray['id'] = $member->getId();

                $profile = $this->getRepo('User\UserProfile')->findOneByUser($member->getUser());
                $memberArray['profile'] = $profile;

                array_push($membersArray, $memberArray);
            } catch (\Exception $e) {
                error_log($e);
                continue;
            }
        }

        // response
        $view = new View($membersArray);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('chatgroup'))
        );

        return $view;
    }

    /**
     * add members.
     *
     * @param Request $request
     * @param int     $id
     *
     * @POST("/chatgroups/{id}/members")
     *
     * @return View
     */
    public function postChatGroupMembersAction(
        Request $request,
        $id
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned and authorized
        if ($myUser->isBanned() || !$myUser->isAuthorized()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // get chatGroup
        $chatGroup = $this->getRepo('ChatGroup\ChatGroup')->find($id);
        $this->throwNotFoundIfNull($chatGroup, self::NOT_FOUND_MESSAGE);

        //add member
        $em = $this->getDoctrine()->getManager();

        $memberIds = json_decode($request->getContent(), true);
        $members = array();

        foreach ($memberIds as $memberId) {
            try {
                $member = $this->getRepo('User\User')->find($memberId);
                if (is_null($member)) {
                    continue;
                }

                // check member is buddy
                $buddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                    'user' => $myUser,
                    'buddy' => $member,
                ));
                if (is_null($buddy)) {
                    continue;
                }

                // save member
                $this->saveChatGroupMember($em, $chatGroup, $member, $myUser);

                array_push($members, $member);
            } catch (\Exception $e) {
                error_log($e);
                continue;
            }
        }

        $em->flush();

        // update chat group in Openfire
        $this->handleXmppChatGroupMember($chatGroup, $myUser, $members, 'POST');

        return new view();
    }

    /**
     * delete members.
     *
     * @param $id
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    strict=true,
     *    description=""
     * )
     *
     * @Delete("/chatgroups/{id}/members")
     *
     * @return View
     */
    public function deleteChatGroupMembersAction(
        $id,
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned and authorized
        if ($myUser->isBanned() || !$myUser->isAuthorized()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // get chat group
        $chatGroup = $this->getRepo('ChatGroup\ChatGroup')->find($id);
        $this->throwNotFoundIfNull($chatGroup, self::NOT_FOUND_MESSAGE);

        $memberIds = $paramFetcher->get('id');

        if (empty($memberIds)) {
            // quit company
            return $this->quitMyChatGroup($chatGroup, $myUser);
        } else {
            // delete members
            return $this->deleteChatGroupMembers($chatGroup, $myUser, $memberIds);
        }
    }

    /**
     * @param ChatGroup $chatGroup
     * @param User      $user
     *
     * @return View
     */
    public function quitMyChatGroup(
        $chatGroup,
        $user
    ) {
        if ($user == $chatGroup->getCreator()) {
            return $this->customErrorView(
                400,
                self::ERROR_IS_CREATOR_CODE,
                self::ERROR_IS_CREATOR_MESSAGE
            );
        }

        // quit my chat group
        $chatGroupMember = $this->getRepo('ChatGroup\ChatGroupMember')
            ->findOneBy(
                array(
                    'chatGroup' => $chatGroup,
                    'user' => $user,
                )
            );

        // remove from db
        if (!is_null($chatGroupMember)) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($chatGroupMember);
            $em->flush();

            // update chat group in Openfire
            $this->handleXmppChatGroupMember($chatGroup, $user, array($user), 'DELETE');
        }

        return new View();
    }

    /**
     * @param ChatGroup $chatGroup
     * @param User      $user
     * @param array     $memberIds
     *
     * @return View
     */
    public function deleteChatGroupMembers(
        $chatGroup,
        $user,
        $memberIds
    ) {
        // only chat group creator is allowed to delete member
        if ($user != $chatGroup->getCreator()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $members = array();

        foreach ($memberIds as $memberId) {
            try {
                $chatGroupMember = $this->getRepo('ChatGroup\ChatGroupMember')->find($memberId);
                if (is_null($chatGroupMember)) {
                    continue;
                }

                // ignore creator
                if ($chatGroup->getCreator() == $chatGroupMember->getUser()) {
                    continue;
                }

                $em->remove($chatGroupMember);

                array_push($members, $chatGroupMember->getUser());
            } catch (\Exception $e) {
                error_log($e);
                continue;
            }
        }

        $em->flush();

        // update chat group in Openfire
        $this->handleXmppChatGroupMember($chatGroup, $user, $members, 'DELETE');

        return new View();
    }
}
