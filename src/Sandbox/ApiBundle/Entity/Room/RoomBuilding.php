<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JsonSerializable;
use Gedmo\Mapping\Annotation as Gedmo;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;

/**
 * RoomBuilding.
 *
 * @ORM\Table(
 *      name="room_building",
 *      indexes={
 *          @ORM\Index(name="fk_Building_cityId_idx", columns={"cityId"})
 *      }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\Room\RoomBuildingRepository"
 * )
 */
class RoomBuilding implements JsonSerializable
{
    const BUILDING_NOT_FOUND_MESSAGE = 'Building Not Found';

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPT = 'accept';
    const STATUS_REFUSE = 'refuse';
    const STATUS_BANNED = 'banned';

    const PLATFORM_SALES_USER_BUILDING = 'sales';
    const PLATFORM_BACKEND_USER_BUILDING = 'backend';

    const LOCATION_TRANSFORM_VERSION_2 = 2; // checking version 2.2.2
    const LOCATION_TRANSFORM_VERSION_3 = 3; // checking version 2.2.3 (android)

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "admin_room",
     *      "client",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "buddy",
     *      "member",
     *      "admin_detail",
     *      "company_info",
     *      "company_basic",
     *      "feed",
     *      "admin_event",
     *      "client_event",
     *      "current_order",
     *      "building_nearby",
     *      "admin_building",
     *      "admin_shop",
     *      "client_order",
     *      "admin",
     *      "shop_nearby",
     *      "client_shop",
     *      "admin_appointment",
     *      "lessor",
     *      "admin_position_bind_view"
     *  }
     * )
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=1024, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_building", "admin_shop", "client_shop"})
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="detail", type="text", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_building", "admin_shop", "client_shop"})
     */
    private $detail;

    /**
     * @var int
     *
     * @ORM\Column(name="cityId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $cityId;

    /**
     * @ORM\ManyToOne(targetEntity="RoomCity")
     * @ORM\JoinColumn(name="cityId", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({
     *     "main",
     *     "building_nearby",
     *     "admin_building",
     *     "admin_shop",
     *     "client_order",
     *     "shop_nearby",
     *     "client_shop"
     * })
     **/
    private $city;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin","admin_building"})
     */
    private $country;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin", "admin_building"})
     */
    private $province;

    /**
     * @var int
     *
     * @ORM\Column(name="districtId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $districtId;

    /**
     * @ORM\ManyToOne(targetEntity="RoomCity")
     * @ORM\JoinColumn(name="districtId", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({
     *     "main",
     *     "admin_building",
     * })
     **/
    private $district;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "admin_room",
     *      "client",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "buddy",
     *      "member",
     *      "admin_detail",
     *      "company_info",
     *      "company_basic",
     *      "feed",
     *      "admin_event",
     *      "client_detail",
     *      "client_event",
     *      "current_order",
     *      "building_nearby",
     *      "admin_building",
     *      "admin_shop",
     *      "client_order",
     *      "shop_nearby",
     *      "client_shop",
     *      "admin_appointment",
     *      "admin_position_bind_view"
     *  }
     * )
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="subtitle", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "admin_room",
     *      "client",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "buddy",
     *      "member",
     *      "admin_detail",
     *      "company_info",
     *      "company_basic",
     *      "feed",
     *      "admin_event",
     *      "client_detail",
     *      "client_event",
     *      "current_order",
     *      "building_nearby",
     *      "admin_building",
     *      "admin_shop",
     *      "client_order",
     *      "shop_nearby",
     *      "client_shop"
     *  }
     * )
     */
    private $subtitle;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_room",
     *      "client",
     *      "admin_detail",
     *      "admin_event",
     *      "client_event",
     *      "current_order",
     *      "building_nearby",
     *      "admin_building",
     *      "admin_shop",
     *      "client_shop",
     *      "shop_nearby",
     *      "client_appointment_detail"
     * })
     */
    private $address;

    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="float", precision=9, scale=6, nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "building_nearby", "admin_building"})
     */
    private $lat;

    /**
     * @var float
     *
     * @ORM\Column(name="lng", type="float", precision=9, scale=6, nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "building_nearby", "admin_building"})
     */
    private $lng;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $floors;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({"main", "avatar", "building_nearby", "admin_building"})
     */
    private $avatar;

    /**
     * @var string
     *
     * @ORM\Column(name="server", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"server", "admin_building"})
     */
    private $server;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modificationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $modificationDate;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "shop_nearby"})
     */
    private $shops;

    /**
     * @var array
     *
     * @Serializer\Groups({"main"})
     */
    private $roomAttachments;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $email;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $phones;

    /**
     * @var string
     *
     * @ORM\Column(name="businessHour", type="string", nullable=true)
     * @Serializer\Groups({"main", "admin_building", "client_shop"})
     */
    private $businessHour;

    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean", nullable=false)
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $visible = true;

    /**
     * @var int
     *
     * @ORM\Column(name="companyId", type="integer", nullable=false)
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $companyId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany")
     * @ORM\JoinColumn(name="companyId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main", "client", "admin_detail", "admin_shop"})
     **/
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=false)
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var bool
     *
     * @ORM\Column(name="isDeleted", type="boolean", nullable=false)
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $isDeleted = false;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $buildingAttachments;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $buildingCompany;

    /**
     * @var int
     */
    private $shopCounts;

    /**
     * @var int
     */
    private $roomCounts;

    /**
     * @var int
     */
    private $productCounts;

    /**
     * @var int
     */
    private $orderCounts;

    /**
     * @var string
     *
     * @ORM\Column(name="orderRemindPhones", type="string", length=2048, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $orderRemindPhones;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $buildingServices;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $buildingTags;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $buildingRoomTypes;

    /**
     * @var float
     *
     * @ORM\Column(name="evaluationStar", type="float", nullable=true)
     * @Serializer\Groups({"main", "client"})
     */
    private $evaluationStar;

    /**
     * @var float
     *
     * @ORM\Column(name="orderStar", type="float", nullable=true)
     * @Serializer\Groups({"main", "client"})
     */
    private $orderStar;

    /**
     * @var float
     *
     * @ORM\Column(name="buildingStar", type="float", nullable=true)
     * @Serializer\Groups({"main", "client"})
     */
    private $buildingStar;

    /**
     * @var float
     *
     * @ORM\Column(name="orderEvaluationNumber", type="float", nullable=true)
     * @Serializer\Groups({"main", "client"})
     */
    private $orderEvaluationNumber;

    /**
     * @var float
     *
     * @ORM\Column(name="buildingEvaluationNumber", type="float", nullable=true)
     * @Serializer\Groups({"main", "client"})
     */
    private $buildingEvaluationNumber;

    /**
     * @var int
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $totalEvaluationNumber;

    /**
     * @var float
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $distance;

    /**
     * it's a number for client side, total rooms with products.
     *
     * @var int
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $roomWithProductNumber;

    /**
     * @var string
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $allSpacesUrl;

    /**
     * @var string
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $wxShareUrl;

    /**
     * @var string
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $allowWxShare = false;

    /**
     * @var string
     *
     * @ORM\Column(name="lessor_name", type="string", length=40)
     *
     * @Serializer\Groups({"main", "admin_building", "lessor"})
     */
    private $lessorName;

    /**
     * @var string
     *
     * @ORM\Column(name="lessor_address", type="string", length=255)
     *
     * @Serializer\Groups({"main", "admin_building", "lessor"})
     */
    private $lessorAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="lessor_contact", type="string", length=20)
     *
     * @Serializer\Groups({"main", "admin_building", "lessor"})
     */
    private $lessorContact;

    /**
     * @var string
     *
     * @ORM\Column(name="lessor_phone", type="string", length=128)
     *
     * @Serializer\Groups({"main", "admin_building", "lessor"})
     */
    private $lessorPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="lessor_email", type="string", length=128)
     *
     * @Serializer\Groups({"main", "admin_building", "lessor"})
     */
    private $lessorEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="lessor_bank_account_name", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_building", "lessor"})
     */
    private $lessorBankAccountName;

    /**
     * @var string
     *
     * @ORM\Column(name="lessor_bank_account_number", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_building", "lessor"})
     */
    private $lessorBankAccountNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="lessor_bank_name", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_building", "lessor"})
     */
    private $lessorBankName;

    /**
     * @var string
     *
     * @ORM\Column(name="lease_remarks", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_building", "lessor"})
     */
    private $leaseRemarks;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", type="string", length=16, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_building", "lessor"})
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="community_manager_name", type="string", length=16, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_building", "lessor"})
     */
    private $communityManagerName;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $customerServices;

    /**
     * @var int
     *
     * @ORM\Column(name="property_type_id", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $propertyTypeId;

    /**
     * @var array
     *
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "admin_room",
     *      "client",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "buddy",
     *      "member",
     *      "admin_detail",
     *      "company_info",
     *      "company_basic",
     *      "feed",
     *      "admin_event",
     *      "client_detail",
     *      "client_event",
     *      "current_order",
     *      "building_nearby",
     *      "admin_building",
     *      "admin_shop",
     *      "client_order",
     *      "shop_nearby",
     *      "client_shop",
     *      "admin_appointment",
     *      "admin_position_bind_view"
     *  }
     * )
     */
    private $propertyType;

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
     * @return string
     */
    public function getLessorBankAccountName()
    {
        return $this->lessorBankAccountName;
    }

    /**
     * @param string $lessorBankAccountName
     */
    public function setLessorBankAccountName($lessorBankAccountName)
    {
        $this->lessorBankAccountName = $lessorBankAccountName;
    }

    /**
     * @return string
     */
    public function getLessorBankAccountNumber()
    {
        return $this->lessorBankAccountNumber;
    }

    /**
     * @param string $lessorBankAccountNumber
     */
    public function setLessorBankAccountNumber($lessorBankAccountNumber)
    {
        $this->lessorBankAccountNumber = $lessorBankAccountNumber;
    }

    /**
     * @return string
     */
    public function getLessorBankName()
    {
        return $this->lessorBankName;
    }

    /**
     * @param string $lessorBankName
     */
    public function setLessorBankName($lessorBankName)
    {
        $this->lessorBankName = $lessorBankName;
    }

    /**
     * @return string
     */
    public function getLeaseRemarks()
    {
        return $this->leaseRemarks;
    }

    /**
     * @param string $leaseRemarks
     */
    public function setLeaseRemarks($leaseRemarks)
    {
        $this->leaseRemarks = $leaseRemarks;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string
     */
    public function getCommunityManagerName()
    {
        return $this->communityManagerName;
    }

    /**
     * @param string $communityManagerName
     */
    public function setCommunityManagerName($communityManagerName)
    {
        $this->communityManagerName = $communityManagerName;
    }

    /**
     * @return int
     */
    public function getPropertyTypeId()
    {
        return $this->propertyTypeId;
    }

    /**
     * @param int $propertyTypeId
     */
    public function setPropertyTypeId($propertyTypeId)
    {
        $this->propertyTypeId = $propertyTypeId;
    }

    /**
     * @return array
     */
    public function getPropertyType()
    {
        return $this->propertyType;
    }

    /**
     * @param array $propertyType
     */
    public function setPropertyType($propertyType)
    {
        $this->propertyType = $propertyType;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return RoomBuilding
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
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param string $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }

    /**
     * Set cityId.
     *
     * @param  $cityId
     *
     * @return RoomBuilding
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * Get cityId.
     *
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * Set shops.
     *
     * @param array $shops
     *
     * @return RoomBuilding
     */
    public function setShops($shops)
    {
        $this->shops = $shops;

        return $this;
    }

    /**
     * Get shops.
     *
     * @return array
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return RoomBuilding
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
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param string $subtitle
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }

    /**
     * Set address.
     *
     * @param string $address
     *
     * @return RoomBuilding
     */
    public function setAddress($address)
    {
        $this->address = $address;

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
     * Set lat.
     *
     * @param float $lat
     *
     * @return RoomBuilding
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat.
     *
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng.
     *
     * @param float $lng
     *
     * @return RoomBuilding
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng.
     *
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set floors.
     *
     * @param array $floors
     *
     * @return RoomBuilding
     */
    public function setFloors($floors)
    {
        $this->floors = $floors;

        return $this;
    }

    /**
     * Get floors.
     *
     * @return array
     */
    public function getFloors()
    {
        return $this->floors;
    }

    /**
     * Set avatar.
     *
     * @param string $avatar
     *
     * @return RoomBuilding
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar.
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param RoomCity $city
     *
     * @return RoomBuilding
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return RoomCity
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return array
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param array $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return array
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * @param array $province
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
     * @return mixed
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param mixed $district
     */
    public function setDistrict($district)
    {
        $this->district = $district;
    }

    /**
     * Set server.
     *
     * @param string $server
     *
     * @return RoomBuilding
     */
    public function setServer($server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * Get server.
     *
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return RoomBuilding
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
     * @return RoomBuilding
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
     * Set room attachments.
     *
     * @param $roomAttachments
     *
     * @return RoomBuilding
     */
    public function setRoomAttachments($roomAttachments)
    {
        $this->roomAttachments = $roomAttachments;

        return $this;
    }

    /**
     * Get room attachments.
     *
     * @return array
     */
    public function getRoomAttachments()
    {
        return $this->roomAttachments;
    }

    /**
     * Set email.
     *
     * @param $email
     *
     * @return RoomBuilding
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
     * Get phones.
     *
     * @return array
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * Set phones.
     *
     * @param array $phones
     *
     * @return RoomBuilding
     */
    public function setPhones($phones)
    {
        $this->phones = $phones;

        return $this;
    }

    /**
     * @return string
     */
    public function getBusinessHour()
    {
        return $this->businessHour;
    }

    /**
     * @param string $businessHour
     */
    public function setBusinessHour($businessHour)
    {
        $this->businessHour = $businessHour;
    }

    /**
     * @return array
     */
    public function getBuildingAttachments()
    {
        return $this->buildingAttachments;
    }

    /**
     * @param array $buildingAttachments
     */
    public function setBuildingAttachments($buildingAttachments)
    {
        $this->buildingAttachments = $buildingAttachments;
    }

    /**
     * @return array
     */
    public function getBuildingCompany()
    {
        return $this->buildingCompany;
    }

    /**
     * @param array $buildingCompany
     */
    public function setBuildingCompany($buildingCompany)
    {
        $this->buildingCompany = $buildingCompany;
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
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * @param int $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * @return SalesCompany
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param SalesCompany $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
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
     * @return bool
     */
    public function isDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @param bool $isDeleted
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;
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
     * @return int
     */
    public function getRoomCounts()
    {
        return $this->roomCounts;
    }

    /**
     * @param int $roomCounts
     */
    public function setRoomCounts($roomCounts)
    {
        $this->roomCounts = $roomCounts;
    }

    /**
     * @return int
     */
    public function getProductCounts()
    {
        return $this->productCounts;
    }

    /**
     * @param int $productCounts
     */
    public function setProductCounts($productCounts)
    {
        $this->productCounts = $productCounts;
    }

    /**
     * @return int
     */
    public function getOrderCounts()
    {
        return $this->orderCounts;
    }

    /**
     * @param int $orderCounts
     */
    public function setOrderCounts($orderCounts)
    {
        $this->orderCounts = $orderCounts;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
        );
    }

    /**
     * Set orderRemindPhones.
     *
     * @param string $orderRemindPhones
     *
     * @return RoomBuilding
     */
    public function setOrderRemindPhones($orderRemindPhones)
    {
        $this->orderRemindPhones = $orderRemindPhones;

        return $this;
    }

    /**
     * Get orderRemindPhones.
     *
     * @return string
     */
    public function getOrderRemindPhones()
    {
        return $this->orderRemindPhones;
    }

    /**
     * @return array
     */
    public function getBuildingServices()
    {
        return $this->buildingServices;
    }

    /**
     * @param array $buildingServices
     */
    public function setBuildingServices($buildingServices)
    {
        $this->buildingServices = $buildingServices;
    }

    /**
     * @return array
     */
    public function getBuildingTags()
    {
        return $this->buildingTags;
    }

    /**
     * @param array $buildingTags
     */
    public function setBuildingTags($buildingTags)
    {
        $this->buildingTags = $buildingTags;
    }

    /**
     * @return array
     */
    public function getBuildingRoomTypes()
    {
        return $this->buildingRoomTypes;
    }

    /**
     * @param array $buildingRoomTypes
     */
    public function setBuildingRoomTypes($buildingRoomTypes)
    {
        $this->buildingRoomTypes = $buildingRoomTypes;
    }

    /**
     * @return float
     */
    public function getEvaluationStar()
    {
        return $this->evaluationStar;
    }

    /**
     * @param float $evaluationStar
     */
    public function setEvaluationStar($evaluationStar)
    {
        $this->evaluationStar = $evaluationStar;
    }

    /**
     * @return float
     */
    public function getOrderStar()
    {
        return $this->orderStar;
    }

    /**
     * @param float $orderStar
     */
    public function setOrderStar($orderStar)
    {
        $this->orderStar = $orderStar;
    }

    /**
     * @return float
     */
    public function getBuildingStar()
    {
        return $this->buildingStar;
    }

    /**
     * @param float $buildingStar
     */
    public function setBuildingStar($buildingStar)
    {
        $this->buildingStar = $buildingStar;
    }

    /**
     * @return float
     */
    public function getOrderEvaluationNumber()
    {
        return $this->orderEvaluationNumber;
    }

    /**
     * @param float $orderEvaluationNumber
     */
    public function setOrderEvaluationNumber($orderEvaluationNumber)
    {
        $this->orderEvaluationNumber = $orderEvaluationNumber;
    }

    /**
     * @return float
     */
    public function getBuildingEvaluationNumber()
    {
        return $this->buildingEvaluationNumber;
    }

    /**
     * @param float $buildingEvaluationNumber
     */
    public function setBuildingEvaluationNumber($buildingEvaluationNumber)
    {
        $this->buildingEvaluationNumber = $buildingEvaluationNumber;
    }

    /**
     * @return int
     */
    public function getTotalEvaluationNumber()
    {
        return $this->totalEvaluationNumber;
    }

    /**
     * @param int $totalEvaluationNumber
     */
    public function setTotalEvaluationNumber($totalEvaluationNumber)
    {
        $this->totalEvaluationNumber = $totalEvaluationNumber;
    }

    /**
     * @return float
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param float $distance
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
    }

    /**
     * @return int
     */
    public function getRoomWithProductNumber()
    {
        return $this->roomWithProductNumber;
    }

    /**
     * @param int $roomWithProductNumber
     */
    public function setRoomWithProductNumber($roomWithProductNumber)
    {
        $this->roomWithProductNumber = $roomWithProductNumber;
    }

    /**
     * @return string
     */
    public function getAllSpacesUrl()
    {
        return $this->allSpacesUrl;
    }

    /**
     * @param string $allSpacesUrl
     */
    public function setAllSpacesUrl($allSpacesUrl)
    {
        $this->allSpacesUrl = $allSpacesUrl;
    }

    /**
     * @return string
     */
    public function getWxShareUrl()
    {
        return $this->wxShareUrl;
    }

    /**
     * @param string $wxShareUrl
     */
    public function setWxShareUrl($wxShareUrl)
    {
        $this->wxShareUrl = $wxShareUrl;
    }

    /**
     * @return string
     */
    public function getAllowWxShare()
    {
        return $this->allowWxShare;
    }

    /**
     * @param string $allowWxShare
     */
    public function setAllowWxShare($allowWxShare)
    {
        $this->allowWxShare = $allowWxShare;
    }

    /**
     * @return string
     */
    public function getLessorName()
    {
        return $this->lessorName;
    }

    /**
     * @param string $lessorName
     */
    public function setLessorName($lessorName)
    {
        $this->lessorName = $lessorName;
    }

    /**
     * @return string
     */
    public function getLessorAddress()
    {
        return $this->lessorAddress;
    }

    /**
     * @param string $lessorAddress
     */
    public function setLessorAddress($lessorAddress)
    {
        $this->lessorAddress = $lessorAddress;
    }

    /**
     * @return string
     */
    public function getLessorContact()
    {
        return $this->lessorContact;
    }

    /**
     * @param string $lessorContact
     */
    public function setLessorContact($lessorContact)
    {
        $this->lessorContact = $lessorContact;
    }

    /**
     * @return string
     */
    public function getLessorPhone()
    {
        return $this->lessorPhone;
    }

    /**
     * @param string $lessorPhone
     */
    public function setLessorPhone($lessorPhone)
    {
        $this->lessorPhone = $lessorPhone;
    }

    /**
     * @return string
     */
    public function getLessorEmail()
    {
        return $this->lessorEmail;
    }

    /**
     * @param string $lessorEmail
     */
    public function setLessorEmail($lessorEmail)
    {
        $this->lessorEmail = $lessorEmail;
    }

    /**
     * @return array
     */
    public function getCustomerServices()
    {
        return $this->customerServices;
    }

    /**
     * @param array $customerServices
     */
    public function setCustomerServices($customerServices)
    {
        $this->customerServices = $customerServices;
    }
}
