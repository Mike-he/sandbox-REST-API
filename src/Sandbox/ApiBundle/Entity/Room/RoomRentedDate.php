<?php

namespace Sandbox\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomRentedDate
 *
 * @ORM\Table(name="RoomRentedDate", indexes={@ORM\Index(name="fk_RoomRentedDate_roomId_idx", columns={"roomId"})})
 * @ORM\Entity
 */
class RoomRentedDate
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
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime", nullable=false)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime", nullable=false)
     */
    private $endDate;

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
     * @return RoomRentedDate
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
     * Set startDate
     *
     * @param  \DateTime      $startDate
     * @return RoomRentedDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param  \DateTime      $endDate
     * @return RoomRentedDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
}
