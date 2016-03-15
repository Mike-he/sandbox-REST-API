<?php

namespace Sandbox\ApiBundle\Entity\Auth;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminAuth.
 */
class AdminAuth
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * Set username.
     *
     * @param string $username
     *
     * @return AdminAuth
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return AdminAuth
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}
