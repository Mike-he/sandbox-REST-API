<?php

namespace Sandbox\ClientApiBundle\Controller\ChatGroup;

use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup;
use Sandbox\ClientApiBundle\Data\ChatGroup\ChatGroupData;
use Sandbox\ApiBundle\Form\ChatGroup\ChatGroupType;
use Sandbox\ClientApiBundle\Form\ChatGroup\ChatGroupDataType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Rs\Json\Patch;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;

/**
 * Client Chat Group Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientChatGroupController extends ChatGroupController
{
    /**
     * Create a chat group.
     *
     * @param Request $request the request object
     *
     * @Route("/chatgroups")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postChatGroupAction(
        Request $request
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned
        if ($myUser->isBanned()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // get request payload
        $data = new ChatGroupData();

        $form = $this->createForm(new ChatGroupDataType(), $data);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // validate request content
        $memberIds = $data->getMemberIds();
        $name = $data->getName();

        if (is_null($memberIds) || empty($memberIds)) {
            // TODO return custom error
        }

        // create new chat group
        $em = $this->getDoctrine()->getManager();

        $chatGroup = new ChatGroup();
        $chatGroup->setCreator($myUser);

        // add member
        $chatGroupName = $name;
        $memberCount = 0;

        $allMembersIds = array($myUserId);
        $allMembersIds = array_merge($allMembersIds, $memberIds);

        foreach ($allMembersIds as $memberId) {
            try {
                if ($memberId != $myUserId) {
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
                } else {
                    $member = $myUser;
                }

                $memberProfile = $this->getRepo('User\UserProfile')->findOneByUser($member);
                if (is_null($memberProfile)) {
                    continue;
                }

                // save member
                $this->saveChatGroupMember($em, $chatGroup, $member, $myUser);

                // generate name
                ++$memberCount;

                if (is_null($name)) {
                    $chatGroupName = $chatGroupName.$memberProfile->getName();

                    if ($memberCount < sizeof($allMembersIds)) {
                        $chatGroupName = $chatGroupName.', ';
                    }
                }
            } catch (\Exception $e) {
                error_log($e);
                continue;
            }
        }

        $chatGroup->setName($chatGroupName);
        $em->persist($chatGroup);

        // save to db
        $em->flush();

        // create chat group in Openfire
        $this->createXmppChatGroup($chatGroup);

        // response
        $view = new View();
        $view->setData(array(
            'id' => $chatGroup->getId(),
            'name' => $chatGroup->getName(),
        ));

        return $view;
    }

    /**
     * List my chat groups.
     *
     * @param Request $request the request object
     *
     * @Route("/chatgroups")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getChatGroupsAction(
        Request $request
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned
        if ($myUser->isBanned()) {
            return new View(array());
        }

        // get my chat groups
        $chatGroups = $this->getRepo('ChatGroup\ChatGroup')->getMyChatGroups($myUserId);

        // response
        return new View($chatGroups);
    }

    /**
     * Retrieve a given chat group.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/chatgroups/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getChatGroupAction(
        Request $request,
        $id
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned and authorized
        if ($myUser->isBanned()) {
            return new View();
        }

        // get chat group
        $chatGroup = $this->getRepo('ChatGroup\ChatGroup')->getChatGroup($id, $myUserId);

        return new View($chatGroup);
    }

    /**
     * Retrieve everything a given chat group.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/chatgroups/{id}/all")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getChatGroupAllAction(
        Request $request,
        $id
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned
        if ($myUser->isBanned()) {
            return new View();
        }

        // get chatGroup
        $chatGroup = $this->getRepo('ChatGroup\ChatGroup')->find($id);
        $this->throwNotFoundIfNull($chatGroup, self::NOT_FOUND_MESSAGE);

        // get chat group array
        $chatGroupArray = $this->getRepo('ChatGroup\ChatGroup')->getChatGroup($id, $myUserId);

        // get chat group members array
        $membersArray = array();

        $members = $this->getRepo('ChatGroup\ChatGroupMember')->findByChatGroup($chatGroup);
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

        $chatGroupArray['members'] = $membersArray;

        // set view
        $view = new View($chatGroupArray);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('chatgroup')));

        return $view;
    }

    /**
     * Modify a given chat group.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/chatgroups/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchChatGroupAction(
        Request $request,
        $id
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned
        if ($myUser->isBanned()) {
            return new View();
        }

        // get chatGroup
        $chatGroup = $this->getRepo('ChatGroup\ChatGroup')->find($id);
        $this->throwNotFoundIfNull($chatGroup, self::NOT_FOUND_MESSAGE);

        // bind data
        $chatGroupJson = $this->container->get('serializer')->serialize($chatGroup, 'json');
        $patch = new Patch($chatGroupJson, $request->getContent());
        $chatGroupJson = $patch->apply();

        $form = $this->createForm(new ChatGroupType(), $chatGroup);
        $form->submit(json_decode($chatGroupJson, true));

        // set chatGroup
        $chatGroup->setModificationDate(new \DateTime('now'));

        // update to db
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // update chat group in Openfire
        $this->updateXmppChatGroup($chatGroup, $myUser);

        return new View();
    }

    /**
     * Remove / quit a given chat group.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/chatgroups/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteChatGroupAction(
        Request $request,
        $id
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned
        if ($myUser->isBanned()) {
            return new View();
        }

        // get chatGroup
        $chatGroup = $this->getRepo('ChatGroup\ChatGroup')->find($id);
        if (is_null($chatGroup)) {
            return new View();
        }

        // only chat group creator is allowed to remove it
        if ($myUser != $chatGroup->getCreator()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // remove from db
        $em = $this->getDoctrine()->getManager();
        $em->remove($chatGroup);
        $em->flush();

        // update chat group in Openfire
        $this->deleteXmppChatGroup($chatGroup, $myUser);

        return new View();
    }

    /**
     * Mute a chat group.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/chatgroups/{id}/mute")
     * @Method({"POST"})
     *
     * @return View
     */
    public function muteChatGroupAction(
        Request $request,
        $id
    ) {
        $this->handleChatGroupMute($id, true);
    }

    /**
     * Unmute a chat group.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/chatgroups/{id}/mute")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function unmuteChatGroupAction(
        Request $request,
        $id
    ) {
        $this->handleChatGroupMute($id, false);
    }

    /**
     * @param int  $id
     * @param bool $mute
     *
     * @return View
     */
    private function handleChatGroupMute(
        $id,
        $mute
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned
        if ($myUser->isBanned()) {
            return new View();
        }

        // get chatGroup
        $chatGroup = $this->getRepo('ChatGroup\ChatGroup')->find($id);
        if (is_null($chatGroup)) {
            return new View();
        }

        // get chat group member
        $chatGroupMember = $this->getRepo('ChatGroup\ChatGroupMember')
            ->findOneBy(
                array(
                    'chatGroup' => $chatGroup,
                    'user' => $myUser,
                )
            );
        if (is_null($chatGroup)) {
            return new View();
        }

        $chatGroupMember->setMute($mute);

        // remove from db
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }
}
