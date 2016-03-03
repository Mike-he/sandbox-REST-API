<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use JMS\Serializer\Annotation as Serializer;

/**
 * ShopProductSpecItem.
 *
 * @ORM\Table(
 *     name="ShopProductSpecItem",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="productSpecId_shopSpecItemId_UNIQUE", columns={"productSpecId", "shopSpecItemId"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Shop\ShopProductSpecItemRepository")
 */
class ShopProductSpecItem implements JsonSerializable
{
    const INSUFFICIENT_INVENTORY = 'Insufficient Inventory';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="productSpecId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $productSpecId;

    /**
     * @var ShopProductSpec
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopProductSpec")
     * @ORM\JoinColumn(name="productSpecId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main"})
     */
    private $productSpec;

    /**
     * @var decimal
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=true)
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $price;

    /**
     * @var bool
     *
     * @ORM\Column(name="inventory", type="integer", nullable=true)
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $inventory;

    /**
     * @var int
     *
     * @ORM\Column(name="shopSpecItemId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $shopSpecItemId;

    /**
     * @var ShopSpecItem
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopSpecItem")
     *
     * @ORM\JoinColumn(name="shopSpecItemId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $shopSpecItem;

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
     * Set price.
     *
     * @param decimal $price
     *
     * @return ShopProductSpecItem
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return decimal
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set inventory.
     *
     * @param int $inventory
     *
     * @return ShopProductSpecItem
     */
    public function setInventory($inventory)
    {
        $this->inventory = $inventory;

        return $this;
    }

    /**
     * Get inventory.
     *
     * @return int
     */
    public function getInventory()
    {
        return $this->inventory;
    }

    /**
     * Set productSpecId.
     *
     * @param $productSpecId
     *
     * @return ShopProductSpecItem
     */
    public function setProductSpecId($productSpecId)
    {
        $this->productSpecId = $productSpecId;

        return $this;
    }

    /**
     * Get productSpecId.
     *
     * @return int
     */
    public function getProductSpecId()
    {
        return $this->productSpecId;
    }

    /**
     * Get ProductSpec.
     *
     * @return ShopProductSpecItem
     */
    public function getProductSpec()
    {
        return $this->productSpec;
    }

    /**
     * Set ProductSpec.
     *
     * @param ShopProductSpec $productSpec
     *
     * @return ShopProductSpecItem
     */
    public function setProductSpec($productSpec)
    {
        $this->productSpec = $productSpec;

        return $this;
    }

    /**
     * set shopSpecItemId.
     *
     * @param int $shopSpecItemId
     *
     * @return ShopProductSpecItem
     */
    public function setShopSpecItemId($shopSpecItemId)
    {
        $this->shopSpecItemId = $shopSpecItemId;

        return $this;
    }

    /**
     * Get shopSpecItemId.
     *
     * @return int
     */
    public function getShopSpecItemId()
    {
        return $this->shopSpecItemId;
    }

    /**
     * Get shopSpecItem.
     *
     * @param $specItem
     *
     * @return ShopProductSpecItem
     */
    public function setShopSpecItem($specItem)
    {
        $this->shopSpecItem = $specItem;

        return $this;
    }

    /**
     * Get shopSpecItem.
     *
     * @return ShopSpecItem
     */
    public function getShopSpecItem()
    {
        return $this->shopSpecItem;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'price' => $this->price,
            'inventory' => $this->inventory,
            'item' => array(
                'id' => $this->shopSpecItem->getId(),
                'name' => $this->shopSpecItem->getName(),
            ),
        );
    }
}
