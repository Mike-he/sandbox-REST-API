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
 * @see     http://www.Sandbox.cn/
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
            $chatGroupMember->setUser($newUser->getId());
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

        $membersIds = [];
        foreach ($members as $member) {
            /* @var ChatGroupMember $member */
            if (ChatGroup::GROUP_SERVICE == $chatGroup->getTag()) {
                $memberId = $member->getUser()->getXmppUsername();
                if ($memberId != $ownerName) {
                    array_push($membersIds, $memberId);
                }
            }
        }

        $service = $this->get('sandbox_api.jmessage');
        $result = $service->createGroup(
            $ownerName,
            $chatRoomName,
            $chatRoomDesc,
            $membersIds
        );

        if (201 != $result['http_code']) {
            // TODO: create Jmessage Group Error
        }

        return $result['body']['gid'];
    }

    /**
     * @param ChatGroup $chatGroup
     *
     * @return mixed|void
     */
    protected function updateXmppChatGroup(
        $chatGroup
    ) {
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

        $gid = $chatGroup->getGid();

        $service = $this->get('sandbox_api.jmessage');
        $service->updateGroup(
            $gid,
            $chatRoomName,
            $chatRoomDesc
        );
    }

    /**
     * @param $gid
     */
    protected function deleteXmppChatGroup(
        $gid
    ) {
        $service = $this->get('sandbox_api.jmessage');
        $service->deleteGroup($gid);
    }

    /**
     * @param ChatGroup $chatGroup
     * @param $memberIds
     * @param $appKey
     */
    protected function addXmppChatGroupMember(
        $chatGroup,
        $memberIds,
        $appKey = null
    ) {
        $gid = $chatGroup->getGid();

        $service = $this->get('sandbox_api.jmessage');
        $result = $service->addGroupMembers($gid, $memberIds, $appKey);

        if (204 != $result['http_code']) {
            // TODO: create Jmessage Group Error
        }
    }

    /**
     * @param ChatGroup $chatGroup
     * @param $memberIds
     */
    protected function deleteXmppChatGroupMember(
        $chatGroup,
        $memberIds,
        $appKey = null
    ) {
        $gid = $chatGroup->getGid();

        $service = $this->get('sandbox_api.jmessage');
        $service->deleteGroupMembers($gid, $memberIds);
    }
}
