<?php

namespace Sandbox\ApiBundle\Entity\Service;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Service.
 *
 * @ORM\Table(name = "services")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Service\ServiceRepository")
 */
class Service
{
    const STATUS_PREHEATING = 'preheating';
    const STATUS_WAITING = 'waiting';
    const STATUS_ONGOING = 'ongoing';
    const STATUS_END = 'end';
    const STATUS_SAVED = 'saved';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_title", type="string", length=255, nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $subTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="country_id", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $countryId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomCity
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomCity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $country;

    /**
     * @var int
     *
     * @ORM\Column(name="city_id", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $cityId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomCity
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomCity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="city_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $city;

    /**
     * @var int
     *
     * @ORM\Column(name="province_id", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $provinceId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomCity
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomCity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="province_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $province;

    /**
     * @var int
     *
     * @ORM\Column(name="district_id", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $districtId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomCity
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomCity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="district_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $district;

    /**
     * @var int
     *
     * @ORM\Column(name="type_id", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $typeId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Service\ServiceType
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Service\ServiceType")
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="limit_number", type="integer", nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $limitNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="service_start_date", type="datetime", nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $serviceStartDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="service_end_date", type="datetime", nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $serviceEndDate;

    /**
     * @var string
     *
     * @ORM\Column(name="publishCompany", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $publishCompany;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_charge", type="boolean", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $isCharge = true;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=true)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $price;

    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service"
     * })
     */
    private $visible = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_saved", type="boolean", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service"
     * })
     */
    private $isSaved = false;

    /**
     * @var int
     *
     * @ORM\Column(name="sales_company_id", type="integer", nullable=false)
     */
    private $salesCompanyId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modification_date", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $modificationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64, nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $status;

    /**
     * @var array
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $attachments;

    /**
     * @var array
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $times;

    /**
     * @var array
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $forms;

    /**
     * @var int
     *
     * @Serializer\Groups({
     *      "main",
     *      "client_service"
     * })
     */
    private $acceptedPersonNumber;

    /**
     * @var int
     *
     * @Serializer\Groups({
     *      "main",
     *      "client_service"
     * })
     */
    private $myLikeId;

    /**
     * @var array
     *
     * @Serializer\Groups({
     *      "main",
     *      "client_service"
     * })
     */
    private $salesCompany;

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
     * Set name
     *
     * @param string $name
     * @return Service
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set subTitle
     *
     * @param string $subTitle
     * @return Service
     */
    public function setSubTitle($subTitle)
    {
        $this->subTitle = $subTitle;

        return $this;
    }

    /**
     * Get subTitle
     *
     * @return string
     */
    public function getSubTitle()
    {
        return $this->subTitle;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Service
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set countryId
     *
     * @param integer $countryId
     * @return Service
     */
    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;

        return $this;
    }

    /**
     * Get countryId
     *
     * @return integer
     */
    public function getCountryId()
    {
        return $this->countryId;
    }

    /**
     * Set cityId
     *
     * @param integer $cityId
     * @return Service
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * Get cityId
     *
     * @return integer 
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * Set provinceId
     *
     * @param integer $provinceId
     * @return Service
     */
    public function setProvinceId($provinceId)
    {
        $this->provinceId = $provinceId;

        return $this;
    }

    /**
     * Get provinceId
     *
     * @return integer 
     */
    public function getProvinceId()
    {
        return $this->provinceId;
    }

    /**
     * Set districtId
     *
     * @param integer $districtId
     * @return Service
     */
    public function setDistrictId($districtId)
    {
        $this->districtId = $districtId;

        return $this;
    }

    /**
     * Get districtId
     *
     * @return integer 
     */
    public function getDistrictId()
    {
        return $this->districtId;
    }

    /**
     * Set limitNumber
     *
     * @param integer $limitNumber
     * @return Service
     */
    public function setLimitNumber($limitNumber)
    {
        $this->limitNumber = $limitNumber;

        return $this;
    }

    /**
     * Get limitNumber
     *
     * @return integer 
     */
    public function getLimitNumber()
    {
        return $this->limitNumber;
    }

    /**
     * Set serviceStartDate
     *
     * @param \DateTime $serviceStartDate
     * @return Service
     */
    public function setServiceStartDate($serviceStartDate)
    {
        $this->serviceStartDate = $serviceStartDate;

        return $this;
    }

    /**
     * Get serviceStartDate
     *
     * @return \DateTime 
     */
    public function getServiceStartDate()
    {
        return $this->serviceStartDate;
    }

    /**
     * Set serviceEndDate
     *
     * @param \DateTime $serviceEndDate
     * @return Service
     */
    public function setServiceEndDate($serviceEndDate)
    {
        $this->serviceEndDate = $serviceEndDate;

        return $this;
    }

    /**
     * Get serviceEndDate
     *
     * @return \DateTime 
     */
    public function getServiceEndDate()
    {
        return $this->serviceEndDate;
    }

    /**
     * Set publishCompany
     *
     * @param string $publishCompany
     * @return Service
     */
    public function setPublishCompany($publishCompany)
    {
        $this->publishCompany = $publishCompany;

        return $this;
    }

    /**
     * Get publishCompany
     *
     * @return string 
     */
    public function getPublishCompany()
    {
        return $this->publishCompany;
    }

    /**
     * Set isCharge
     *
     * @param boolean $isCharge
     * @return Service
     */
    public function setIsCharge($isCharge)
    {
        $this->isCharge = $isCharge;

        return $this;
    }

    /**
     * Get isCharge
     *
     * @return boolean 
     */
    public function isCharge()
    {
        return $this->isCharge;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return Service
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set visible
     *
     * @param boolean $visible
     * @return Service
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean 
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set isSaved
     *
     * @param boolean $isSaved
     * @return Service
     */
    public function setIsSaved($isSaved)
    {
        $this->isSaved = $isSaved;

        return $this;
    }

    /**
     * Get isSaved
     *
     * @return boolean 
     */
    public function getIsSaved()
    {
        return $this->isSaved;
    }

    /**
     * Set salesCompanyId
     *
     * @param integer $salesCompanyId
     * @return Service
     */
    public function setSalesCompanyId($salesCompanyId)
    {
        $this->salesCompanyId = $salesCompanyId;

        return $this;
    }

    /**
     * Get salesCompanyId
     *
     * @return integer 
     */
    public function getSalesCompanyId()
    {
        return $this->salesCompanyId;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Service
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
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
     * Set modificationDate
     *
     * @param \DateTime $modificationDate
     * @return Service
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
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
     * Set status
     *
     * @param string $status
     * @return Service
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set country
     *
     * @param \Sandbox\ApiBundle\Entity\Room\RoomCity $country
     * @return Service
     */
    public function setCountry(\Sandbox\ApiBundle\Entity\Room\RoomCity $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \Sandbox\ApiBundle\Entity\Room\RoomCity
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set city
     *
     * @param \Sandbox\ApiBundle\Entity\Room\RoomCity $city
     * @return Service
     */
    public function setCity(\Sandbox\ApiBundle\Entity\Room\RoomCity $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \Sandbox\ApiBundle\Entity\Room\RoomCity 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set province
     *
     * @param \Sandbox\ApiBundle\Entity\Room\RoomCity $province
     * @return Service
     */
    public function setProvince(\Sandbox\ApiBundle\Entity\Room\RoomCity $province = null)
    {
        $this->province = $province;

        return $this;
    }

    /**
     * Get province
     *
     * @return \Sandbox\ApiBundle\Entity\Room\RoomCity 
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * Set district
     *
     * @param \Sandbox\ApiBundle\Entity\Room\RoomCity $district
     * @return Service
     */
    public function setDistrict(\Sandbox\ApiBundle\Entity\Room\RoomCity $district = null)
    {
        $this->district = $district;

        return $this;
    }

    /**
     * Get district
     *
     * @return \Sandbox\ApiBundle\Entity\Room\RoomCity 
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Set typeId
     *
     * @param integer $typeId
     * @return Service
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId
     *
     * @return integer
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set type
     *
     * @param \Sandbox\ApiBundle\Entity\Service\ServiceType $type
     * @return Service
     */
    public function setType(\Sandbox\ApiBundle\Entity\Service\ServiceType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Sandbox\ApiBundle\Entity\Service\ServiceType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param ServiceAttachment $attachments
     *
     * @return Service
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * @return ServiceAttachment
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set times.
     *
     * @param ServiceTime $times
     *
     * @return Service
     */
    public function setTimes($times)
    {
        $this->times= $times;

        return $this;
    }

    /**
     * Get times.
     *
     * @return ServiceTime
     */
    public function getTimes()
    {
        return $this->times;
    }

    /**
     * @param ServiceForm $forms
     *
     * @return Service
     */
    public function setForms($forms)
    {
        $this->forms = $forms;

        return $this;
    }

    /**
     * @return ServiceForm
     */
    public function getForms()
    {
        return $this->forms;
    }

    /**
     * Set accepted person number.
     *
     * @param int $acceptedPersonNumber
     *
     * @return Service
     */
    public function setAcceptedPersonNumber($acceptedPersonNumber)
    {
        $this->acceptedPersonNumber = $acceptedPersonNumber;

        return $this;
    }

    /**
     * Get accepted person number.
     *
     * @return int
     */
    public function getAcceptedPersonNumber()
    {
        return $this->acceptedPersonNumber;
    }

    /**
     * @return int
     */
    public function getMyLikeId()
    {
        return $this->myLikeId;
    }

    /**
     * @param int $myLikeId
     */
    public function setMyLikeId($myLikeId)
    {
        $this->myLikeId = $myLikeId;
    }

    /**
     * @return array
     */
    public function getSalesCompany()
    {
        return $this->salesCompany;
    }

    /**
     * @param array $salesCompany
     */
    public function setSalesCompany($salesCompany)
    {
        $this->salesCompany = $salesCompany;
    }
}
