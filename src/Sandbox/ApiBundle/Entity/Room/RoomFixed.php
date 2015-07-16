<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomFixed.
 *
 * @ORM\Table(
 *      name="RoomFixed",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="roomId_seatNumber_UNIQUE",columns={"roomId", "seatNumber"})
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_RoomFixed_roomId_idx", columns={"roomId"})
 *      }
 * )
 * @ORM\Entity
 */
class RoomFixed
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $id;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\Room
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\Room", inversedBy="fixed")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="roomId", referencedColumnName="id", onDelete="CASCADE")
     * })
     *
     * @Serializer\Groups({"main"})
     */
    private $room;

    /**
     * @var int
     *
     * @ORM\Column(name="seatNumber", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $seatNumber;

    /**
     * @var bool
     *
     * @ORM\Column(name="available", type="boolean", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $available;

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
     * Set RoomS.
     *
     * @param Room $room
     *
     * @return RoomFixed
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

    /**
     * Set seatNumber.
     *
     * @param int $seatNumber
     *
     * @return RoomFixed
     */
    public function setSeatNumber($seatNumber)
    {
        $this->seatNumber = $seatNumber;

        return $this;
    }

    /**
     * Get seatNumber.
     *
     * @return int
     */
    public function getSeatNumber()
    {
        return $this->seatNumber;
    }

    /**
     * Set available.
     *
     * @param bool $available
     *
     * @return RoomFixed
     */
    public function setAvailable($available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Get available.
     *
     * @return bool
     */
    public function getAvailable()
    {
        return $this->available;
    }
}
