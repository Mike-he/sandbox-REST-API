<?php

namespace jean553\OpenfireBundle\Services;

class DummyOpenfireService
{
    /**
     * @param string $username
     * @param string $password
     */
    public function createUser(
        $username,
        $password
    ) {
    }

    /**
     * @param string $username
     * @param string $password
     */
    public function editUserPassword(
        $username,
        $password
    ) {
    }

    /**
     * @param string $chatRoomName
     * @param string $ownerName
     */
    public function createChatRoom(
        $chatRoomName,
        $ownerName
    ) {
    }

    /**
     * @param string $chatRoomName
     * @param string $role
     * @param string $username
     */
    public function addUserInChatRoom(
        $chatRoomName,
        $role,
        $username
    ) {
    }

    /**
     * @param string $chatRoomName
     * @param string $role
     * @param string $username
     */
    public function deleteUserInChatRoom(
        $chatRoomName,
        $role,
        $username
    ) {
    }

    /**
     * @param integer $chatRoomId
     * @param string $chatRoomName
     * @param string $ownerName
     * @param string $serviceName
     */
    public function createChatRoomWithSpecificService(
        $chatRoomId,
        $chatRoomName,
        $ownerName,
        $serviceName
    ) {
    }

    /**
     * @param string $chatRoomName
     * @param string $role
     * @param string $username
     * @param string $serviceName
     */
    public function addUserInChatRoomWithSpecificService(
        $chatRoomName,
        $role,
        $username,
        $serviceName
    ) {
    }

    /**
     * @param string $username Openfire username for the user
     *
     * @return string
     */
    public function getUserJID($username)
    {
        return $username.'@localhost';
    }

    /**
     * @param string $chatRoomName
     */
    public function deleteChatRoom(
        $chatRoomName
    ) {
    }

    /**
     * @param string $chatRoomName
     * @param string $serviceName
     */
    public function deleteChatRoomWithSpecificService(
        $chatRoomName,
        $serviceName
    ) {
    }

    /**
     * @param string $chatRoomId
     * @param string $chatRoomName
     */
    public function putChatRoomName(
        $chatRoomId,
        $chatRoomName
    ) {
    }

    /**
     * @param integer $chatRoomId
     * @param string $chatRoomName
     * @param array $membersIds
     * @param string $ownerName
     */
    public function putChatRoom(
        $chatRoomId,
        $chatRoomName,
        $membersIds,
        $ownerName
    ) {
    }

    /**
     * @param integer $chatRoomId
     * @param string $chatRoomName
     * @param array $membersIds
     * @param string $ownerName
     * @param string $serviceName
     */
    public function putChatRoomWithSpecificService(
        $chatRoomId,
        $chatRoomName,
        $membersIds,
        $ownerName,
        $serviceName
    ) {
    }
}
