<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Room
 *
 * @ORM\Table(
 *  name="Room",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="buildingId_number_UNIQUE", columns={"buildingId", "number"}),
 *      @ORM\UniqueConstraint(name="floorId_number_UNIQUE", columns={"floorId", "number"})
 *  },
 *  indexes={
 *      @ORM\Index(name="fk_Room_cityId_idx", columns={"cityId"}),
 *      @ORM\Index(name="fk_Room_buildingId_idx", columns={"buildingId"}),
 *      @ORM\Index(name="fk_Room_floorId_idx", columns={"floorId"})})
 * @ORM\Entity
 */
class Room
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
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
     * @var integer
     *
     * @ORM\Column(name="cityId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $cityId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomCity
     *
     * @ORM\OneToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomCity")
     * @ORM\JoinColumn(name="cityId", referencedColumnName="id")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $city;

    /**
     * @var integer
     *
     * @ORM\Column(name="buildingId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $buildingId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomBuilding
     *
     * @ORM\OneToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuilding")
     * @ORM\JoinColumn(name="buildingId", referencedColumnName="id")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $building;

    /**
     * @var integer
     *
     * @ORM\Column(name="floorId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $floorId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomFloor
     *
     * @ORM\OneToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomFloor")
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
     * @var integer
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
     * @var integer
     *
     * @ORM\Column(name="allowedPeople", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $allowedPeople;

    /**
     * @Serializer\Groups({"admin_room"})
     **/
    private $officeSupplies;

    /**
     * @var RoomMeeting
     *
     * @ORM\OneToOne(
     *      targetEntity="Sandbox\ApiBundle\Entity\Room\RoomMeeting",
     *      mappedBy="roomId",
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
     *      mappedBy="roomId",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="roomId")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $fixed;

    /**
     * @var RoomRentedDate
     *
     * @ORM\OneToMany(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomRentedDate", mappedBy="roomId")
     * @ORM\JoinColumn(name="id", referencedColumnName="roomId")
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $rentedDates;

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
     * @var integer
     */
    private $attachmentId;

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
     * Set cityId
     *
     * @param $cityId
     * @return Room
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
     * Set buildingId
     *
     * @param $buildingId
     * @return Room
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;

        return $this;
    }

    /**
     * Get buildingId
     *
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * Set floorId
     *
     * @param  integer $floorId
     * @return Room
     */
    public function setFloorId($floorId)
    {
        $this->floorId = $floorId;

        return $this;
    }

    /**
     * Get floorId
     *
     * @return integer
     */
    public function getFloorId()
    {
        return $this->floorId;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return Room
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
     * Set description
     *
     * @param  string $description
     * @return Room
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
     * Set number
     *
     * @param  string $number
     * @return Room
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set area
     *
     * @param  integer $area
     * @return Room
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return integer
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Get allowed people
     *
     * @return int
     */
    public function getAllowedPeople()
    {
        return $this->allowedPeople;
    }

    /**
     * set allowed people
     *
     * @param $allowedPeople
     * @return $this
     */
    public function setAllowedPeople($allowedPeople)
    {
        $this->allowedPeople = $allowedPeople;

        return $this;
    }

    /**
     * Set officeSupplies
     *
     * @param  integer $officeSupplies
     * @return Room
     */
    public function setOfficeSupplies($officeSupplies)
    {
        $this->officesupplies = $officeSupplies;

        return $this;
    }

    /**
     * Get officeSupplies
     *
     * @return integer
     */
    public function getOfficeSupplies()
    {
        return $this->officeSupplies;
    }

    /**
     * Set type
     *
     * @param  string $type
     * @return Room
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set creationDate
     *
     * @param  \DateTime $creationDate
     * @return Room
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
     * @param  \DateTime $modificationDate
     * @return Room
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
     * Get meeting
     *
     * @return RoomMeeting
     */
    public function getMeeting()
    {
        return $this->meeting;
    }

    /**
     * Set meeting
     *
     * @param  RoomMeeting $meeting
     * @return Room
     */
    public function setMeeting($meeting)
    {
        $this->meeting = $meeting;

        return $this;
    }

    /**
     * Get fixed
     *
     * @return RoomFixed
     */
    public function getFixed()
    {
        return $this->fixed;
    }

    /**
     * Set fixed
     *
     * @param  RoomFixed $fixed
     * @return Room
     */
    public function setFixed($fixed)
    {
        $this->fixed = $fixed;

        return $this;
    }

    /**
     * Get city
     *
     * @return Room
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Get building
     *
     * @return Room
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * Get floor
     *
     * @return Room
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * Get RoomRentedDate
     *
     * @return RoomRentedDate
     */
    public function getRentedDates()
    {
        return $this->rentedDates;
    }

    /**
     * @return integer
     */
    public function getAttachmentId()
    {
        return $this->attachmentId;
    }

    /**
     * @param  integer $attachmentId
     * @return Room
     */
    public function setAttachmentId($attachmentId)
    {
        $this->attachmentId = $attachmentId;

        return $this;
    }

    /**
     * Set floor
     *
     * @param  RoomFloor $floor
     * @return Room
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;

        return $this;
    }

    /**
     * Set city
     *
     * @param  RoomCity $city
     * @return Room
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Set building
     *
     * @param  RoomBuilding $building
     * @return Room
     */
    public function setBuilding($building)
    {
        $this->building = $building;

        return $this;
    }
}
