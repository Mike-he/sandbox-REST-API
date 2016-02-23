<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use JMS\Serializer\Annotation as Serializer;

/**
 * ShopSpec.
 *
 * @ORM\Table(name="ShopSpec")
 * @ORM\Entity
 */
class ShopSpec implements JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="shopId", type="integer")
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $shopId;

    /**
     * @var Shop
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\Shop")
     * @ORM\JoinColumn(name="shopId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main"})
     */
    private $shop;

    /**
     * @var ShopSpecItem
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopSpecItem",
     *      mappedBy="spec",
     *      cascade={"persist"}
     * )
     *
     * @ORM\JoinColumn(name="id", referencedColumnName="specId")
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $specItems;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="multiple", type="boolean")
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $multiple = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="optional", type="boolean")
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $optional = false;

    /**
     * @var array
     */
    private $items;

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
     * @return ShopSpec
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
     * Set description.
     *
     * @param string $description
     *
     * @return ShopSpec
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
     * Set multiple.
     *
     * @param bool $multiple
     *
     * @return ShopSpec
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * Get multiple.
     *
     * @return bool
     */
    public function getMultiple()
    {
        return $this->multiple;
    }

    /**
     * Set optional.
     *
     * @param bool $optional
     *
     * @return ShopSpec
     */
    public function setOptional($optional)
    {
        $this->optional = $optional;

        return $this;
    }

    /**
     * Get optional.
     *
     * @return bool
     */
    public function getOptional()
    {
        return $this->optional;
    }

    /**
     * @return ShopSpecItem
     */
    public function getSpecItems()
    {
        return $this->specItems;
    }

    /**
     * @param ShopSpecItem $item
     *
     * @return ShopSpec
     */
    public function setSpecItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Set shopId.
     *
     * @param int $shopId
     *
     * @return ShopSpec
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * Get shopId.
     *
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * Set Shop.
     *
     * @param Shop $shop
     *
     * @return ShopSpec
     */
    public function setShop($shop)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * Get Shop.
     *
     * @return Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * Set items.
     *
     * @param $items
     *
     * @return Shop
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Get items.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
        );
    }
}
