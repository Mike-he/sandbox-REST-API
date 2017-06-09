<?php

namespace jean553\OpenfireBundle\Services;

use jean553\OpenfireBundle\Logic\OpenfireClient;

use GuzzleHttp\Exception\RequestException;

class OpenfireService
{
    const REQUEST_ERROR_MESSAGE = "Openfire server error.";

    /**
     * @var OpenfireClient
     */
    private $client;

    /**
     * @param OpenfireClient $client
     */
    public function __construct(
        OpenfireClient $client,
        $config
    ) {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Create a new user with the given username
     * if this user does not already exists
     *
     * @param string $username
     * @param string $password
     *
     * @throws Exception
     */
    public function createUser(
        $username,
        $password
    ) {

        try {
            $this->client->request(
                'post',
                '/users',
                array(
                    'username' => $username,
                    'password' => $password
                )
            );
        } catch (RequestException $e) {
            throw new \Exception(self::REQUEST_ERROR_MESSAGE);
        }
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @throws Exception
     */
    public function editUserPassword(
        $username,
        $password
    ) {

        try {
            $this->client->request(
                'put',
                '/users/'.$username,
                array(
                    'password' => $password
                )
            );
        } catch (RequestException $e) {
            throw new \Exception(self::REQUEST_ERROR_MESSAGE);
        }
    }

    /**
     * @param integer $chatRoomId
     * @param string $chatRoomName
     * @param string $ownerName
     */
    public function createChatRoom(
        $chatRoomId,
        $chatRoomName,
        $ownerName
    ) {

        $ownerInfos =
            $ownerName.'@'.$this->config['servicename'].'.'.$this->config['servername'];

        $this->client->request(
            'post',
            '/chatrooms',
            array(
                'roomName' => $chatRoomId,
                'naturalName' => $chatRoomName,
                'description' => $chatRoomName,
                'owners' => array(
                    'owner' => $ownerInfos
                ),
                'persistent' => true
            )
        );
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

        $ownerInfos =
            $ownerName.'@'.$serviceName.'.'.$this->config['servername'];

        $this->client->request(
            'post',
            '/chatrooms?servicename='.$serviceName,
            array(
                'roomName' => $chatRoomId,
                'naturalName' => $chatRoomName,
                'description' => $chatRoomName,
                'owners' => array(
                    'owner' => $ownerInfos
                ),
                'persistent' => true
            )
        );
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

        $this->client->request(
            'post',
            '/chatrooms/'.$chatRoomName.'/'.$role.'/'.$username,
            array()
        );
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

        $this->client->request(
            'post',
            '/chatrooms/'.$chatRoomName.'/'.$role.'/'.$username.'?servicename='.$serviceName,
            array()
        );
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

        $this->client->request(
            'delete',
            '/chatrooms/'.$chatRoomName.'/'.$role.'/'.$username,
            array()
        );
    }

    /**
     * @param string $chatRoomName
     */
    public function deleteChatRoom($chatRoomName)
    {

        $this->client->request(
            'delete',
            '/chatrooms/'.$chatRoomName,
            array()
        );
    }

    /**
     * @param string $chatRoomName
     * @param string $serviceName
     */
    public function deleteChatRoomWithSpecificService(
        $chatRoomName,
        $serviceName
    ) {

        $this->client->request(
            'delete',
            '/chatrooms/'.$chatRoomName.'?servicename='.$serviceName,
            array()
        );
    }

    /**
     * @param string $username Openfire username for the user
     *
     * @return string
     */
    public function getUserJID($username)
    {
        return $username.'@'.$this->config['servername'];
    }

    /**
     * @param string $chatRoomId
     * @param string $chatRoomName
     *
     * TODO: to delete ? replaced by putChatRoom()
     */
    public function putChatRoomName(
        $chatRoomId,
        $chatRoomName
    ) {
        $this->client->request(
            'put',
            '/chatrooms/'.$chatRoomId,
            array(
                'roomName' => $chatRoomId,
                'naturalName' => $chatRoomName,
                'description' => $chatRoomName,
                'persistent' => true
            )
        );
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

        // TODO: For some reasons, the provided data is an 
        // associative array sometimes, we change it into
        // a single array.
    
        $membersIds = array_values($membersIds);
        
        $membersNames = array_map(function($memberId) {
            return $this->getUserJID($memberId);
        }, $membersIds);

        $ownerInfos =
            $ownerName.'@'.$this->config['servicename'].'.'.$this->config['servername'];

        $this->client->request(
            'put',
            '/chatrooms/'.$chatRoomId,
            array(
                'roomName' => $chatRoomId,
                'naturalName' => $chatRoomName,
                'description' => $chatRoomName,
                'persistent' => true,
                'members' => [
                    'member' => $membersNames
                ],
                'owners' => [
                    'owner' => $ownerInfos 
                ]
            )
        );
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

        // TODO: For some reasons, the provided data is an 
        // associative array sometimes, we change it into
        // a single array.
    
        $membersIds = array_values($membersIds);
        
        $membersNames = array_map(function($memberId) {
            return $this->getUserJID($memberId);
        }, $membersIds);

        $ownerInfos =
            $ownerName.'@'.$serviceName.'.'.$this->config['servername'];

        $this->client->request(
            'put',
            '/chatrooms/'.$chatRoomId.'?servicename='.$serviceName,
            array(
                'roomName' => $chatRoomId,
                'naturalName' => $chatRoomName,
                'description' => $chatRoomName,
                'persistent' => true,
                'members' => [
                    'member' => $membersNames
                ],
                'owners' => [
                    'owner' => $ownerInfos 
                ]
            )
        );
    }
}
