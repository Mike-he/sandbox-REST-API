<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomBuildingServices.
 *
 * @ORM\Table(name="RoomBuildingServices")
 * @ORM\Entity
 */
class RoomBuildingServices
{
    const TRANS_PREFIX = 'building.service.';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "list", "admin_building"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     *
     * @Serializer\Groups({"main", "list"})
     */
    private $name;

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
     * @return RoomBuildingServices
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
     * Set icon.
     *
     * @param string $icon
     *
     * @return RoomBuildingServices
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
