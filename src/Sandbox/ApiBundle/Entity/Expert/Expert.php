<?php

namespace Sandbox\ApiBundle\Entity\Expert;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Expert.
 *
 * @ORM\Table(name = "expert")
 * @ORM\Entity()
 */
class Expert
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=16)
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(name="credential_no", type="string", length=64)
     */
    private $credentialNo;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=128, nullable=true)
     */
    private $email;

    /**
     * @var int
     *
     * @ORM\Column(name="country_id", type="integer", nullable=true)
     */
    private $countryId;

    /**
     * @var int
     *
     * @ORM\Column(name="city_id", type="integer", nullable=true)
     */
    private $cityId;

    /**
     * @var int
     *
     * @ORM\Column(name="province_id", type="integer", nullable=true)
     */
    private $provinceId;

    /**
     * @var int
     *
     * @ORM\Column(name="district_id", type="integer", nullable=true)
     */
    private $districtId;

    /**
     * @var float
     *
     * @ORM\Column(name="base_price", type="float", nullable=true)
     */
    private $basePrice;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_service", type="boolean")
     */
    private $isService = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="banned", type="boolean")
     */
    private $banned = false;

    /**
     * @var string
     *
     * @ORM\Column(name="identity", type="string", length=64, nullable=true)
     */
    private $identity;

    /**
     * @var string
     *
     * @ORM\Column(name="introduction", type="string", length=256, nullable=true)
     */
    private $introduction;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="text",nullable=true)
     */
    private $photo;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime", nullable=true)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modification_date", type="datetime", nullable=true)
     */
    private $modificationDate;

    /**
     * @var expertFields
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\ApiBundle\Entity\Expert\ExpertField")
     * @ORM\JoinTable(
     *      name="expert_has_expert_field",
     *      joinColumns={@ORM\JoinColumn(name="expert_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="expert_field_id", referencedColumnName="id")}
     * )
     */
    private $expertFields;

    public function __construct()
    {
        $this->expertFields = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
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
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getCredentialNo()
    {
        return $this->credentialNo;
    }

    /**
     * @param string $credentialNo
     */
    public function setCredentialNo($credentialNo)
    {
        $this->credentialNo = $credentialNo;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
     * @return string
     */
    public function getBasePrice()
    {
        return $this->basePrice;
    }

    /**
     * @param string $basePrice
     */
    public function setBasePrice($basePrice)
    {
        $this->basePrice = $basePrice;
    }

    /**
     * @return bool
     */
    public function isService()
    {
        return $this->isService;
    }

    /**
     * @param bool $isService
     */
    public function setIsService($isService)
    {
        $this->isService = $isService;
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
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @param string $identity
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
    }

    /**
     * @return string
     */
    public function getIntroduction()
    {
        return $this->introduction;
    }

    /**
     * @param string $introduction
     */
    public function setIntroduction($introduction)
    {
        $this->introduction = $introduction;
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
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param string $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
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
     * @return expertFields
     */
    public function getExpertFields()
    {
        return $this->expertFields;
    }

    /**
     * @param expertFields $expertField
     */
    public function addExpertFields($expertField)
    {
        $this->expertFields[] = $expertField;
    }

    /**
     * @param expertFields $expertField
     */
    public function removeExpertFields($expertField)
    {
        return $this->expertFields->removeElement($expertField);
    }
}
