<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use JMS\Serializer\Annotation as Serializer;

/**
 * ShopSpecItem.
 *
 * @ORM\Table(name="ShopSpecItem")
 * @ORM\Entity
 */
class ShopSpecItem implements JsonSerializable
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
     * @ORM\Column(name="specId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $specId;

    /**
     * @var ShopSpec
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopSpec")
     * @ORM\JoinColumn(name="specId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main"})
     */
    private $spec;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="integer")
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $amount = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2)
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $price = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="inventory", type="boolean")
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $inventory = false;

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
     * @return ShopSpecItem
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
     * Set amount.
     *
     * @param int $amount
     *
     * @return ShopSpecItem
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set inventory.
     *
     * @param bool $inventory
     *
     * @return ShopSpecItem
     */
    public function setInventory($inventory)
    {
        $this->inventory = $inventory;

        return $this;
    }

    /**
     * Get inventory.
     *
     * @return bool
     */
    public function hasInventory()
    {
        return $this->inventory;
    }

    /**
     * Set price.
     *
     * @param string $price
     *
     * @return ShopSpecItem
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set specId.
     *
     * @param $specId
     *
     * @return ShopSpecItem
     */
    public function setSpecId($specId)
    {
        $this->specId = $specId;

        return $this;
    }

    /**
     * Get specId.
     *
     * @return int
     */
    public function getSpecId()
    {
        return $this->specId;
    }

    /**
     * Get Spec.
     *
     * @return ShopSpec
     */
    public function getSpec()
    {
        return $this->spec;
    }

    /**
     * Set Spec.
     *
     * @param ShopSpec $spec
     *
     * @return ShopSpec
     */
    public function setSpec($spec)
    {
        $this->spec = $spec;

        return $this;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
        );
    }
}
