<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomAttachmentBinding.
 *
 * @ORM\Table(
 *      name="RoomAttachmentBinding",
 *      indexes={
 *          @ORM\Index(name="fk_RoomAttachmentBinding_roomId_idx", columns={"roomId"})
 *      }
 * )
 * @ORM\Entity
 */
class RoomAttachmentBinding
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Groups({"main"})
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
     * @var int
     *
     * @ORM\Column(name="attachmentId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $attachmentId;

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
     * Set attachmentId.
     *
     * @param int $attachmentId
     *
     * @return RoomAttachmentBinding
     */
    public function setAttachmentId($attachmentId)
    {
        $this->attachmentId = $attachmentId;

        return $this;
    }

    /**
     * Get attachmentId.
     *
     * @return int
     */
    public function getAttachmentId()
    {
        return $this->attachmentId;
    }

    /**
     * Set room.
     *
     * @param Room $room
     *
     * @return RoomAttachmentBinding
     */
    public function setRoom($room)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Get roomId.
     *
     * @return Room
     */
    public function getRoomId()
    {
        return $this->room;
    }
}
