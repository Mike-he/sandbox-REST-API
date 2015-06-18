<?php

namespace Sandbox\ClientApiBundle\Data\User;

/**
 * Register Submit Incoming Data
 */
class RegisterSubmit
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
     * @param  string         $email
     * @return RegisterSubmit
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
     * @param  string         $phone
     * @return RegisterSubmit
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
