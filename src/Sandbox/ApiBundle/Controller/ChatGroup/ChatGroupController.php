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

            // openfire API URL
            $apiURL = $globals['openfire_innet_url'].
                $globals['openfire_plugin_bstgroupchat'].
                $globals['openfire_plugin_bstgroupchat_room'];

            $jid = $chatGroup->getId().'@'.ChatGroup::XMPP_SERVICE.'.'.$domain;

            $owner = $chatGroup->getCreator()->getXmppUsername().'@'.$domain;

            $members = $this->addXmppChatGroupMember($chatGroup, $domain);

            // request json
            $jsonDataArray = array(
                'service' => ChatGroup::XMPP_SERVICE,
                'jid' => $jid,
                'name' => $chatGroup->getName(),
                'owner' => $owner,
                'members' => $members,
            );
            $jsonData = json_encode($jsonDataArray);

            // init curl
            $ch = curl_init($apiURL);

            // get then response when post OpenFire API
            $response = $this->get('curl_util')->callAPI(
                $ch,
                'POST',
                null,
                $jsonData);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode != self::HTTP_STATUS_OK) {
                return;
            }

            return $response;
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
    protected function addXmppChatGroupMember(
        $chatGroup,
        $domain
    ) {
        $memberJids = array();

        $members = $this->getRepo('ChatGroup\ChatGroupMember')->findByChatGroup($chatGroup);
        foreach ($members as $member) {
            try {
                $memberJid = $member->getUser()->getXmppUsername().'@'.$domain;
                array_push($memberJids, $memberJid);
            } catch (\Exception $e) {
                error_log('Add XMPP chat group member went wrong!');
                continue;
            }
        }

        return $memberJids;
    }
}
