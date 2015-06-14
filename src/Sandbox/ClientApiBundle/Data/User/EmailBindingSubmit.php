<?php

namespace Sandbox\ClientApiBundle\Data\User;

/**
 * Email Binding Submit Incoming Data
 */
class EmailBindingSubmit
{
    /**
     * @var string
     */
    private $email;

    /**
     * Set email
     *
     * @param  string             $email
     * @return EmailBindingSubmit
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
}
