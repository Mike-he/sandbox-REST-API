<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomTypeUnit.
 *
 * @ORM\Table(name="room_type_unit")
 * @ORM\Entity
 */
class RoomTypeUnit
{

    const UNIT_HOUR = 'hour';
    const UNIT_DAY = 'day';
    const UNIT_WEEK = 'week';
    const UNIT_MONTH = 'month';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="unit", type="string", length=16)
     *
     * @Serializer\Groups({"main", "drop_down"})
     */
    private $unit;

    /**
     * @var int
     *
     * @ORM\Column(name="typeId", type="integer")
     */
    private $typeId;

    /**
     * @var RoomTypes
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typeId", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $type;

    /**
     * @var string
     *
     * @Serializer\Groups({"main", "drop_down"})
     */
    private $description;

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
     * Set unit.
     *
     * @param string $unit
     *
     * @return RoomTypeUnit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get unit.
     *
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set typeId.
     *
     * @param int $typeId
     *
     * @return RoomTypeUnit
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId.
     *
     * @return int
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set type.
     *
     * @param RoomTypes $type
     *
     * @return RoomTypeUnit
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return RoomTypes
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return RoomTypeUnit
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
