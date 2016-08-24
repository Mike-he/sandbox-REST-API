<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomTypes.
 *
 * @ORM\Table(name="RoomTypes")
 * @ORM\Entity
 */
class RoomTypes
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=16)
     *
     * @Serializer\Groups({"main", "drop_down"})
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Room\RoomTypeUnit",
     *      mappedBy="type",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="typeId")
     *
     * @Serializer\Groups({"main", "drop_down"})
     */
    private $units;

    /**
     * @var string
     *
     * @Serializer\Groups({"main", "drop_down"})
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="text")
     *
     * @Serializer\Groups({"main"})
     */
    private $icon;

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
     * Set name.
     *
     * @param string $name
     *
     * @return RoomTypes
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set units.
     *
     * @param array $units
     *
     * @return RoomTypes
     */
    public function setUnits($units)
    {
        $this->units = $units;

        return $this;
    }

    /**
     * Get units.
     *
     * @return array
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return RoomTypes
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

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }
}
