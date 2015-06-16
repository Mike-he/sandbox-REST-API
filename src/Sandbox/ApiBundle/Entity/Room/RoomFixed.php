<?php

namespace Sandbox\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomFixed
 *
 * @ORM\Table(name="RoomFixed", indexes={@ORM\Index(name="fk_RoomFixed_roomId_idx", columns={"roomId"})})
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
     */
    private $id;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="roomId", referencedColumnName="id")
     * })
     */
    private $roomId;

    /**
     * @var integer
     *
     * @ORM\Column(name="seatNumber", type="integer", nullable=false)
     */
    private $seatNumber;

    /**
     * @var boolean
     *
     * @ORM\Column(name="available", type="boolean", nullable=false)
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
     * Set roomId
     *
     * @param  \Sandbox\ApiBundle\Entity\Room $roomId
     * @return RoomFixed
     */
    public function setRoomId(\Sandbox\ApiBundle\Entity\Room $roomId = null)
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Get roomId
     *
     * @return \Sandbox\ApiBundle\Entity\Room
     */
    public function getRoomId()
    {
        return $this->roomId;
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
