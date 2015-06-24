<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;

/**
 * Room
 *
 * @ORM\Table(name="Room")
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
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var RoomAttachment
     *
     * @ORM\OneToMany(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomAttachment", mappedBy="roomId")
     * @ORM\JoinColumn(name="id", referencedColumnName="roomId")
     */
    private $roomAttachment;

    /**
     * @var RoomRentedDate
     *
     * @ORM\OneToMany(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomRentedDate", mappedBy="roomId")
     * @ORM\JoinColumn(name="id", referencedColumnName="roomId")
     */
    private $rentedDate;

    /**
     * @var RoomMeeting
     *
     * @ORM\OneToMany(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomMeeting", mappedBy="roomId")
     * @ORM\JoinColumn(name="id", referencedColumnName="roomId")
     */
    private $meeting;

    /**
     * @var RoomFixed
     *
     * @ORM\OneToMany(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomFixed", mappedBy="roomId")
     * @ORM\JoinColumn(name="id", referencedColumnName="roomId")
     */
    private $roomFixed;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \Sandbox\ApiBundle\Entity\Location\City
     *
     * @ORM\OneToOne(targetEntity="Sandbox\ApiBundle\Entity\Location\City")
     * @ORM\JoinColumn(name="city", referencedColumnName="id")
     *
     */
    private $city;

    /**
     * @var \Sandbox\ApiBundle\Entity\Location\Building
     *
     * @ORM\OneToOne(targetEntity="Sandbox\ApiBundle\Entity\Location\Building")
     * @ORM\JoinColumn(name="building", referencedColumnName="id")
     *
     */
    private $building;

    /**
     * @var \Sandbox\ApiBundle\Entity\Location\Floor
     *
     * @ORM\OneToOne(targetEntity="Sandbox\ApiBundle\Entity\Location\Floor")
     * @ORM\JoinColumn(name="floor", referencedColumnName="id")
     *
     */
    private $floor;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=64, nullable=false)
     */
    private $number;

    /**
     * @var integer
     *
     * @ORM\Column(name="allowedPeople", type="integer", nullable=false)
     */
    private $allowedPeople;

    /**
     * @var integer
     *
     * @ORM\Column(name="area", type="integer", nullable=false)
     */
    private $area;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     */
    private $modificationDate;

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
     * Set RoomMeeting
     *
     * @param  object $meeting
     * @return Room
     */
    public function setMeeting($meeting)
    {
        $this->meeting = $meeting;

        return $this;
    }

    /**
     * Get RoomMeeting
     *
     * @return RoomMeeting
     */
    public function getMeeting()
    {
        return $this->meeting;
    }

    /**
     * Set RoomFixed
     *
     * @param  object $roomFixed
     * @return Room
     */
    public function setRoomFixed($roomFixed)
    {
        $this->roomFixed = $roomFixed;

        return $this;
    }

    /**
     * Get RoomFixed
     *
     * @return RoomFixed
     */
    public function getRoomFixed()
    {
        return $this->roomFixed;
    }

    /**
     * Set RoomRentedDate
     *
     * @param  object $rentedDate
     * @return Room
     */
    public function setRentedDate($rentedDate)
    {
        $this->rentedDate = $rentedDate;

        return $this;
    }

    /**
     * Get RoomRentedDate
     *
     * @return RoomRentedDate
     */
    public function getRentedDate()
    {
        return $this->rentedDate;
    }

    /**
     * Set RoomAttachment
     *
     * @param  object $attachment
     * @return Room
     */
    public function setRoomAttachment($attachment)
    {
        $this->roomAttachment = $attachment;

        return $this;
    }

    /**
     * Get RoomAttachment
     *
     * @return RoomAttachment
     */
    public function getRoomAttachment()
    {
        return $this->roomAttachment;
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
     * Set city
     *
     * @param  string $city
     * @return Room
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set building
     *
     * @param  string $building
     * @return Room
     */
    public function setBuilding($building)
    {
        $this->building = $building;

        return $this;
    }

    /**
     * Get building
     *
     * @return string
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * Set floor
     *
     * @param  integer $floor
     * @return Room
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;

        return $this;
    }

    /**
     * Get floor
     *
     * @return integer
     */
    public function getFloor()
    {
        return $this->floor;
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
     * Set allowedPeople
     *
     * @param  integer $allowedPeople
     * @return Room
     */
    public function setAllowedPeople($allowedPeople)
    {
        $this->allowedPeople = $allowedPeople;

        return $this;
    }

    /**
     * Get allowedPeople
     *
     * @return integer
     */
    public function getAllowedPeople()
    {
        return $this->allowedPeople;
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
}
