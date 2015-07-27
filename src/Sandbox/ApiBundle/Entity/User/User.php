<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * User.
 *
 * @ORM\Table(
 *      name="User",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="xmppUsername_UNIQUE", columns={"xmppUsername"}),
 *          @ORM\UniqueConstraint(name="email_UNIQUE", columns={"email"}),
 *          @ORM\UniqueConstraint(name="phone_UNIQUE", columns={"phone"})
 *      }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\User\UserRepository"
 * )
 */
class User implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "login", "company_info"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="xmppUsername", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main", "login", "company_info"})
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
     * @Serializer\Groups({"main", "login"})
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main", "login"})
     */
    private $phone;

    /**
     * @var bool
     *
     * @ORM\Column(name="banned", type="boolean", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $banned = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $modificationDate;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set xmppUsername.
     *
     * @param string $xmppUsername
     *
     * @return User
     */
    public function setXmppUsername($xmppUsername)
    {
        $this->xmppUsername = $xmppUsername;

        return $this;
    }

    /**
     * Get xmppUsername.
     *
     * @return string
     */
    public function getXmppUsername()
    {
        return $this->xmppUsername;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return User
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

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return User
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
     * Set phone.
     *
     * @param string $phone
     *
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set banned.
     *
     * @param bool $banned
     *
     * @return User
     */
    public function setBanned($banned)
    {
        $this->banned = $banned;

        return $this;
    }

    /**
     * Is banned.
     *
     * @return bool
     */
    public function isBanned()
    {
        return $this->banned;
    }

    /**
     * Get creationDate.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return User
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * Get modificationDate.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return User
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    public function __construct()
    {
        $this->setCreationDate(new \DateTime('now'));
        $this->setModificationDate(new \DateTime('now'));
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->xmppUsername;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return array('ROLE_USER');
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}
