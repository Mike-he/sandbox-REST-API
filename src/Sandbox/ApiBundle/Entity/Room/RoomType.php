<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomType.
 *
 * @ORM\Table(name="RoomType")
 * @ORM\Entity
 */
class RoomType
{
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
     * @return RoomType
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
     * @return RoomType
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
     * @return RoomType
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
