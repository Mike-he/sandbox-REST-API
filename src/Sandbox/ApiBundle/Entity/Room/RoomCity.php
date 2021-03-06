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
    const LEVEL_COUNTRY = 1;
    const LEVEL_PROVINCE = 2;
    const LEVEL_CITY = 3;
    const LEVEL_AREA = 4;

    const TYPE_INTERNATIONAL = 'international';
    const TYPE_INTERNAL = 'internal';

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
     *      "client_shop",
     *      "admin_appointment"
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
     *      "client_shop",
     *      "admin_appointment"
     * })
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="enName", type="string", length=255, nullable=true)
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
     *      "client_shop",
     *      "admin_appointment"
     * })
     */
    private $enName;

    /**
     * @var string
     *
     * @ORM\Column(name="`key`", type="string", length=16, nullable=true)
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
    private $key;

    /**
     * @var int
     *
     * @ORM\Column(name="level", type="integer")
     */
    private $level;

    /**
     * @var bool
     *
     * @ORM\Column(name="capital", type="boolean")
     */
    private $capital = false;

    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="float", precision=9, scale=6, nullable=true)
     *
     * @Serializer\Groups({
     *      "main",
     *      "client"
     * })
     */
    private $lat;

    /**
     * @var float
     *
     * @ORM\Column(name="lng", type="float", precision=9, scale=6, nullable=true)
     *
     * @Serializer\Groups({
     *      "main",
     *      "client"
     * })
     */
    private $lng;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string",nullable=true)
     *
     * @Serializer\Groups({
     *      "main",
     *      "client"
     * })
     */
    private $code;

    /**
     * @var bool
     *
     * @ORM\Column(name="hot", type="boolean")
     */
    private $hot = false;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=true)
     */
    private $type;

    /**
     * @var string
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
     *      "client_shop",
     *      "admin_appointment"
     * })
     *
     * @ORM\Column(name="timezone", type="string", length=64 ,nullable=true)
     */
    private $timezone;

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
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
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
     * @return string
     */
    public function getEnName()
    {
        return $this->enName;
    }

    /**
     * @param string $enName
     */
    public function setEnName($enName)
    {
        $this->enName = $enName;
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

    /**
     * @return bool
     */
    public function isCapital()
    {
        return $this->capital;
    }

    /**
     * @param bool $capital
     */
    public function setCapital($capital)
    {
        $this->capital = $capital;
    }

    /**
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param float $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param float $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function isHot()
    {
        return $this->hot;
    }

    /**
     * @param bool $hot
     */
    public function setHot($hot)
    {
        $this->hot = $hot;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
