<?php

namespace Sandbox\ClientApiBundle\Data\User;

use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;

/**
 * User Login Incoming Data.
 */
class UserLoginData
{
    /**
     * @var UserClient
     */
    private $client;

    /**
     * @var UserLoginDeviceData
     */
    private $device;

    /**
     * @var ThirdPartyOAuthWeChatData
     */
    private $wechat;

    /**
     * @return UserClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param UserClient $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return UserLoginDeviceData
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param UserLoginDeviceData $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }

    /**
     * @return ThirdPartyOAuthWeChatData
     */
    public function getWechat()
    {
        return $this->wechat;
    }

    /**
     * @param ThirdPartyOAuthWeChatData $wechat
     */
    public function setWechat($wechat)
    {
        $this->wechat = $wechat;
    }
}
