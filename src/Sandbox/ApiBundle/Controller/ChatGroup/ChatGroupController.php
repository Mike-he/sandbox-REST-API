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

                $jid = $this->constructXmppJid($user->getXmppUsername());
                $profile->setJid($jid);

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
     * @param $chatGroup
     * @param $serviceType
     *
     * @return mixed|void
     */
    protected function createXmppChatGroup(
        $chatGroup,
        $serviceType = ChatGroup::XMPP_SERVICE
    ) {
        try {
            $chatRoomId = $chatGroup->getId();
            $chatRoomName = $chatGroup->getName();
            $ownerName = $chatGroup->getCreator()->getXmppUsername();
            $members = $this->getRepo('ChatGroup\ChatGroupMember')->findByChatGroup($chatGroup);

            $membersIds = array();
            foreach ($members as $member) {
                $memberId = $member->getUser()->getXmppUsername();
                array_push($membersIds, $memberId);
            }

            $service = $this->get('openfire.service');
            $service->createChatRoomWithSpecificMembersAndService(
                $chatRoomId,
                $chatRoomName,
                $ownerName,
                $membersIds,
                $serviceType
            );
        } catch (\Exception $e) {
            error_log('Create XMPP chat group went wrong!');
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
            $chatRoomId = $chatGroup->getId();
            $chatRoomName = $chatGroup->getName();
            $service = $this->get('openfire.service');
            $service->putChatRoomName(
                $chatRoomId,
                $chatRoomName
            );
        } catch (\Exception $e) {
            error_log('Update XMPP chat group went wrong!');
        }
    }

    /**
     * @param ChatGroup $chatGroup
     *
     * @return mixed|void
     */
    protected function deleteXmppChatGroup(
        $chatGroup
    ) {
        try {
            $chatRoomId = $chatGroup->getId();
            $service = $this->get('openfire.service');
            $service->deleteChatRoom($chatRoomId);
        } catch (\Exception $e) {
            error_log('Update XMPP chat group went wrong!');
        }
    }

    /**
     * @param $chatGroup
     * @param $members
     */
    protected function addXmppChatGroupMember(
        $chatGroup,
        $members
    ) {
        try {
            $chatRoomId = $chatGroup->getId();
            $role = 'members';
            $serviceName = $chatGroup->getTag() ? $chatGroup->getTag() : ChatGroup::XMPP_SERVICE;
            $service = $this->get('openfire.service');

            foreach ($members as $member) {
                $memberId = $member->getXmppUsername();
                $service->addUserInChatRoomWithSpecificService(
                    $chatRoomId,
                    $role,
                    $memberId,
                    $serviceName
                );
            }
        } catch (\Exception $e) {
            error_log('Add XMPP chat group user went wrong!');
        }
    }

    protected function deleteXmppChatGroupMember(
        $chatGroup,
        $members
    ) {
        try {
            $chatRoomId = $chatGroup->getId();
            $role = 'members';
            $serviceName = $chatGroup->getTag() ? $chatGroup->getTag() : ChatGroup::XMPP_SERVICE;
            $service = $this->get('openfire.service');

            foreach ($members as $member) {
                $memberId = $member->getXmppUsername();
                $service->deleteUserInChatRoomWithSpecificService(
                    $chatRoomId,
                    $role,
                    $memberId,
                    $serviceName
                );
            }
        } catch (\Exception $e) {
            error_log('Delete XMPP chat group user went wrong!');
        }
    }

    /**
     * @param ChatGroup $chatGroup
     * @param User      $user
     * @param bool      $mute
     *
     * @return mixed|void
     */
    protected function handleXmppChatGroupMute(
        $chatGroup,
        $user,
        $mute
    ) {
        try {
            // get globals
            $twig = $this->container->get('twig');
            $globals = $twig->getGlobals();

            $domain = $globals['xmpp_domain'];
            $id = $chatGroup->getId();
            $type = ChatGroup::XMPP_SERVICE;

            if (!is_null($chatGroup->getTag())) {
                $type = ChatGroup::XMPP_CUSTOMER_SERVICE;
            }

            $targetJid = "$id".'@'.$type.'.'.$domain;
            $userJid = $user->getXmppUsername().'@'.$domain;

            // request json
            $jsonDataArray = array(
                'user_jid' => $userJid,
                'target_jid' => $targetJid,
                'mute' => $mute,
            );
            $jsonData = json_encode($jsonDataArray);

            // call openfire chat config api
            $this->callOpenfireChatConfigApi($jsonData);
        } catch (\Exception $e) {
            error_log('Update XMPP chat group went wrong!');
        }
    }

    /**
     * @param object $jsonData
     *
     * @return mixed|void
     */
    protected function callOpenfireChatConfigApi(
        $jsonData
    ) {
        try {
            // get globals
            $twig = $this->container->get('twig');
            $globals = $twig->getGlobals();

            // openfire API URL
            $apiURL = $globals['openfire_innet_url'].
                $globals['openfire_plugin_bstios'].
                $globals['openfire_plugin_bstios_chatconfig'];

            // init curl
            $ch = curl_init($apiURL);

            // get then response when post OpenFire API
            $response = $this->callAPI($ch, 'POST', null, $jsonData);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode != self::HTTP_STATUS_OK) {
                return;
            }

            return $response;
        } catch (\Exception $e) {
            error_log('Call Openfire Chat Config API went wrong!');
        }
    }
}
