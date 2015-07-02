<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomMeeting.
 *
 * @ORM\Table(
 *      name="RoomMeeting",
 *      indexes={
 *          @ORM\Index(name="fk_RoomMeeting_roomId_idx", columns={"roomId"})
 *      }
 * )
 * @ORM\Entity
 */
class RoomMeeting
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
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\Room", inversedBy="meeting")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="roomId", referencedColumnName="id")
     * })
     *
     * @Serializer\Groups({"main"})
     */
    private $room;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startHour", type="time", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $startHour;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endHour", type="time", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $endHour;

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
     * Set startHour.
     *
     * @param \DateTime $startHour
     *
     * @return RoomMeeting
     */
    public function setStartHour($startHour)
    {
        $this->startHour = $startHour;

        return $this;
    }

    /**
     * Get startHour.
     *
     * @return \DateTime
     */
    public function getStartHour()
    {
        return $this->startHour;
    }

    /**
     * Set endHour.
     *
     * @param \DateTime $endHour
     *
     * @return RoomMeeting
     */
    public function setEndHour($endHour)
    {
        $this->endHour = $endHour;

        return $this;
    }

    /**
     * Get endHour.
     *
     * @return \DateTime
     */
    public function getEndHour()
    {
        return $this->endHour;
    }

    /**
     * @return Room
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param Room $room
     *
     * @return Room
     */
    public function setRoom($room)
    {
        $this->room = $room;

        return $this;
    }
}
