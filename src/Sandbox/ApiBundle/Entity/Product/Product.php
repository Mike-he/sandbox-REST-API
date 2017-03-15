<?php

namespace Sandbox\ApiBundle\Entity\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;
use Sandbox\ApiBundle\Entity\Lease\LeaseRentTypes;
use Sandbox\ApiBundle\Entity\Room\Room;

/**
 * Product.
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Product\ProductRepository")
 */
class Product
{
    const OFF_SALE = '0';
    const ON_SALE = '1';
    const READY_SALE = '2';

    const UNIT_HOUR = 'hour';
    const UNIT_DAY = 'day';
    const UNIT_MONTH = 'month';
    const UNIT_DAYS = 'days';
    const UNIT_MIN = 'min';

    const LONG_TERM_ROOM_MISSING_INFO_CODE = 400100;
    const LONG_TERM_ROOM_MISSING_INFO_MESSAGE = 'Information Missing for Long Term Room';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "client", "admin_room", "admin_detail", "current_order", "client_appointment_list"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="roomId", type="integer")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $roomId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\Room
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\Room")
     * @ORM\JoinColumn(name="roomId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"main", "client", "admin_detail", "current_order", "admin_room", "client_appointment_list"})
     */
    private $room;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_room", "current_order"})
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="visibleUserId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $visibleUserId;

    /**
     * @var float
     *
     * @ORM\Column(name="basePrice", type="decimal", precision=10, scale=2, nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_room", "admin_detail", "client_appointment_list"})
     */
    private $basePrice;

    /**
     * @var string
     *
     * @ORM\Column(name="unitPrice", type="string", length=255)
     *
     * @Serializer\Groups({"main", "client", "admin_room", "admin_detail", "client_appointment_list"})
     */
    private $unitPrice;

    /**
     * @var bool
     *
     * @ORM\Column(name="private", type="boolean")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $private = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="renewable", type="boolean")
     *
     * @Serializer\Groups({"main", "client", "admin_room"})
     */
    private $renewable = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean")
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $visible = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_room"})
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_room"})
     */
    private $endDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="recommend", type="boolean")
     *
     * @Serializer\Groups({"main", "admin_room", "admin_detail"})
     */
    private $recommend = false;

    /**
     * @var string
     *
     * @ORM\Column(name="sortTime", type="string", length=15, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room", "admin_detail"})
     */
    private $sortTime;

    /**
     * @var bool
     *
     * @ORM\Column(name="isDeleted", type="boolean", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "admin_detail"})
     */
    private $isDeleted = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="isAnnualRent", type="boolean", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "admin_detail", "client"})
     */
    private $isAnnualRent = false;

    /**
     * @var float
     *
     * @ORM\Column(name="annualRentUnitPrice", type="decimal", precision=10, scale=2, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room", "admin_detail", "client"})
     */
    private $annualRentUnitPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="annualRentUnit", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room", "admin_detail", "client"})
     */
    private $annualRentUnit;

    /**
     * @var string
     *
     * @ORM\Column(name="annualRentDescription", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room", "admin_detail", "client"})
     */
    private $annualRentDescription;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modificationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $modificationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="earliestRentDate", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail"})
     */
    private $earliestRentDate;

    /**
     * @var float
     *
     * @ORM\Column(name="deposit", type="decimal", precision=10, scale=2, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "client_appointment_list"})
     */
    private $deposit;

    /**
     * @var string
     *
     * @ORM\Column(name="rentalInfo", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "client_appointment_detail"})
     */
    private $rentalInfo;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "client_appointment_detail"})
     */
    private $filename;

    /**
     * @var int
     */
    private $pendingAppointmentCounts;

    /**
     * @var int
     */
    private $totalAppointmentCounts;

    /**
     * @var float
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $distance;

    /**
     * @var array
     */
    private $seats;

    /**
     * @var bool
     *
     * @ORM\Column(name="appointment", type="boolean", options={"default":true})
     *
     * @Serializer\Groups({"main", "admin_room", "admin_detail", "client"})
     */
    private $appointment = true;

    /**
     * @var string
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $collectionMethod;

    /**
     * @var LeaseRentTypes
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\ApiBundle\Entity\Lease\LeaseRentTypes")
     * @ORM\JoinTable(
     *      name="product_has_rent_types",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="lease_rent_types_id", referencedColumnName="id")}
     * )
     *
     * @Serializer\Groups({"main", "admin_room", "admin_appointment", "client"})
     */
    private $leaseRentTypes;

    public function __construct()
    {
        $date = new \DateTime('2099-12-30 23:59:59');
        $this->setEndDate($date);
        $this->leaserentTypes = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isAppointment()
    {
        return $this->appointment;
    }

    /**
     * @param bool $appointment
     */
    public function setAppointment($appointment)
    {
        $this->appointment = $appointment;
    }

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
     * @return \DateTime
     */
    public function getEarliestRentDate()
    {
        return $this->earliestRentDate;
    }

    /**
     * @param \DateTime $earliestRentDate
     */
    public function setEarliestRentDate($earliestRentDate)
    {
        $this->earliestRentDate = $earliestRentDate;
    }

    /**
     * @return float
     */
    public function getDeposit()
    {
        return $this->deposit;
    }

    /**
     * @param float $deposit
     */
    public function setDeposit($deposit)
    {
        $this->deposit = $deposit;
    }

    /**
     * @return string
     */
    public function getRentalInfo()
    {
        return $this->rentalInfo;
    }

    /**
     * @param string $rentalInfo
     */
    public function setRentalInfo($rentalInfo)
    {
        $this->rentalInfo = $rentalInfo;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Set distance.
     *
     * @param float $distance
     *
     * @return Product
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get distance.
     *
     * @return float
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set roomId.
     *
     * @param int $roomId
     *
     * @return Product
     */
    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Get roomId.
     *
     * @return int
     */
    public function getRoomId()
    {
        return $this->roomId;
    }

    /**
     * Get room.
     *
     * @return Room
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param Room $room
     *
     * @return Product
     */
    public function setRoom($room)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Product
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
     * Set seats.
     *
     * @param array $seats
     *
     * @return Product
     */
    public function setSeats($seats)
    {
        $this->seats = $seats;

        return $this;
    }

    /**
     * Get seats.
     *
     * @return array
     */
    public function getSeats()
    {
        return $this->seats;
    }

    /**
     * Set visibleUserId.
     *
     * @param int $visibleUserId
     *
     * @return Product
     */
    public function setVisibleUserId($visibleUserId)
    {
        $this->visibleUserId = $visibleUserId;

        return $this;
    }

    /**
     * Get visibleUserId.
     *
     * @return int
     */
    public function getVisibleUserId()
    {
        return $this->visibleUserId;
    }

    /**
     * Set basePrice.
     *
     * @param float $basePrice
     *
     * @return Product
     */
    public function setBasePrice($basePrice)
    {
        $this->basePrice = $basePrice;

        return $this;
    }

    /**
     * Get basePrice.
     *
     * @return float
     */
    public function getBasePrice()
    {
        return $this->basePrice;
    }

    /**
     * Set unitPrice.
     *
     * @param string $unitPrice
     *
     * @return Product
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    /**
     * Get unitPrice.
     *
     * @return string
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * Set private.
     *
     * @param bool $private
     *
     * @return Product
     */
    public function setPrivate($private)
    {
        $this->private = $private;

        return $this;
    }

    /**
     * Get private.
     *
     * @return bool
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * Set recommend.
     *
     * @param bool $recommend
     *
     * @return Product
     */
    public function setRecommend($recommend)
    {
        $this->recommend = $recommend;

        return $this;
    }

    /**
     * Get recommend.
     *
     * @return bool
     */
    public function isRecommend()
    {
        return $this->recommend;
    }

    /**
     * Set sortTime.
     *
     * @param string $sortTime
     *
     * @return Product
     */
    public function setSortTime($sortTime)
    {
        $this->sortTime = $sortTime;

        return $this;
    }

    /**
     * Get sortTime.
     *
     * @return string
     */
    public function getSortTime()
    {
        return $this->sortTime;
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
     * @return bool
     */
    public function isAnnualRent()
    {
        return $this->isAnnualRent;
    }

    /**
     * @param bool $isAnnualRent
     */
    public function setIsAnnualRent($isAnnualRent)
    {
        $this->isAnnualRent = $isAnnualRent;
    }

    /**
     * @return float
     */
    public function getAnnualRentUnitPrice()
    {
        return $this->annualRentUnitPrice;
    }

    /**
     * @param float $annualRentUnitPrice
     */
    public function setAnnualRentUnitPrice($annualRentUnitPrice)
    {
        $this->annualRentUnitPrice = $annualRentUnitPrice;
    }

    /**
     * @return string
     */
    public function getAnnualRentUnit()
    {
        return $this->annualRentUnit;
    }

    /**
     * @param string $annualRentUnit
     */
    public function setAnnualRentUnit($annualRentUnit)
    {
        $this->annualRentUnit = $annualRentUnit;
    }

    /**
     * @return string
     */
    public function getAnnualRentDescription()
    {
        return $this->annualRentDescription;
    }

    /**
     * @param string $annualRentDescription
     */
    public function setAnnualRentDescription($annualRentDescription)
    {
        $this->annualRentDescription = $annualRentDescription;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Product
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
     * @return Product
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
     * Set startDate.
     *
     * @param \DateTime $startDate
     *
     * @return Product
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate.
     *
     * @param \DateTime $endDate
     *
     * @return Product
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate.
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set renewable.
     *
     * @param bool $renewable
     *
     * @return Product
     */
    public function setRenewable($renewable)
    {
        $this->renewable = $renewable;

        return $this;
    }

    /**
     * Get renewable.
     *
     * @return bool
     */
    public function getRenewable()
    {
        return $this->renewable;
    }

    /**
     * Set visible.
     *
     * @param bool $visible
     *
     * @return Product
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible.
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @return int
     */
    public function getPendingAppointmentCounts()
    {
        return $this->pendingAppointmentCounts;
    }

    /**
     * @param int $pendingAppointmentCounts
     */
    public function setPendingAppointmentCounts($pendingAppointmentCounts)
    {
        $this->pendingAppointmentCounts = $pendingAppointmentCounts;
    }

    /**
     * @return int
     */
    public function getTotalAppointmentCounts()
    {
        return $this->totalAppointmentCounts;
    }

    /**
     * @param int $totalAppointmentCounts
     */
    public function setTotalAppointmentCounts($totalAppointmentCounts)
    {
        $this->totalAppointmentCounts = $totalAppointmentCounts;
    }

    /**
     * @return string
     */
    public function getCollectionMethod()
    {
        return $this->collectionMethod;
    }

    /**
     * @param string $collectionMethod
     */
    public function setCollectionMethod($collectionMethod)
    {
        $this->collectionMethod = $collectionMethod;
    }

    /**
     * @return LeaseRentTypes
     */
    public function getLeaseRentTypes()
    {
        return $this->leaseRentTypes;
    }

    /**
     * @param LeaseRentTypes $leaseRentType
     */
    public function addLeaseRentTypes($leaseRentType)
    {
        $this->leaseRentTypes[] = $leaseRentType;
    }

    /**
     * @param LeaseRentTypes $leaseRentType
     */
    public function removeLeaseRentTypes($leaseRentType)
    {
        return $this->leaseRentTypes->removeElement($leaseRentType);
    }
}
