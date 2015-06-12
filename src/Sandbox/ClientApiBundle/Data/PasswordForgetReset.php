<?php

namespace Sandbox\ClientApiBundle\Data;

/**
 * Password Forget Reset Incoming Data
 */
class PasswordForgetReset
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $password;

    /**
     * Set token
     *
     * @param  string              $token
     * @return PasswordForgetReset
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set password
     *
     * @param  string              $password
     * @return PasswordForgetReset
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}
