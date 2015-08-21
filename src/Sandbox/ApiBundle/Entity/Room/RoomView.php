<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Room.
 *
 * @ORM\Table(name="RoomView")
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\Room\RoomRepository"
 * )
 */
class RoomView
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $description;

    /**
     * @var int
     *
     * @Serializer\Groups({"main"})
     */
    private $cityId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomCity
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomCity")
     * @ORM\JoinColumn(name="cityId", referencedColumnName="id")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $city;

    /**
     * @var int
     *
     * @Serializer\Groups({"main"})
     */
    private $buildingId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomBuilding
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuilding")
     * @ORM\JoinColumn(name="buildingId", referencedColumnName="id")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $building;

    /**
     * @var int
     *
     * @Serializer\Groups({"main"})
     */
    private $floorId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomFloor
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomFloor")
     * @ORM\JoinColumn(name="floorId", referencedColumnName="id")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $floor;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=64, nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $number;

    /**
     * @var int
     *
     * @ORM\Column(name="area", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $area;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="allowedPeople", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $allowedPeople;

    /**
     * @var RoomDoors
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Room\RoomDoors",
     *      mappedBy="room",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="roomId")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $doorControl;

    /**
     * @var RoomSupplies
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Room\RoomSupplies",
     *      mappedBy="room",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="roomId")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $officeSupplies;

    /**
     * @var RoomMeeting
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Room\RoomMeeting",
     *      mappedBy="room",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="roomId")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $meeting;

    /**
     * @var RoomFixed
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Room\RoomFixed",
     *      mappedBy="room",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="roomId")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $fixed;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $modificationDate;

    /**
     * @var RoomAttachmentBinding
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Room\RoomAttachmentBinding",
     *      mappedBy="room",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="roomId")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $attachment;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="orderStartDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $orderStartDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="orderEndDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $orderEndDate;

    /**
     * @var int
     *
     * @ORM\Column(name="renterId", type="integer", length=64, nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $renterId;

    /**
     * @var string
     *
     * @ORM\Column(name="renterName", type="string", length=64, nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $renterName;

    /**
     * @var string
     *
     * @ORM\Column(name="renterEmail", type="string", length=128, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $renterEmail;

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
     * Set cityId.
     *
     * @param $cityId
     *
     * @return Room
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
     * Set buildingId.
     *
     * @param $buildingId
     *
     * @return Room
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;

        return $this;
    }

    /**
     * Get buildingId.
     *
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * Set floorId.
     *
     * @param int $floorId
     *
     * @return Room
     */
    public function setFloorId($floorId)
    {
        $this->floorId = $floorId;

        return $this;
    }

    /**
     * Get floorId.
     *
     * @return int
     */
    public function getFloorId()
    {
        return $this->floorId;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Room
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
     * @return Room
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
     * Set number.
     *
     * @param string $number
     *
     * @return Room
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number.
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set area.
     *
     * @param int $area
     *
     * @return Room
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area.
     *
     * @return int
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Get allowed people.
     *
     * @return int
     */
    public function getAllowedPeople()
    {
        return $this->allowedPeople;
    }

    /**
     * set allowed people.
     *
     * @param $allowedPeople
     *
     * @return $this
     */
    public function setAllowedPeople($allowedPeople)
    {
        $this->allowedPeople = $allowedPeople;

        return $this;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Room
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Room
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
     * @return Room
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
     * Get meeting.
     *
     * @return RoomMeeting
     */
    public function getMeeting()
    {
        return $this->meeting;
    }

    /**
     * Set meeting.
     *
     * @param RoomMeeting $meeting
     *
     * @return Room
     */
    public function setMeeting($meeting)
    {
        $this->meeting = $meeting;

        return $this;
    }

    /**
     * Get fixed.
     *
     * @return RoomFixed
     */
    public function getFixed()
    {
        return $this->fixed;
    }

    /**
     * Set fixed.
     *
     * @param RoomFixed $fixed
     *
     * @return Room
     */
    public function setFixed($fixed)
    {
        $this->fixed = $fixed;

        return $this;
    }

    /**
     * Get city.
     *
     * @return Room
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Get building.
     *
     * @return Room
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * Get floor.
     *
     * @return Room
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * @return RoomAttachmentBinding
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param RoomAttachmentBinding $attachment
     *
     * @return Room
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;

        return $this;
    }

    /**
     * @return RoomSupplies
     */
    public function getOfficeSupplies()
    {
        return $this->officeSupplies;
    }

    /**
     * @param RoomSupplies $officeSupplies
     *
     * @return Room
     */
    public function setOfficeSupplies($officeSupplies)
    {
        $this->officeSupplies = $officeSupplies;

        return $this;
    }

    /**
     * Set floor.
     *
     * @param RoomFloor $floor
     *
     * @return Room
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;

        return $this;
    }

    /**
     * Set city.
     *
     * @param RoomCity $city
     *
     * @return Room
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Set building.
     *
     * @param RoomBuilding $building
     *
     * @return Room
     */
    public function setBuilding($building)
    {
        $this->building = $building;

        return $this;
    }

    /**
     * @return RoomDoors
     */
    public function getDoorControl()
    {
        return $this->doorControl;
    }

    /**
     * @param RoomDoors $doorControl
     *
     * @return Room
     */
    public function setDoorControl($doorControl)
    {
        $this->doorControl = $doorControl;

        return $this;
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
     *
     * @return RoomView
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOrderStartDate()
    {
        return $this->orderStartDate;
    }

    /**
     * @param \DateTime $orderStartDate
     *
     * @return RoomView
     */
    public function setOrderStartDate($orderStartDate)
    {
        $this->orderStartDate = $orderStartDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOrderEndDate()
    {
        return $this->orderEndDate;
    }

    /**
     * @param \DateTime $orderEndDate
     *
     * @return RoomView
     */
    public function setOrderEndDate($orderEndDate)
    {
        $this->orderEndDate = $orderEndDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getRenterId()
    {
        return $this->renterId;
    }

    /**
     * @param string $renterId
     *
     * @return RoomView
     */
    public function setRenterId($renterId)
    {
        $this->renterId = $renterId;

        return $this;
    }

    /**
     * @return string
     */
    public function getRenterName()
    {
        return $this->renterName;
    }

    /**
     * @param string $renterName
     *
     * @return RoomView
     */
    public function setRenterName($renterName)
    {
        $this->renterName = $renterName;

        return $this;
    }

    /**
     * @return string
     */
    public function getRenterEmail()
    {
        return $this->renterEmail;
    }

    /**
     * @param string $renterEmail
     *
     * @return RoomView
     */
    public function setRenterEmail($renterEmail)
    {
        $this->renterEmail = $renterEmail;

        return $this;
    }
}
