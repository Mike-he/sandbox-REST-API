<?php

namespace Sandbox\ClientApiBundle\Data\User;

/**
 * Email Binding Verify Incoming Data.
 */
class EmailBindingVerify
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $code;

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return EmailBindingVerify
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return EmailBindingVerify
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
