<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * User view.
 *
 * @ORM\Table(name="user_view")
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\User\UserViewRepository"
 * )
 */
class UserView
{
    const DATE_TYPE_AUTHORIZED = 'authorized_date';
    const DATE_TYPE_REGISTRATION = 'registration_date';
    const DATE_TYPE_BIND_CARD = 'bind_card_date';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "account", "client", "room_usage"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=128, nullable=true)
     * @Serializer\Groups({"main", "account", "client", "room_usage"})
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main", "account", "client", "room_usage"})
     */
    private $phone;

    /**
     * @var bool
     *
     * @ORM\Column(name="banned", type="boolean", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $banned;

    /**
     * @var bool
     *
     * @ORM\Column(name="authorized", type="boolean", nullable=false)
     * @Serializer\Groups({"main", "account"})
     */
    private $authorized;

    /**
     * @var string
     *
     * @ORM\Column(name="cardNo", type="string", length=32, nullable=true)
     * @Serializer\Groups({"main", "lease_bill"})
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
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "account", "client", "room_usage", "lease_bill"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $gender;

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
     * @var string
     *
     * @ORM\Column(name="userRegistrationDate", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $userRegistrationDate;

    /**
     * @var array
     */
    private $building;

    /**
     * @var float
     */
    private $salesInvoiceAmount;

    private $groups;

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
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
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
     * @param $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
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
     * Get credentialNo.
     *
     * @return string
     */
    public function getCredentialNo()
    {
        return $this->credentialNo;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
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
     * @return string
     */
    public function getUserRegistrationDate()
    {
        return $this->userRegistrationDate;
    }

    /**
     * @param string $userRegistrationDate
     */
    public function setUserRegistrationDate($userRegistrationDate)
    {
        $this->userRegistrationDate = $userRegistrationDate;
    }

    /**
     * @return array
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param array $building
     */
    public function setBuilding($building)
    {
        $this->building = $building;
    }

    /**
     * @return float
     */
    public function getSalesInvoiceAmount()
    {
        return $this->salesInvoiceAmount;
    }

    /**
     * @param float $salesInvoiceAmount
     */
    public function setSalesInvoiceAmount($salesInvoiceAmount)
    {
        $this->salesInvoiceAmount = $salesInvoiceAmount;
    }

    /**
     * @return mixed
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param mixed $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }
}
