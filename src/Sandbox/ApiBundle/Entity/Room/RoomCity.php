<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JsonSerializable;

/**
 * RoomCity.
 *
 * @ORM\Table(
 *      name="RoomCity",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="key_UNIQUE", columns={"key"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Room\RoomCityRepository")
 */
class RoomCity implements JsonSerializable
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
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=16, nullable=false)
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
     *      "shop_nearby"
     * })
     */
    private $key;

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
     * Set key.
     *
     * @param string $key
     *
     * @return RoomCity
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
        );
    }
}
