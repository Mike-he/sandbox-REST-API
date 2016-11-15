<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomCity.
 *
 * @ORM\Table(name="room_city")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Room\RoomCityRepository")
 */
class RoomCity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_room",
     *      "client",
     *      "admin_detail",
     *      "admin_event",
     *      "client_event",
     *      "current_order",
     *      "building_nearby",
     *      "admin_building",
     *      "admin_shop",
     *      "client_order",
     *      "shop_nearby",
     *      "client_shop"
     * })
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="parentId", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * @ORM\ManyToOne(targetEntity="RoomCity")
     * @ORM\JoinColumn(name="parentId", referencedColumnName="id", onDelete="SET NULL")
     */
    private $parent;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_room",
     *      "client",
     *      "admin_detail",
     *      "admin_event",
     *      "client_detail",
     *      "client_event",
     *      "current_order",
     *      "building_nearby",
     *      "admin_building",
     *      "admin_shop",
     *      "client_order",
     *      "shop_nearby",
     *      "client_shop"
     * })
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="level", type="integer")
     */
    private $level;

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
     * Set parentId.
     *
     * @param int $parentId
     *
     * @return RoomCity
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return RoomCity
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
     * Set level.
     *
     * @param int $level
     *
     * @return RoomCity
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level.
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }
}
