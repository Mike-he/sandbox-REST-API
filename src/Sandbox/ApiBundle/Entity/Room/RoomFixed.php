<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomFixed
 *
 * @ORM\Table(
 *      name="RoomFixed",
 *      indexes={
 *          @ORM\Index(name="fk_RoomFixed_roomId_idx", columns={"roomId"})
 *      }
 * )
 * @ORM\Entity
 */
class RoomFixed
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
     * @var \Sandbox\ApiBundle\Entity\Room\Room
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\Room", inversedBy="fixed")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="roomId", referencedColumnName="id")
     * })
     *
     * @Serializer\Groups({"main"})
     */
    private $room;

    /**
     * @var integer
     *
     * @ORM\Column(name="seatNumber", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $seatNumber;

    /**
     * @var boolean
     *
     * @ORM\Column(name="available", type="boolean", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room"})
     */
    private $available;

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
     * Set RoomS
     *
     * @param  Room      $room
     * @return RoomFixed
     */
    public function setRoom($room)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Get Room
     *
     * @return Room
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * Set seatNumber
     *
     * @param  integer   $seatNumber
     * @return RoomFixed
     */
    public function setSeatNumber($seatNumber)
    {
        $this->seatNumber = $seatNumber;

        return $this;
    }

    /**
     * Get seatNumber
     *
     * @return integer
     */
    public function getSeatNumber()
    {
        return $this->seatNumber;
    }

    /**
     * Set available
     *
     * @param  boolean   $available
     * @return RoomFixed
     */
    public function setAvailable($available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Get available
     *
     * @return boolean
     */
    public function getAvailable()
    {
        return $this->available;
    }
}
