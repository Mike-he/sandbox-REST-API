<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(
 *      name="User",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="username_UNIQUE", columns={"username"}),
 *          @ORM\UniqueConstraint(name="email_UNIQUE", columns={"email"}),
 *          @ORM\UniqueConstraint(name="phone_UNIQUE", columns={"phone"})
 *      }
 * )
 * @ORM\Entity
 */
class User
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="xmppUsername", type="string", length=64, nullable=true)
     */
    private $xmppUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=256, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=128, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64, nullable=true)
     */
    private $phone;

    /**
     * @var boolean
     *
     * @ORM\Column(name="banned", type="boolean", nullable=false)
     */
    private $banned = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     */
    private $modificationDate;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set xmppUsername
     *
     * @param  string $xmppUsername
     * @return User
     */
    public function setXmppUsername($xmppUsername)
    {
        $this->xmppUsername = $xmppUsername;

        return $this;
    }

    /**
     * Get xmppUsername
     *
     * @return string
     */
    public function getXmppUsername()
    {
        return $this->xmppUsername;
    }

    /**
     * Set password
     *
     * @param  string $password
     * @return User
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

    /**
     * Set email
     *
     * @param  string $email
     * @return User
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
     * @param  string $phone
     * @return User
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

    /**
     * Set banned
     *
     * @param  boolean $banned
     * @return User
     */
    public function setBanned($banned)
    {
        $this->banned = $banned;

        return $this;
    }

    /**
     * Get banned
     *
     * @return boolean
     */
    public function getBanned()
    {
        return $this->banned;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set creationDate
     *
     * @param  \DateTime $creationDate
     * @return User
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * Get modificationDate
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set modificationDate
     *
     * @param  \DateTime $modificationDate
     * @return User
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    public function __construct()
    {
        $this->setCreationDate(new \DateTime("now"));
        $this->setModificationDate(new \DateTime("now"));
    }
}
