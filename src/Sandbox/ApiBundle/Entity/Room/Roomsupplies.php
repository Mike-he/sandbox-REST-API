<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomSupplies.
 *
 * @ORM\Table(name="RoomSupplies", indexes={@ORM\Index(name="fk_RoomSupplies_roomId_idx", columns={"roomId"})})
 * @ORM\Entity
 */
class RoomSupplies
{
    /**
     * @var int
     *
     * @ORM\Column(name="suppliesId", type="integer", nullable=false)
     */
    private $suppliesId;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     */
    private $quantity;

    /**
     * @var int
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
     * Set suppliesId.
     *
     * @param int $suppliesId
     *
     * @return RoomSupplies
     */
    public function setSuppliesId($suppliesId)
    {
        $this->suppliesId = $suppliesId;

        return $this;
    }

    /**
     * Get suppliesId.
     *
     * @return int
     */
    public function getSuppliesId()
    {
        return $this->suppliesId;
    }

    /**
     * Set quantity.
     *
     * @param int $quantity
     *
     * @return RoomSupplies
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity.
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

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
     * Set roomId.
     *
     * @param \Sandbox\ApiBundle\Entity\Room $roomId
     *
     * @return RoomSupplies
     */
    public function setRoomId(\Sandbox\ApiBundle\Entity\Room $roomId = null)
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Get roomId.
     *
     * @return \Sandbox\ApiBundle\Entity\Room
     */
    public function getRoomId()
    {
        return $this->roomId;
    }
}
