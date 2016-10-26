<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomBuildingTag.
 *
 * @ORM\Table(name="room_building_tag")
 * @ORM\Entity
 */
class RoomBuildingTag
{
    const TRANS_PREFIX = 'building.tag.';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "list", "admin_building", "build_filter"})
     */
    private $id;

    /**
     * @var string
     *
     * @Serializer\Groups({"main", "list", "build_filter"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=64)
     *
     * @Serializer\Groups({"main", "list"})
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="text")
     *
     * @Serializer\Groups({"main", "list"})
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
     * @return RoomBuildingTag
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
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Set icon.
     *
     * @param string $icon
     *
     * @return RoomBuildingTag
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }
}
