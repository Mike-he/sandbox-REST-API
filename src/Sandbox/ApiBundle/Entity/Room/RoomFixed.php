<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomFixed.
 *
 * @ORM\Table(
 *      name="room_fixed",
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
     * @Serializer\Groups({"main", "admin_room", "client", "current_order", "admin_detail", "admin_spaces"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="roomId", type="integer")
     */
    private $roomId;

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
     * @Serializer\Groups({"main", "admin_room", "client", "current_order", "admin_detail", "admin_spaces"})
     */
    private $seatNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="basePrice", type="decimal", nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_room", "current_order", "admin_detail", "admin_spaces"})
     */
    private $basePrice;

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
     * Set basePrice.
     *
     * @param string $basePrice
     *
     * @return RoomFixed
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
     * Set RoomS.
     *
     * @param Room $roomId
     *
     * @return RoomFixed
     */
    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Get Room.
     *
     * @return Room
     */
    public function getRoomId()
    {
        return $this->roomId;
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
}
