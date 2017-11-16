<?php

namespace Sandbox\ApiBundle\Service;

use JMessage\Cross\Member;
use JMessage\IM\Friend;
use JMessage\IM\Resource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JMessage\JMessage;
use JMessage\IM\User;
use JMessage\IM\Group;
use JMessage\IM\Report;

/**
 * Class JmessageService.
 */
class JmessageServiceProperty
{
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->appKey = $this->container->getParameter('jpush_property_key');
        $this->masterSecret = $this->container->getParameter('jpush_property_secret');
        $this->client = new JMessage($this->appKey, $this->masterSecret);
        $this->user = new User($this->client);
        $this->group = new Group($this->client);
        $this->resource = new Resource($this->client);
        $this->report = new Report($this->client);
        $this->friend = new Friend($this->client);
        $this->member = new Member($this->client);
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
            $respone = $this->group->addMembers($gid, $usernames);
        } else {
            $respone = $this->member->add($gid, $appKey, $usernames);
        }

        return $respone;
    }

    public function deleteGroupMembers(
        $gid,
        $usernames
    ) {
        $this->group->removeMembers($gid, $usernames);
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
}
