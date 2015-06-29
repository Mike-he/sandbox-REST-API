<?php

namespace Sandbox\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomAttachmentBinding
 *
 * @ORM\Table(name="RoomAttachmentBinding")
 * @ORM\Entity
 */
class RoomAttachmentBinding
{
    /**
     * @var integer
     *
     * @ORM\Column(name="roomId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $roomId;

    /**
     * @var integer
     *
     * @ORM\Column(name="attachmentId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $attachmentId;

    /**
     * Set roomId
     *
     * @param  integer               $roomId
     * @return RoomAttachmentBinding
     */
    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Get roomId
     *
     * @return integer
     */
    public function getRoomId()
    {
        return $this->roomId;
    }

    /**
     * Set attachmentId
     *
     * @param  integer               $attachmentId
     * @return RoomAttachmentBinding
     */
    public function setAttachmentId($attachmentId)
    {
        $this->attachmentId = $attachmentId;

        return $this;
    }

    /**
     * Get attachmentId
     *
     * @return integer
     */
    public function getAttachmentId()
    {
        return $this->attachmentId;
    }
}
