<?php

namespace Sandbox\ApiBundle\Controller\ChatGroup;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroupMember;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Traits\CurlUtil;

/**
 * Chat Group Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ChatGroupController extends SandboxRestController
{
    use CurlUtil;

    /**
     * @param array $members
     *
     * @return array
     */
    protected function getChatGroupMembersArray(
        $members
    ) {
        $membersArray = array();

        foreach ($members as $member) {
            try {
                $memberArray = array();
                $memberArray['id'] = $member->getId();

                $user = $member->getUser();
                $profile = $this->getRepo('User\UserProfile')->findOneByUser($user);

                $memberArray['profile'] = $profile;
                array_push($membersArray, $memberArray);
            } catch (\Exception $e) {
                error_log($e);
                continue;
            }
        }

        return $membersArray;
    }

    /**
     * @param $em
     * @param ChatGroup $chatGroup
     * @param User      $newUser
     * @param User      $myUser
     */
    protected function saveChatGroupMember(
        $em,
        $chatGroup,
        $newUser,
        $myUser
    ) {
        $member = $this->getRepo('ChatGroup\ChatGroupMember')->findOneBy(
            array(
                'chatGroup' => $chatGroup,
                'user' => $newUser,
            )
        );

        if (is_null($member)) {
            // new chat group member
            $chatGroupMember = new ChatGroupMember();
            $chatGroupMember->setChatGroup($chatGroup);
            $chatGroupMember->setUser($newUser);
            $chatGroupMember->setAddBy($myUser);
            $em->persist($chatGroupMember);
        }
    }

    /**
     * @param ChatGroup $chatGroup
     *
     * @return mixed|void
     */
    protected function createXmppChatGroup(
        $chatGroup
    ) {
        try {
            //            $chatRoomId = $chatGroup->getId();
            $chatRoomName = $chatGroup->getName().'@'.$chatGroup->getTag();
            $chatRoomDesc = array(
                'tag' => $chatGroup->getTag(),
            );
            if ($chatGroup->getBuildingId()) {
                $chatRoomDesc['building_id'] = $chatGroup->getBuildingId();
                $building = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->find($chatGroup->getBuildingId());
                if ($building) {
                    $chatRoomDesc['avatar'] = $building->getAvatar();
                }
            }
            $chatRoomDesc = json_encode($chatRoomDesc);

            $ownerName = $chatGroup->getCreator()->getXmppUsername();
            $members = $this->getRepo('ChatGroup\ChatGroupMember')->findByChatGroup($chatGroup);

            $membersIds = array();
            foreach ($members as $member) {
                $memberId = $member->getUser()->getXmppUsername();
                if ($memberId != $ownerName) {
                    array_push($membersIds, $memberId);
                }
            }

            $service = $this->get('sandbox_api.jmessage');
            $result = $service->createGroup(
                $ownerName,
                $chatRoomName,
                $chatRoomDesc,
                $membersIds
            );

            return $result['body']['gid'];
        } catch (\Exception $e) {
            error_log('Create chat group went wrong!');
        }
    }

    /**
     * @param ChatGroup $chatGroup
     *
     * @return mixed|void
     */
    protected function updateXmppChatGroup(
        $chatGroup
    ) {
        try {
            $chatRoomName = $chatGroup->getName();
            $chatRoomDesc = array(
                'tag' => $chatGroup->getTag(),
            );
            if ($chatGroup->getBuildingId()) {
                $chatRoomDesc['building_id'] = $chatGroup->getBuildingId();
                $building = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->find($chatGroup->getBuildingId());
                if ($building) {
                    $chatRoomDesc['avatar'] = $building->getAvatar();
                }
            }
            $chatRoomDesc = json_encode($chatRoomDesc);

            $gid = $chatGroup->getGid();

            $service = $this->get('sandbox_api.jmessage');
            $service->updateGroup(
                $gid,
                $chatRoomName,
                $chatRoomDesc
            );
        } catch (\Exception $e) {
            error_log('Update chat group went wrong!');
        }
    }

    /**
     * @param $gid
     */
    protected function deleteXmppChatGroup(
        $gid
    ) {
        try {
            $service = $this->get('sandbox_api.jmessage');
            $service->deleteGroup($gid);
        } catch (\Exception $e) {
            error_log('Delete chat group went wrong!');
        }
    }

    /**
     * @param ChatGroup $chatGroup
     * @param $members
     */
    protected function addXmppChatGroupMember(
        $chatGroup,
        $members
    ) {
        try {
            $gid = $chatGroup->getGid();
            $memberIds = [];
            foreach ($members as $member) {
                $memberIds[] = $member->getXmppUsername();
            }

            $service = $this->get('sandbox_api.jmessage');
            $service->addGroupMembers($gid, $memberIds);
        } catch (\Exception $e) {
            error_log('Add chat group members went wrong!');
        }
    }

    protected function deleteXmppChatGroupMember(
        $chatGroup,
        $members
    ) {
        try {
            $gid = $chatGroup->getGid();
            $memberIds = [];
            foreach ($members as $member) {
                $memberIds[] = $member->getXmppUsername();
            }

            $service = $this->get('sandbox_api.jmessage');
            $service->deleteGroupMembers($gid, $memberIds);
        } catch (\Exception $e) {
            error_log('Delete chat group members went wrong!');
        }
    }
}
