<?php

namespace Sandbox\ApiBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Company.
 *
 * @ORM\Table(name="Company", indexes={
 *     @ORM\Index(name="fk_company_creatorId_idx",columns={"creatorId"})}
 * )
 * @ORM\Entity(
 *      repositoryClass="Sandbox\ApiBundle\Repository\Company\CompanyRepository"
 * )
 */
class Company
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "company_info", "company_basic", "member", "buddy"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     * @Serializer\Groups({"main", "company_info", "company_basic", "member", "buddy"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Serializer\Groups({"main", "company_info", "company_basic"})
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=1024, nullable=true)
     * @Serializer\Groups({"main", "company_info", "company_basic"})
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main", "company_info", "company_basic"})
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main", "company_info", "company_basic"})
     */
    private $fax;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=128, nullable=true)
     * @Serializer\Groups({"main", "company_info", "company_basic"})
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=256, nullable=true)
     * @Serializer\Groups({"main", "company_info", "company_basic"})
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="sinaWeibo", type="string", length=128, nullable=true)
     * @Serializer\Groups({"main", "company_info", "company_basic"})
     */
    private $sinaWeibo;

    /**
     * @var string
     *
     * @ORM\Column(name="tencentWeibo", type="string", length=128, nullable=true)
     * @Serializer\Groups({"main", "company_info", "company_basic"})
     */
    private $tencentWeibo;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook", type="string", length=128, nullable=true)
     * @Serializer\Groups({"main", "company_info", "company_basic"})
     */
    private $facebook;

    /**
     * @var string
     *
     * @ORM\Column(name="linkedin", type="string", length=128, nullable=true)
     * @Serializer\Groups({"main", "company_info", "company_basic"})
     */
    private $linkedin;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomBuilding
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuilding")
     * @ORM\JoinColumn(name="buildingId", referencedColumnName="id")
     *
     * @Serializer\Groups({"main", "company_info", "company_basic"})
     */
    private $building;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $buildingId;

    /**
     * @var \Sandbox\ApiBundle\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="creatorId", referencedColumnName="id")
     * })
     * @Serializer\Groups({"main", "company_info"})
     */
    private $creator;

    /**
     * @var int
     *
     * @ORM\Column(name="creatorId", type="integer", nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $creatorId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main", "company_info"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main", "company_info"})
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
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Company
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * Set description.
     *
     * @param string $description
     *
     * @return Company
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address.
     *
     * @param string $address
     *
     * @return Company
     */
    public function setAddress($address)
    {
        $this->address = $address;

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
     * Set phone.
     *
     * @param string $phone
     *
     * @return Company
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get fax.
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set fax.
     *
     * @param string $fax
     *
     * @return Company
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

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
     * Set email.
     *
     * @param string $email
     *
     * @return Company
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get website.
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set website.
     *
     * @param string $website
     *
     * @return Company
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get sinaWeibo.
     *
     * @return string
     */
    public function getSinaWeibo()
    {
        return $this->sinaWeibo;
    }

    /**
     * Set sinaWeibo.
     *
     * @param string $sinaWeibo
     *
     * @return Company
     */
    public function setSinaWeibo($sinaWeibo)
    {
        $this->sinaWeibo = $sinaWeibo;

        return $this;
    }

    /**
     * Get tencentWeibo.
     *
     * @return string
     */
    public function getTencentWeibo()
    {
        return $this->tencentWeibo;
    }

    /**
     * Set tencentWeibo.
     *
     * @param string $tencentWeibo
     *
     * @return Company
     */
    public function setTencentWeibo($tencentWeibo)
    {
        $this->tencentWeibo = $tencentWeibo;

        return $this;
    }

    /**
     * Get facebook.
     *
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set facebook.
     *
     * @param string $facebook
     *
     * @return Company
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get linkedin.
     *
     * @return string
     */
    public function getLinkedin()
    {
        return $this->linkedin;
    }

    /**
     * Set linkedin.
     *
     * @param string $linkedin
     *
     * @return Company
     */
    public function setLinkedin($linkedin)
    {
        $this->linkedin = $linkedin;

        return $this;
    }

    /**
     * @return Company
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param object $building
     */
    public function setBuilding($building)
    {
        $this->building = $building;
    }

    /**
     * @return object
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @param object $buildingId
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;
    }

    /**
     * @return \Sandbox\ApiBundle\Entity\User\User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param \Sandbox\ApiBundle\Entity\User\User $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * Get creatorId.
     *
     * @return \Sandbox\ApiBundle\Entity\User\User
     */
    public function getCreatorId()
    {
        return $this->creatorId;
    }

    /**
     * Set creatorId.
     *
     * @return Company
     */
    public function setCreatorId($creatorId)
    {
        $this->creatorId = $creatorId;

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
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Company
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

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
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return Company
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }
}
