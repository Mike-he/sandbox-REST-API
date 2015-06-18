<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomMeeting
 *
 * @ORM\Table(name="RoomMeeting", indexes={@ORM\Index(name="fk_RoomMeeting_roomId_idx", columns={"roomId"})})
 * @ORM\Entity
 */
class RoomMeeting
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
     * @var \Sandbox\ApiBundle\Entity\Room\Room
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\Room")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="roomId", referencedColumnName="id")
     * })
     */
    private $roomId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startHour", type="datetime", nullable=false)
     */
    private $startHour;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endHour", type="datetime", nullable=false)
     */
    private $endHour;

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
     * @param  \Sandbox\ApiBundle\Entity\Room\Room $roomId
     * @return RoomMeeting
     */
    public function setRoomId(\Sandbox\ApiBundle\Entity\Room\Room $roomId = null)
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Get roomId
     *
     * @return \Sandbox\ApiBundle\Entity\Room\Room
     */
    public function getRoomId()
    {
        return $this->roomId;
    }

    /**
     * Set startHour
     *
     * @param  \DateTime   $startHour
     * @return RoomMeeting
     */
    public function setStartHour($startHour)
    {
        $this->startHour = $startHour;

        return $this;
    }

    /**
     * Get startHour
     *
     * @return \DateTime
     */
    public function getStartHour()
    {
        return $this->startHour;
    }

    /**
     * Set endHour
     *
     * @param  \DateTime   $endHour
     * @return RoomMeeting
     */
    public function setEndHour($endHour)
    {
        $this->endHour = $endHour;

        return $this;
    }

    /**
     * Get endHour
     *
     * @return \DateTime
     */
    public function getEndHour()
    {
        return $this->endHour;
    }
}
