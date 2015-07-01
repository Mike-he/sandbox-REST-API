<?php

namespace Sandbox\ClientApiBundle\Data\User;

/**
 * email Incoming Data.
 */
class Email
{
    /**
     * @var string
     */
    private $email;

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return Email
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
}
