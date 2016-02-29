<?php

namespace Sandbox\ClientApiBundle\Data\User;

/**
 * User Login Incoming Data.
 */
class UserLoginData
{
    /**
     * @var UserLoginClientData
     */
    private $client;

    /**
     * @var UserLoginDeviceData
     */
    private $device;

    /**
     * @return UserLoginClientData
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param UserLoginClientData $client
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
}
