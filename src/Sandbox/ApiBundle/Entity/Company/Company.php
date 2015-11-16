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
     * @Serializer\Groups({
     *      "main",
     *      "company_info",
     *      "company_basic",
     *      "member",
     *      "buddy",
     *      "company_limit",
     *      "profile",
     *      "profile_stranger",
     *      "profile_basic",
     *      "profile_basic_stranger",
     *      "company_invitation"
     * })
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "company_info",
     *      "company_basic",
     *      "member",
     *      "buddy",
     *      "company_limit",
     *      "profile",
     *      "profile_stranger",
     *      "profile_basic",
     *      "profile_basic_stranger",
     *      "company_invitation"
     * })
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Serializer\Groups({
     *      "main",
     *      "company_info",
     *      "company_basic",
     *      "company_limit"
     * })
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=1024, nullable=true)
     * @Serializer\Groups({
     *      "main",
     *      "company_info",
     *      "company_basic",
     *      "company_limit"
     * })
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
     *
     * @Serializer\Groups({"main"})
     */
    private $creator;

    /**
     * @var int
     *
     * @ORM\Column(name="creatorId", type="integer", nullable=false)
     * @Serializer\Groups({"main", "company_info","company_limit"})
     */
    private $creatorId;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "company_info"})
     */
    private $creatorProfile;

    /**
     * @var bool
     *
     * @ORM\Column(name="banned", type="boolean", nullable=false)
     * @Serializer\Groups({"main", "company_limit"})
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
     * @var array
     *
     * @Serializer\Groups({"main", "company_info"})
     */
    private $industries;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "company_info"})
     */
    private $portfolios;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "company_info"})
     */
    private $members;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "company_info", "company_limit"})
     */
    private $companyVerifyRecord;

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
     * @return int
     */
    public function getCreatorId()
    {
        return $this->creatorId;
    }

    /**
     * @param int $creatorId
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

    /**
     * @return array
     */
    public function getIndustries()
    {
        return $this->industries;
    }

    /**
     * @param array $industries
     */
    public function setIndustries($industries)
    {
        $this->industries = $industries;
    }

    /**
     * @return array
     */
    public function getPortfolios()
    {
        return $this->portfolios;
    }

    /**
     * @param array $portfolios
     */
    public function setPortfolios($portfolios)
    {
        $this->portfolios = $portfolios;
    }

    /**
     * @return array
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param array $members
     */
    public function setMembers($members)
    {
        $this->members = $members;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }

    /**
     * Set creator profile.
     *
     * @param $creatorProfile
     *
     * @return Company
     */
    public function setCreatorProfile($creatorProfile)
    {
        $this->creatorProfile = $creatorProfile;

        return $this;
    }

    /**
     * Get creator profile.
     *
     * @return array
     */
    public function getCreatorProfile()
    {
        return $this->creatorProfile;
    }

    /**
     * Set banned.
     *
     * @param bool $banned
     *
     * @return Company
     */
    public function setBanned($banned)
    {
        $this->banned = $banned;

        return $this;
    }

    /**
     * Get banned.
     *
     * @return bool
     */
    public function getBanned()
    {
        return $this->banned;
    }

    /**
     * Set company verify record.
     *
     * @param $companyVerifyRecord
     *
     * @return Company
     */
    public function setCompanyVerifyRecord($companyVerifyRecord)
    {
        $this->companyVerifyRecord = $companyVerifyRecord;

        return $this;
    }

    /**
     * Get company verify record.
     *
     * @return array
     */
    public function getCompanyVerifyRecord()
    {
        return $this->companyVerifyRecord;
    }
}
