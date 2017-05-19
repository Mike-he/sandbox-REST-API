<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Room.
 *
 * @ORM\Table(
 *  name="room",
 *  indexes={
 *      @ORM\Index(name="fk_Room_cityId_idx", columns={"cityId"}),
 *      @ORM\Index(name="fk_Room_buildingId_idx", columns={"buildingId"}),
 *      @ORM\Index(name="fk_Room_floorId_idx", columns={"floorId"})})
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\Room\RoomRepository"
 * )
 */
class Room
{
    const TYPE_OFFICE = 'office';
    const TYPE_MEETING = 'meeting';
    const TYPE_DESK = 'desk';
    const TYPE_OTHERS = 'others';

    const TAG_HOT_DESK = 'hot_desk';
    const TAG_DEDICATED_DESK = 'dedicated_desk';


    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "current_order", "client_appointment_list"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "current_order", "client_appointment_list"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "current_order"})
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="cityId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $cityId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomCity
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomCity")
     * @ORM\JoinColumn(name="cityId", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "current_order", "admin_appointment"})
     */
    private $city;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $buildingId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomBuilding
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuilding")
     * @ORM\JoinColumn(name="buildingId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "current_order", "client_appointment_detail"})
     */
    private $building;

    /**
     * @var int
     *
     * @ORM\Column(name="floorId", type="integer", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $floorId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomFloor
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomFloor")
     * @ORM\JoinColumn(name="floorId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "current_order"})
     */
    private $floor;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "current_order", "admin_appointment"})
     */
    private $number;

    /**
     * @var int
     *
     * @ORM\Column(name="area", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "client_appointment_detail"})
     */
    private $area;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64, nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "current_order", "client_appointment_detail"})
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="type_tag", type="string", length=64, nullable=true)
     */
    private $typeTag;

    /**
     * @var string
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "current_order"})
     */
    private $typeDescription;

    /**
     * @var int
     *
     * @ORM\Column(name="allowedPeople", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "client_appointment_detail"})
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
     * @Serializer\Groups({"main", "admin_room", "client"})
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
     * @Serializer\Groups({"main", "admin_room", "client"})
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
     * @Serializer\Groups({"main", "admin_room", "client", "current_order"})
     */
    private $fixed;

    /**
     * @var bool
     *
     * @ORM\Column(name="isDeleted", type="boolean", nullable=false)
     */
    private $isDeleted = false;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
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
     * @ORM\OrderBy({"id" = "ASC"})
     *
     * @Serializer\Groups({"main", "admin_room", "client", "current_order", "client_appointment_list"})
     */
    private $attachment;

    /**
     * @var string
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $rentType;

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
     * @return string
     */
    public function getTypeTag()
    {
        return $this->typeTag;
    }

    /**
     * @param string $typeTag
     */
    public function setTypeTag($typeTag)
    {
        $this->typeTag = $typeTag;
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
     * @return RoomBuilding
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
     * @return bool
     */
    public function isDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @param bool $isDeleted
     *
     * @return Room
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * @return Room
     */
    public function getTypeDescription()
    {
        return $this->typeDescription;
    }

    /**
     * @param string $description
     *
     * @return string
     */
    public function setTypeDescription($description)
    {
        $this->typeDescription = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getRentType()
    {
        return $this->rentType;
    }

    /**
     * @param string $rentType
     */
    public function setRentType($rentType)
    {
        $this->rentType = $rentType;
    }

    public function degenerateAttachment()
    {
        $attachment = array_map(
            function ($attachment) {
                return
                    $attachment->getAttachmentId()->getContent()
                ;
            },
            $this->attachment->toArray()
        );

        return $attachment[0];
    }
}
