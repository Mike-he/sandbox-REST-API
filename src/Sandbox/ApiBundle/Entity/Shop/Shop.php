<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JsonSerializable;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;

/**
 * Shop.
 *
 * @ORM\Table(
 *     name="Shop",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="buildingId_name_UNIQUE", columns={"buildingId", "name"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Shop\ShopRepository")
 */
class Shop implements JsonSerializable
{
    const PATH_ONLINE = '/online';
    const PATH_CLOSE = '/close';
    const PATH_ACTIVE = '/active';
    const SHOP_INACTIVE_CODE = 400001;
    const SHOP_INACTIVE_MESSAGE = 'This Shop is Inactive';
    const SHOP_CONFLICT_MESSAGE = 'Shop with this name already exist in this building';
    const CLOSED_CODE = 400002;
    const CLOSED_MESSAGE = 'This Shop Is Closed Currently';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "drop_down", "admin_shop", "client_order", "admin"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $buildingId;

    /**
     * @var RoomBuilding
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuilding")
     * @ORM\JoinColumn(name="buildingId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main", "admin_shop", "client_order", "admin"})
     */
    private $building;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main", "drop_down", "admin_shop", "client_order", "admin"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startHour", type="time", nullable=true)
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $startHour;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endHour", type="time", nullable=true)
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $endHour;

    /**
     * @var bool
     *
     * @ORM\Column(name="close", type="boolean", options={"default": false})
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $close = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="online", type="boolean", options={"default": false})
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $online = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", options={"default": false})
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $active = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $modificationDate;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopAttachment",
     *      mappedBy="shop",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="shopId")
     * @ORM\OrderBy({"id" = "ASC"})
     *
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $shopAttachments;

    /**
     * @var array
     */
    private $attachments;

    /**
     * @var string
     */
    private $start;

    /**
     * @var string
     */
    private $end;

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
     * @return Shop
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
     * Set description.
     *
     * @param string $description
     *
     * @return Shop
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
     * Set startHour.
     *
     * @param \DateTime $startHour
     *
     * @return Shop
     */
    public function setStartHour($startHour)
    {
        $this->startHour = $startHour;

        return $this;
    }

    /**
     * Get startHour.
     *
     * @return \DateTime
     */
    public function getStartHour()
    {
        return $this->startHour;
    }

    /**
     * Set endHour.
     *
     * @param \DateTime $endHour
     *
     * @return Shop
     */
    public function setEndHour($endHour)
    {
        $this->endHour = $endHour;

        return $this;
    }

    /**
     * Get endHour.
     *
     * @return \DateTime
     */
    public function getEndHour()
    {
        return $this->endHour;
    }

    /**
     * Set online status.
     *
     * @param bool $status
     *
     * @return Shop
     */
    public function setOnline($status)
    {
        $this->online = $status;

        return $this;
    }

    /**
     * Get online status.
     *
     * @return bool
     */
    public function isOnline()
    {
        return $this->online;
    }

    /**
     * Set active status.
     *
     * @param bool $active
     *
     * @return Shop
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active status.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Set close status.
     *
     * @param bool $close
     *
     * @return Shop
     */
    public function setClose($close)
    {
        $this->close = $close;

        return $this;
    }

    /**
     * Get close status.
     *
     * @return bool
     */
    public function isClose()
    {
        return $this->close;
    }

    /**
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @param int $buildingId
     *
     * @return Shop
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;

        return $this;
    }

    /**
     * Set room building.
     *
     * @param RoomBuilding $building
     *
     * @return Shop
     */
    public function setBuilding($building)
    {
        $this->building = $building;

        return $this;
    }

    /**
     * Get room building.
     *
     * @return RoomBuilding
     */
    public function getBuilding()
    {
        return $this->building;
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
     *
     * @return Shop
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return Shop
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
     * Set shop attachments.
     *
     * @param $shopAttachments
     *
     * @return Shop
     */
    public function setShopAttachments($shopAttachments)
    {
        $this->shopAttachments = $shopAttachments;

        return $this;
    }

    /**
     * Get shop attachments.
     *
     * @return array
     */
    public function getShopAttachments()
    {
        return $this->shopAttachments;
    }

    /**
     * Set attachments.
     *
     * @param $attachments
     *
     * @return Shop
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Get attachments.
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * set start string.
     *
     * @param $start
     *
     * @return Shop
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start string.
     *
     * @return string
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * set end string.
     *
     * @param $end
     *
     * @return Shop
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end string.
     *
     * @return string
     */
    public function getEnd()
    {
        return $this->end;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
        );
    }
}
