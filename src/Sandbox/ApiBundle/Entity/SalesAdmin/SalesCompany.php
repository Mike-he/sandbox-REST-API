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
     * @Serializer\Groups({
     *     "main",
     *     "admin_list",
     *     "admin",
     *     "dropdown",
     *     "client",
     *     "auth",
     *     "admin_detail",
     *     "admin_shop",
     *     "admin_view",
     *     "official_list"
     * })
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     *
     * @Serializer\Groups({
     *     "main",
     *     "admin_list",
     *     "admin",
     *     "auth",
     *     "dropdown",
     *     "client",
     *     "admin_detail",
     *     "client_event",
     *     "admin_shop",
     *     "admin_view",
     *     "official_list"
     * })
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="contacter", type="string", length=64)
     *
     * @Serializer\Groups({"main", "admin", "admin_view"})
     */
    private $contacter;

    /**
     * @var string
     *
     * @ORM\Column(name="contacter_phone", type="string", length=64)
     *
     * @Serializer\Groups({"main", "admin", "admin_view"})
     */
    private $contacterPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main", "admin", "admin_view"})
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="contacter_email", type="string", length=255)
     *
     * @Serializer\Groups({"main", "admin", "admin_view"})
     */
    private $contacterEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255)
     *
     * @Serializer\Groups({"main", "admin", "admin_view"})
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "admin", "admin_view"})
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="banned", type="boolean", nullable=false)
     * @Serializer\Groups({"main", "admin", "admin_list", "admin_view"})
     */
    private $banned = false;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin", "admin_view"})
     */
    private $admins;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin", "admin_view"})
     */
    private $coffeeAdmins;

    /**
     * @var int
     *
     * @Serializer\Groups({"main", "admin", "admin_list", "admin_view"})
     */
    private $buildingCounts;

    /**
     * @var int
     *
     * @Serializer\Groups({"main", "admin", "admin_list", "admin_view"})
     */
    private $shopCounts;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $permissions;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin", "admin_view", "admin_list"})
     */
    private $excludePermissions;

    /**
     * @var bool
     *
     * @Serializer\Groups({"main", "admin", "admin_list", "admin_view"})
     */
    private $hasPendingBuilding = false;

    /**
     * @var bool
     *
     * @Serializer\Groups({"main", "admin", "admin_list", "admin_view"})
     */
    private $hasPendingShop = false;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin_view", "admin"})
     */
    private $services;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="financial_contacter", type="string", length=255, nullable=true)
     */
    private $financialContacter;

    /**
     * @var string
     *
     * @ORM\Column(name="financial_contacter_phone", type="string", length=255, nullable=true)
     */
    private $financialContacterPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="financial_contacter_email", type="string", length=255, nullable=true)
     */
    private $financialContacterEmail;

    /**
     * @var bool
     *
     * @ORM\Column(name="online_sales", type="boolean", nullable=false)
     */
    private $onlineSales;

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
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param array $services
     */
    public function setServices($services)
    {
        $this->services = $services;
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
    public function getAdmins()
    {
        return $this->admins;
    }

    /**
     * @param string $admins
     */
    public function setAdmins($admins)
    {
        $this->admins = $admins;
    }

    /**
     * @return string
     */
    public function getCoffeeAdmins()
    {
        return $this->coffeeAdmins;
    }

    /**
     * @param string $coffeeAdmins
     */
    public function setCoffeeAdmins($coffeeAdmins)
    {
        $this->coffeeAdmins = $coffeeAdmins;
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

    /**
     * @return array
     */
    public function getExcludePermissions()
    {
        return $this->excludePermissions;
    }

    /**
     * @param array $excludePermissions
     */
    public function setExcludePermissions($excludePermissions)
    {
        $this->excludePermissions = $excludePermissions;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param string $website
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * @return string
     */
    public function getFinancialContacter()
    {
        return $this->financialContacter;
    }

    /**
     * @param string $financialContacter
     */
    public function setFinancialContacter($financialContacter)
    {
        $this->financialContacter = $financialContacter;
    }

    /**
     * @return string
     */
    public function getFinancialContacterPhone()
    {
        return $this->financialContacterPhone;
    }

    /**
     * @param string $financialContacterPhone
     */
    public function setFinancialContacterPhone($financialContacterPhone)
    {
        $this->financialContacterPhone = $financialContacterPhone;
    }

    /**
     * @return string
     */
    public function getFinancialContacterEmail()
    {
        return $this->financialContacterEmail;
    }

    /**
     * @param string $financialContacterEmail
     */
    public function setFinancialContacterEmail($financialContacterEmail)
    {
        $this->financialContacterEmail = $financialContacterEmail;
    }

    /**
     * @return bool
     */
    public function isOnlineSales()
    {
        return $this->onlineSales;
    }

    /**
     * @param bool $onlineSales
     */
    public function setOnlineSales($onlineSales)
    {
        $this->onlineSales = $onlineSales;
    }
}
