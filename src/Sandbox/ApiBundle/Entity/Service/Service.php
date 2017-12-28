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
     * @var string
     *
     *  @ORM\Column(name="type", type="string", length=64, nullable=false)
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getSubTitle()
    {
        return $this->subTitle;
    }

    /**
     * @param string $subTitle
     */
    public function setSubTitle($subTitle)
    {
        $this->subTitle = $subTitle;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getCountryId()
    {
        return $this->countryId;
    }

    /**
     * @param int $countryId
     */
    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;
    }

    /**
     * @return \Sandbox\ApiBundle\Entity\Room\RoomCity
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param \Sandbox\ApiBundle\Entity\Room\RoomCity $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * @param int $cityId
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;
    }

    /**
     * @return \Sandbox\ApiBundle\Entity\Room\RoomCity
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param \Sandbox\ApiBundle\Entity\Room\RoomCity $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return int
     */
    public function getProvinceId()
    {
        return $this->provinceId;
    }

    /**
     * @param int $provinceId
     */
    public function setProvinceId($provinceId)
    {
        $this->provinceId = $provinceId;
    }

    /**
     * @return \Sandbox\ApiBundle\Entity\Room\RoomCity
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * @param \Sandbox\ApiBundle\Entity\Room\RoomCity $province
     */
    public function setProvince($province)
    {
        $this->province = $province;
    }

    /**
     * @return int
     */
    public function getDistrictId()
    {
        return $this->districtId;
    }

    /**
     * @param int $districtId
     */
    public function setDistrictId($districtId)
    {
        $this->districtId = $districtId;
    }

    /**
     * @return \Sandbox\ApiBundle\Entity\Room\RoomCity
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param \Sandbox\ApiBundle\Entity\Room\RoomCity $district
     */
    public function setDistrict($district)
    {
        $this->district = $district;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getLimitNumber()
    {
        return $this->limitNumber;
    }

    /**
     * @param int $limitNumber
     */
    public function setLimitNumber($limitNumber)
    {
        $this->limitNumber = $limitNumber;
    }

    /**
     * @return \DateTime
     */
    public function getServiceStartDate()
    {
        return $this->serviceStartDate;
    }

    /**
     * @param \DateTime $serviceStartDate
     */
    public function setServiceStartDate($serviceStartDate)
    {
        $this->serviceStartDate = $serviceStartDate;
    }

    /**
     * @return \DateTime
     */
    public function getServiceEndDate()
    {
        return $this->serviceEndDate;
    }

    /**
     * @param \DateTime $serviceEndDate
     */
    public function setServiceEndDate($serviceEndDate)
    {
        $this->serviceEndDate = $serviceEndDate;
    }

    /**
     * @return string
     */
    public function getPublishCompany()
    {
        return $this->publishCompany;
    }

    /**
     * @param string $publishCompany
     */
    public function setPublishCompany($publishCompany)
    {
        $this->publishCompany = $publishCompany;
    }

    /**
     * @return bool
     */
    public function isCharge()
    {
        return $this->isCharge;
    }

    /**
     * @param bool $isCharge
     */
    public function setIsCharge($isCharge)
    {
        $this->isCharge = $isCharge;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    /**
     * @return bool
     */
    public function isIsSaved()
    {
        return $this->isSaved;
    }

    /**
     * @param bool $isSaved
     */
    public function setIsSaved($isSaved)
    {
        $this->isSaved = $isSaved;
    }

    /**
     * @return int
     */
    public function getSalesCompanyId()
    {
        return $this->salesCompanyId;
    }

    /**
     * @param int $salesCompanyId
     */
    public function setSalesCompanyId($salesCompanyId)
    {
        $this->salesCompanyId = $salesCompanyId;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param \DateTime $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param array $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * @return array
     */
    public function getTimes()
    {
        return $this->times;
    }

    /**
     * @param array $times
     */
    public function setTimes($times)
    {
        $this->times = $times;
    }

    /**
     * @return array
     */
    public function getForms()
    {
        return $this->forms;
    }

    /**
     * @param array $forms
     */
    public function setForms($forms)
    {
        $this->forms = $forms;
    }

    /**
     * @return int
     */
    public function getAcceptedPersonNumber()
    {
        return $this->acceptedPersonNumber;
    }

    /**
     * @param int $acceptedPersonNumber
     */
    public function setAcceptedPersonNumber($acceptedPersonNumber)
    {
        $this->acceptedPersonNumber = $acceptedPersonNumber;
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
