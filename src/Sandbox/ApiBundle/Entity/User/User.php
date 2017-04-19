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
 *      name="user",
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
    const XMPP_SERVICE = 'service';
    const ERROR_NOT_FOUND = 'User Not Found';

    const AUTHORIZED_PLATFORM_OFFICIAL = 'official';
    const AUTHORIZED_PLATFORM_SALES = 'sales';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "login", "buddy", "client_evaluation", "admin_detail", "admin_view"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="xmppUsername", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main", "login"})
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
     * @Serializer\Groups({"main", "login", "buddy", "admin_detail"})
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phoneCode", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main", "login", "buddy", "admin_detail", "admin_view"})
     */
    private $phoneCode;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main", "login", "buddy", "admin_detail", "admin_view"})
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
     * @var bool
     *
     * @ORM\Column(name="authorized", type="boolean", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $authorized = false;

    /**
     * @var string
     *
     * @ORM\Column(name="cardNo", type="string", length=32, nullable=true)
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $cardNo;

    /**
     * @var string
     *
     * @ORM\Column(name="credentialNo", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $credentialNo;

    /**
     * @var string
     *
     * @ORM\Column(name="authorizedPlatform", type="string", length=32, nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $authorizedPlatform;

    /**
     * @var int
     *
     * @ORM\Column(name="authorizedAdminUsername", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $authorizedAdminUsername;

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
     * @var string
     *
     * @ORM\Column(name="customerId", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $customerId;

    /**
     * @var UserProfile
     *
     * @ORM\OneToOne(targetEntity="UserProfile", mappedBy="user")
     * @Serializer\Groups({"main", "admin_view"})
     */
    private $userProfile;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="banned_date", type="datetime", nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $bannedDate;

    /**
     * @var float
     *
     * @ORM\Column(name="bean", type="float", options={"default": 0})
     * @Serializer\Groups({"main"})
     */
    private $bean = 0;

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
     * @return string
     */
    public function getPhoneCode()
    {
        return $this->phoneCode;
    }

    /**
     * @param string $phoneCode
     */
    public function setPhoneCode($phoneCode)
    {
        $this->phoneCode = $phoneCode;
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
     * Set authorized.
     *
     * @param bool $authorized
     *
     * @return User
     */
    public function setAuthorized($authorized)
    {
        $this->authorized = $authorized;

        return $this;
    }

    /**
     * Is authorized.
     *
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->authorized;
    }

    /**
     * Get cardNo.
     *
     * @return string
     */
    public function getCardNo()
    {
        return $this->cardNo;
    }

    /**
     * Set cardNo.
     *
     * @param string $cardNo
     *
     * @return User
     */
    public function setCardNo($cardNo)
    {
        $this->cardNo = $cardNo;

        return $this;
    }

    /**
     * Get credentialNo.
     *
     * @return string
     */
    public function getCredentialNo()
    {
        return $this->credentialNo;
    }

    /**
     * Set credentialNo.
     *
     * @param string $credentialNo
     *
     * @return User
     */
    public function setCredentialNo($credentialNo)
    {
        $this->credentialNo = $credentialNo;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorizedPlatform()
    {
        return $this->authorizedPlatform;
    }

    /**
     * @param string $authorizedPlatform
     */
    public function setAuthorizedPlatform($authorizedPlatform)
    {
        $this->authorizedPlatform = $authorizedPlatform;
    }

    /**
     * @return int
     */
    public function getAuthorizedAdminUsername()
    {
        return $this->authorizedAdminUsername;
    }

    /**
     * @param int $authorizedAdminUsername
     */
    public function setAuthorizedAdminUsername($authorizedAdminUsername)
    {
        $this->authorizedAdminUsername = $authorizedAdminUsername;
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
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
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

    /**
     * Get customerId.
     *
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Set customerId.
     *
     * @param string $customerId
     *
     * @return User
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return UserProfile
     */
    public function getUserProfile()
    {
        return $this->userProfile;
    }

    /**
     * @param UserProfile $userProfile
     */
    public function setUserProfile($userProfile)
    {
        $this->userProfile = $userProfile;
    }

    /**
     * @return \DateTime
     */
    public function getBannedDate()
    {
        return $this->bannedDate;
    }

    /**
     * @param \DateTime $bannedDate
     */
    public function setBannedDate($bannedDate)
    {
        $this->bannedDate = $bannedDate;
    }

    /**
     * @return float
     */
    public function getBean()
    {
        return $this->bean;
    }

    /**
     * @param float $bean
     */
    public function setBean($bean)
    {
        $this->bean = $bean;
    }
}
