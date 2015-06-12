<?php

namespace Sandbox\ApiBundle\Entity\IncomingData;

/**
 * Email Binding Verify Incoming Data
 */
class EmailBindingVerify
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $code;

    /**
     * Set token
     *
     * @param  string             $token
     * @return EmailBindingVerify
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
     * Set code
     *
     * @param  string             $code
     * @return EmailBindingVerify
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
