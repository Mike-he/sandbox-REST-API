<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SalesCompany.
 *
 * @ORM\Table(name="sales_company")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\SalesAdmin\SalesCompanyRepository")
 */
class SalesCompany
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "admin_list", "admin", "dropdown", "client", "auth", "admin_detail", "admin_shop"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     *
     * @Serializer\Groups({"main", "admin_list", "admin", "auth", "dropdown", "client", "admin_detail", "client_event", "admin_shop"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="contacter", type="string", length=64)
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $contacter;

    /**
     * @var string
     *
     * @ORM\Column(name="contacter_phone", type="string", length=64)
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $contacterPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64)
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="contacter_email", type="string", length=255)
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $contacterEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255)
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="banned", type="boolean", nullable=false)
     * @Serializer\Groups({"main", "admin", "admin_list"})
     */
    private $banned = false;

    /**
     * @var string
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $admin;

    /**
     * @var string
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $coffeeAdmin;

    /**
     * @var int
     *
     * @Serializer\Groups({"main", "admin", "admin_list"})
     */
    private $buildingCounts;

    /**
     * @var int
     *
     * @Serializer\Groups({"main", "admin", "admin_list"})
     */
    private $shopCounts;

    /**
     * @var string
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $permissions;

    /**
     * @var bool
     *
     * @Serializer\Groups({"main", "admin", "admin_list"})
     */
    private $hasPendingBuilding = false;

    /**
     * @var bool
     *
     * @Serializer\Groups({"main", "admin", "admin_list"})
     */
    private $hasPendingShop = false;

    /**
     * @var bool
     *
     * @Serializer\Groups({"main", "admin", "admin_list"})
     */
    private $hasEventModule = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modification_date", type="datetime")
     * @Gedmo\Timestampable(on="update")
     *
     * @Serializer\Groups({"main", "admin"})
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
     * Set name.
     *
     * @param string $name
     *
     * @return SalesCompany
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isHasEventModule()
    {
        return $this->hasEventModule;
    }

    /**
     * @param boolean $hasEventModule
     */
    public function setHasEventModule($hasEventModule)
    {
        $this->hasEventModule = $hasEventModule;
    }

    /**
     * @return string
     */
    public function getContacter()
    {
        return $this->contacter;
    }

    /**
     * @param string $contacter
     */
    public function setContacter($contacter)
    {
        $this->contacter = $contacter;
    }

    /**
     * @return string
     */
    public function getContacterPhone()
    {
        return $this->contacterPhone;
    }

    /**
     * @param string $contacterPhone
     */
    public function setContacterPhone($contacterPhone)
    {
        $this->contacterPhone = $contacterPhone;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return SalesCompany
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
     * @return string
     */
    public function getContacterEmail()
    {
        return $this->contacterEmail;
    }

    /**
     * @param string $contacterEmail
     */
    public function setContacterEmail($contacterEmail)
    {
        $this->contacterEmail = $contacterEmail;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return SalesCompany
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return SalesCompany
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
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
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return SalesCompany
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
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
     * @return string
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @param string $admin
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    /**
     * @return string
     */
    public function getCoffeeAdmin()
    {
        return $this->coffeeAdmin;
    }

    /**
     * @param string $coffeeAdmin
     */
    public function setCoffeeAdmin($coffeeAdmin)
    {
        $this->coffeeAdmin = $coffeeAdmin;
    }

    /**
     * @return int
     */
    public function getBuildingCounts()
    {
        return $this->buildingCounts;
    }

    /**
     * @param int $buildingCounts
     */
    public function setBuildingCounts($buildingCounts)
    {
        $this->buildingCounts = $buildingCounts;
    }

    /**
     * @return int
     */
    public function getShopCounts()
    {
        return $this->shopCounts;
    }

    /**
     * @param int $shopCounts
     */
    public function setShopCounts($shopCounts)
    {
        $this->shopCounts = $shopCounts;
    }

    /**
     * @return bool
     */
    public function isBanned()
    {
        return $this->banned;
    }

    /**
     * @param bool $banned
     */
    public function setBanned($banned)
    {
        $this->banned = $banned;
    }

    /**
     * @return string
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param string $permissions
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @return bool
     */
    public function isHasPendingBuilding()
    {
        return $this->hasPendingBuilding;
    }

    /**
     * @param bool $hasPendingBuilding
     */
    public function setHasPendingBuilding($hasPendingBuilding)
    {
        $this->hasPendingBuilding = $hasPendingBuilding;
    }

    /**
     * @return bool
     */
    public function isHasPendingShop()
    {
        return $this->hasPendingShop;
    }

    /**
     * @param bool $hasPendingShop
     */
    public function setHasPendingShop($hasPendingShop)
    {
        $this->hasPendingShop = $hasPendingShop;
    }
}
