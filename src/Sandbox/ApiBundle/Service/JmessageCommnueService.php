<?php

namespace Sandbox\ApiBundle\Service;

use JMessage\Cross\Member;
use JMessage\IM\Friend;
use JMessage\IM\Message;
use JMessage\IM\Resource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JMessage\JMessage;
use JMessage\IM\User;
use JMessage\IM\Group;
use JMessage\IM\Report;

/**
 * Class JmessageService.
 */
class JmessageCommnueService
{
    /**
     * @var mixed
     */
    private $errorLogDir;

    /**
     * @var mixed
     */
    private $appKey;

    /**
     * @var mixed
     */
    private $masterSecret;

    /**
     * @var JMessage
     */
    private $client;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Group
     */
    private $group;

    /**
     * @var resource
     */
    private $resource;

    /**
     * @var Report
     */
    private $report;

    /**
     * @var Friend
     */
    private $friend;

    /**
     * @var Member
     */
    private $member;

    /**
     * @var Message
     */
    private $message;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->appKey = $this->container->getParameter('jpush_commnue_key');
        $this->masterSecret = $this->container->getParameter('jpush_commnue_secret');
        $this->client = new JMessage($this->appKey, $this->masterSecret);
        $this->user = new User($this->client);
        $this->group = new Group($this->client);
        $this->resource = new Resource($this->client);
        $this->report = new Report($this->client);
        $this->friend = new Friend($this->client);
        $this->member = new Member($this->client);
        $this->message = new Message($this->client);
    }

    public function createUser(
        $username,
        $password,
        $nickname = null
    ) {
        $this->user->register($username, $password, $nickname);
    }

    public function updatePassword(
        $username,
        $password
    ) {
        $result = $this->user->updatePassword($username, $password);

        return $result;
    }

    public function updateNickname(
        $username,
        $nickname
    ) {
        $options = [
            'nickname' => $nickname,
        ];

        $this->user->update($username, $options);
    }

    public function updateUserInfo(
        $username,
        $options
    ) {
        $response = $this->user->update($username, $options);

        return $response;
    }

    public function createGroup(
        $owner,
        $name,
        $desc,
        $members
    ) {
        $response = $this->group->create($owner, $name, $desc, $members);

        return $response;
    }

    public function updateGroup(
        $gid,
        $name,
        $desc
    ) {
        $this->group->update($gid, $name, $desc);
    }

    public function deleteGroup(
        $gid
    ) {
        $this->group->delete($gid);
    }

    public function addGroupMembers(
        $gid,
        $usernames,
        $appKey = null
    ) {
        if (is_null($appKey)) {
            $response = $this->group->addMembers($gid, $usernames);
        } else {
            $response = $this->member->add($gid, $appKey, $usernames);
        }

        return $response;
    }

    public function deleteGroupMembers(
        $gid,
        $usernames,
        $appKey = null
    ) {
        if (is_null($appKey)) {
            $response = $this->group->removeMembers($gid, $usernames);
        } else {
            $response = $this->member->remove($gid, $appKey, $usernames);
        }

        return $response;
    }

    public function getMedia(
        $mediaId
    ) {
        return $this->resource->download($mediaId);
    }

    public function getMessages(
        $beginTime,
        $endTime
    ) {
        $response = $this->report->getMessages(
            0,
            1000,
            $beginTime,
            $endTime
        );

        return $response;
    }

    public function getUserMessages(
        $user,
        $beginTime,
        $endTime
    ) {
        $response = $this->report->getUserMessages(
            $user,
            0,
            1000,
            $beginTime,
            $endTime
        );

        return $response;
    }

    public function addFriends(
        $user,
        $friends
    ) {
        $this->friend->add($user, $friends);
    }

    public function sendTxtMessage(
        $xmppUser,
        $txt
    ) {
        $version = 1;

        $from = [
            'id' => 'commnue',
            'name' => '合创社',
            'type' => 'admin',
        ];

        $target = [
            'id' => $xmppUser,
            'type' => 'single',
        ];
        $msg = [
            'text' => $txt,
        ];

        $result = $this->message->sendText($version, $from, $target, $msg);

        return $result;
    }
}
