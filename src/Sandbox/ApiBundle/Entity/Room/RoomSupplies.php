<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomSupplies.
 *
 * @ORM\Table(
 *      name="RoomSupplies",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="roomId_suppliesId_UNIQUE", columns={"roomId", "suppliesId"}
 *          )
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_RoomSupplies_roomId_idx", columns={"roomId"})
 *      }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\Room\RoomSuppliesRepository"
 * )
 */
class RoomSupplies
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
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\Room", inversedBy="officeSupplies")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="roomId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     *
     * @Serializer\Groups({"main"})
     */
    private $room;

    /**
     * @var Supplies
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\Supplies")
     * @ORM\JoinColumn(name="suppliesId", referencedColumnName="id", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $supply;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $quantity;

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

    /**
     * Set Supplies.
     *
     * @param Supplies $supply
     *
     * @return RoomSupplies
     */
    public function setSupply($supply)
    {
        $this->supply = $supply;

        return $this;
    }

    /**
     * Get Supplies.
     *
     * @return Supplies
     */
    public function getSupply()
    {
        return $this->supply;
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
}
