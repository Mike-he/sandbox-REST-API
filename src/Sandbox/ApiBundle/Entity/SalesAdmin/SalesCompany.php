<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

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
     * @Serializer\Groups({"main", "admin", "dropdown", "client", "auth", "admin_detail"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     *
     * @Serializer\Groups({"main", "admin", "auth", "dropdown", "client", "admin_detail", "client_event"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="applicantName", type="string", length=64)
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $applicantName;

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
     * @ORM\Column(name="email", type="string", length=255)
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $email;

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
     * @Serializer\Groups({"main", "admin"})
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
     * @Serializer\Groups({"main", "admin"})
     */
    private $buildingCounts;

    /**
     * @var int
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $shopCounts;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
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
     * Set applicantName.
     *
     * @param string $applicantName
     *
     * @return SalesCompany
     */
    public function setApplicantName($applicantName)
    {
        $this->applicantName = $applicantName;

        return $this;
    }

    /**
     * Get applicantName.
     *
     * @return string
     */
    public function getApplicantName()
    {
        return $this->applicantName;
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
     * Set email.
     *
     * @param string $email
     *
     * @return SalesCompany
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
     * @return boolean
     */
    public function isBanned()
    {
        return $this->banned;
    }

    /**
     * @param boolean $banned
     */
    public function setBanned($banned)
    {
        $this->banned = $banned;
    }


}
