<?php

namespace Sandbox\ApiBundle\Service;

use JMessage\IM\Resource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JMessage\JMessage;
use JMessage\IM\User;
use JMessage\IM\Group;

/**
 * Class JmessageService.
 */
class JmessageService
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
     * @var Resource
     */
    private $resource;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->appKey = $this->container->getParameter('jpush_key');
        $this->masterSecret = $this->container->getParameter('jpush_secret');
        $this->client = new JMessage($this->appKey, $this->masterSecret);
        $this->user = new User($this->client);
        $this->group = new Group($this->client);
        $this->resource = new Resource($this->client);
    }

    public function createUser(
        $username,
        $password
    ) {
        $this->user->register($username, $password);
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
        $usernames
    ) {
        $this->group->addMembers($gid, $usernames);
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
}
