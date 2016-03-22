<?php

namespace Sandbox\ApiBundle\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sandbox\ApiBundle\Entity\Room\Room;

/**
 * Product.
 *
 * @ORM\Table(
 *  name="Product"
 * )
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

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "client", "admin_room", "admin_detail", "current_order"})
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
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     *
     * @Serializer\Groups({"main", "client", "admin_room", "current_order"})
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="seatNumber", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_room", "admin_detail", "current_order"})
     */
    private $seatNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="visibleUserId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $visibleUserId;

    /**
     * @var string
     *
     * @ORM\Column(name="basePrice", type="decimal")
     *
     * @Serializer\Groups({"main", "client", "admin_room", "admin_detail"})
     */
    private $basePrice;

    /**
     * @var string
     *
     * @ORM\Column(name="unitPrice", type="string", length=255)
     *
     * @Serializer\Groups({"main", "client", "admin_room", "admin_detail"})
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
     * @Serializer\Groups({"main", "admin_room"})
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
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $modificationDate;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\Room
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\Room")
     * @ORM\JoinColumn(name="roomId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"main", "client", "admin_detail", "current_order"})
     */
    private $room;

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
     * Set seatNumber.
     *
     * @param string $seatNumber
     *
     * @return Product
     */
    public function setSeatNumber($seatNumber)
    {
        $this->seatNumber = $seatNumber;

        return $this;
    }

    /**
     * Get seatNumber.
     *
     * @return string
     */
    public function getSeatNumber()
    {
        return $this->seatNumber;
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
     * @param string $basePrice
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
     * @return string
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
        return $this->$recommend;
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
}
