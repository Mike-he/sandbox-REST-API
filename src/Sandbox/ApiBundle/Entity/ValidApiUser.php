<?php

namespace Sandbox\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ValidApiUser
 *
 * @ORM\Table(name="ValidApiUserView", uniqueConstraints={@ORM\UniqueConstraint(name="secretDigest_UNIQUE", columns={"secretDigest"})})
 * @ORM\Entity
 */
class ValidApiUser implements UserInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="uii", type="string", length=32, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $uii;

    /**
     * @var integer
     *
     * @ORM\Column(name="userId", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $userid;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=64, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="secretDigest", type="string", length=64, nullable=false)
     */
    private $secretdigest;

    /**
     * @var string
     *
     * @ORM\Column(name="creationdate", type="string", length=15, nullable=false)
     */
    private $creationdate;

    /**
     * @var string
     *
     * @ORM\Column(name="jid", type="text", nullable=false)
     */
    private $jid;

    /**
     * Set uii
     *
     * @param  string       $uii
     * @return ValidApiUser
     */
    public function setUii($uii)
    {
        $this->uii = $uii;

        return $this;
    }

    /**
     * Get uii
     *
     * @return string
     */
    public function getUii()
    {
        return $this->uii;
    }

    /**
     * Set userid
     *
     * @param  string       $userid
     * @return ValidApiUser
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
     * Set username
     *
     * @param  string       $username
     * @return ValidApiUser
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set secretdigest
     *
     * @param  string       $secretdigest
     * @return ValidApiUser
     */
    public function setSecretdigest($secretdigest)
    {
        $this->secretdigest = $secretdigest;

        return $this;
    }

    /**
     * Get secretdigest
     *
     * @return string
     */
    public function getSecretdigest()
    {
        return $this->secretdigest;
    }

    /**
     * Set creationdate
     *
     * @param  string       $creationdate
     * @return ValidApiUser
     */
    public function setCreationdate($creationdate)
    {
        $this->creationdate = $creationdate.'000';

        return $this;
    }

    /**
     * Get creationdate
     *
     * @return string
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * Set jid
     *
     * @param  string       $jid
     * @return ValidApiUser
     */
    public function setJid($jid)
    {
        $this->jid = $jid;

        return $this;
    }

    /**
     * Get jid
     *
     * @return string
     */
    public function getJid()
    {
        return $this->jid;
    }

    ///////////////////////////
    // Interface UserInterface
    ///////////////////////////

    public function getSalt()
    {
        return;
    }

    public function getPassword()
    {

        // we use sharedsecret as username
        // and uii as password because
        // sharedsecret is unique and password is not
        return $this->uii;
    }

    public function getRoles()
    {
        return array('ROLE_API');
    }

    public function eraseCredentials()
    {
    }
}
