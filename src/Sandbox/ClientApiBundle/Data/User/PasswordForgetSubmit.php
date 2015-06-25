<?php

namespace Sandbox\ClientApiBundle\Data\User;

/**
 * Password Forget Submit Incoming Data
 */
class PasswordForgetSubmit
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $phone;

    /**
     * Set email
     *
     * @param  string               $email
     * @return PasswordForgetSubmit
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param  string               $phone
     * @return PasswordForgetSubmit
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }
}
