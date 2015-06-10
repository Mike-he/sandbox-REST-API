<?php

namespace Sandbox\ApiBundle\Entity\IncomingData;

/**
 * Password Forget Reset Incoming Data
 */
class PasswordChange
{
    /**
     * @var string
     */
    private $userid;

    /**
     * @var string
     */
    private $currentpassword;

    /**
     * @var string
     */
    private $newpassword;

    /**
     * @var string
     */
    private $fulljid;

    /**
     * Set userid
     *
     * @param  string         $userid
     * @return PasswordChange
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Get userid
     *
     * @return string
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Set currentpassword
     *
     * @param  string         $currentpassword
     * @return PasswordChange
     */
    public function setCurrentpassword($currentpassword)
    {
        $this->currentpassword = $currentpassword;

        return $this;
    }

    /**
     * Get currentpassword
     *
     * @return string
     */
    public function getCurrentpassword()
    {
        return $this->currentpassword;
    }

    /**
     * Set newpassword
     *
     * @param  string         $newpassword
     * @return PasswordChange
     */
    public function setNewpassword($newpassword)
    {
        $this->newpassword = $newpassword;

        return $this;
    }

    /**
     * Get newpassword
     *
     * @return string
     */
    public function getNewpassword()
    {
        return $this->newpassword;
    }

    /**
     * Set fulljid
     *
     * @param  string         $fulljid
     * @return PasswordChange
     */
    public function setFulljid($fulljid)
    {
        $this->fulljid = $fulljid;

        return $this;
    }

    /**
     * Get fulljid
     *
     * @return string
     */
    public function getFulljid()
    {
        return $this->fulljid;
    }
}
