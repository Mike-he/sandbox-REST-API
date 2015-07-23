<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomDoors.
 *
 * @ORM\Table(
 *  name="RoomDoors",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="roomId_doorControlId", columns={"roomId", "doorControlId"})
 *  },
 *  indexes={
 *      @ORM\Index(name="fk_RoomDoors_roomId_idx", columns={"roomId"})
 *  }
 * )
 * @ORM\Entity
 */
class RoomDoors
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Room
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\Room")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="roomId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $room;

    /**
     * @var string
     *
     * @ORM\Column(name="doorControlId", type="string", length=255, nullable=false)
     */
    private $doorControlId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

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
     * Set doorControlId.
     *
     * @param string $doorControlId
     *
     * @return RoomDoors
     */
    public function setDoorControlId($doorControlId)
    {
        $this->doorControlId = $doorControlId;

        return $this;
    }

    /**
     * Get doorControlId.
     *
     * @return string
     */
    public function getDoorControlId()
    {
        return $this->doorControlId;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return RoomDoors
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
     * Set Room.
     *
     * @param Room $room
     *
     * @return RoomDoors
     */
    public function setRoom($room)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Get Room.
     *
     * @return Room
     */
    public function getRoom()
    {
        return $this->room;
    }

    public function __construct()
    {
        $this->setCreationDate(new \DateTime('now'));
    }
}
