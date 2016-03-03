<?php

namespace Sandbox\ClientApiBundle\Data\User;

/**
 * User Login Device Incoming Data.
 */
class UserLoginDeviceData
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $platform;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }
}
