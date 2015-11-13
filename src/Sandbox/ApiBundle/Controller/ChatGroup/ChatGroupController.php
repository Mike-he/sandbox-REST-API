<?php

namespace Sandbox\ApiBundle\Controller\ChatGroup;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroupMember;
use Sandbox\ApiBundle\Entity\User\User;

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
            // get globals
            $twig = $this->container->get('twig');
            $globals = $twig->getGlobals();

            $domain = $globals['xmpp_domain'];
            $jid = $chatGroup->getId().'@'.ChatGroup::XMPP_SERVICE.'.'.$domain;
            $owner = $chatGroup->getCreator()->getXmppUsername().'@'.$domain;
            $members = $this->addMembers($chatGroup, $domain);

            // request json
            $jsonDataArray = array(
                'service' => ChatGroup::XMPP_SERVICE,
                'jid' => $jid,
                'name' => $chatGroup->getName(),
                'owner' => $owner,
                'members' => $members,
            );
            $jsonData = json_encode($jsonDataArray);

            // call openfire room api
            $this->callOpenfireRoomApi('POST', $jsonData);
        } catch (\Exception $e) {
            error_log('Create XMPP chat group went wrong!');
        }
    }

    /**
     * @param ChatGroup $chatGroup
     * @param string    $domain
     *
     * @return array
     */
    private function addMembers(
        $chatGroup,
        $domain
    ) {
        $memberJids = array();

        $members = $this->getRepo('ChatGroup\ChatGroupMember')->findByChatGroup($chatGroup);
        foreach ($members as $member) {
            try {
                $memberJidArray = $this->setMemberJidArray($member->getUser(), $domain);
                array_push($memberJids, $memberJidArray);
            } catch (\Exception $e) {
                error_log('Add XMPP chat group member went wrong!');
                continue;
            }
        }

        return $memberJids;
    }

    /**
     * @param User   $user
     * @param string $domain
     *
     * @return array
     */
    private function setMemberJidArray(
        $user,
        $domain
    ) {
        return array('jid' => $user->getXmppUsername().'@'.$domain);
    }

    /**
     * @param ChatGroup $chatGroup
     * @param User      $user
     *
     * @return mixed|void
     */
    protected function updateXmppChatGroup(
        $chatGroup,
        $user
    ) {
        try {
            // get globals
            $twig = $this->container->get('twig');
            $globals = $twig->getGlobals();

            $domain = $globals['xmpp_domain'];
            $jid = $chatGroup->getId().'@'.ChatGroup::XMPP_SERVICE.'.'.$domain;
            $actor = $user->getXmppUsername().'@'.$domain;

            $room = array(
                'jid' => $jid,
                'name' => $chatGroup->getName(),
            );

            // request json
            $jsonDataArray = array(
                'actor' => $actor,
                'rooms' => array($room),
            );
            $jsonData = json_encode($jsonDataArray);

            // call openfire room api
            $this->callOpenfireRoomApi('PUT', $jsonData);
        } catch (\Exception $e) {
            error_log('Update XMPP chat group went wrong!');
        }
    }

    /**
     * @param ChatGroup $chatGroup
     * @param User      $user
     *
     * @return mixed|void
     */
    protected function deleteXmppChatGroup(
        $chatGroup,
        $user
    ) {
        try {
            // get globals
            $twig = $this->container->get('twig');
            $globals = $twig->getGlobals();

            $domain = $globals['xmpp_domain'];
            $jid = $chatGroup->getId().'@'.ChatGroup::XMPP_SERVICE.'.'.$domain;
            $actor = $user->getXmppUsername().'@'.$domain;

            $room = array('jid' => $jid);

            // request json
            $jsonDataArray = array(
                'actor' => $actor,
                'rooms' => array($room),
            );
            $jsonData = json_encode($jsonDataArray);

            // call openfire room api
            $this->callOpenfireRoomApi('DELETE', $jsonData);
        } catch (\Exception $e) {
            error_log('Update XMPP chat group went wrong!');
        }
    }

    /**
     * @param ChatGroup $chatGroup
     * @param User      $user
     * @param array     $members
     * @param string    $method
     *
     * @return mixed|void
     */
    protected function handleXmppChatGroupMember(
        $chatGroup,
        $user,
        $members,
        $method
    ) {
        try {
            // get globals
            $twig = $this->container->get('twig');
            $globals = $twig->getGlobals();

            $domain = $globals['xmpp_domain'];
            $jid = $chatGroup->getId().'@'.ChatGroup::XMPP_SERVICE.'.'.$domain;
            $actor = $user->getXmppUsername().'@'.$domain;

            $room = array('jid' => $jid);

            $membersArray = array();
            foreach ($members as $member) {
                $memberJidArray = $this->setMemberJidArray($member, $domain);
                array_push($membersArray, $memberJidArray);
            }

            // request json
            $jsonDataArray = array(
                'actor' => $actor,
                'rooms' => array($room),
                'members' => $membersArray,
            );
            $jsonData = json_encode($jsonDataArray);

            // call openfire room api
            $this->callOpenfireRoomApi($method, $jsonData, true);
        } catch (\Exception $e) {
            error_log('Update XMPP chat group went wrong!');
        }
    }

    /**
     * @param string $method
     * @param object $jsonData
     * @param bool   $member
     *
     * @return mixed|void
     */
    protected function callOpenfireRoomApi(
        $method,
        $jsonData,
        $member = false
    ) {
        try {
            // get globals
            $twig = $this->container->get('twig');
            $globals = $twig->getGlobals();

            // openfire API URL
            $apiURL = $globals['openfire_innet_url'].
                $globals['openfire_plugin_bstgroupchat'].
                $globals['openfire_plugin_bstgroupchat_room'];

            if ($member) {
                $apiURL = $apiURL.$globals['openfire_plugin_bstgroupchat_room_member'];
            }

            // init curl
            $ch = curl_init($apiURL);

            // get then response when post OpenFire API
            $response = $this->get('curl_util')->callAPI($ch, $method, null, $jsonData);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode != self::HTTP_STATUS_OK) {
                return;
            }

            return $response;
        } catch (\Exception $e) {
            error_log('Call Openfire Room API went wrong!');
        }
    }
}
