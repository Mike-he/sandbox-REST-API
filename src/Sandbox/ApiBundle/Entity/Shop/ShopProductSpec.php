<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use JMS\Serializer\Annotation as Serializer;

/**
 * ShopProductSpec.
 *
 * @ORM\Table(name="ShopProductSpec")
 * @ORM\Entity
 */
class ShopProductSpec implements JsonSerializable
{
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
     * @ORM\Column(name="productId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $productId;

    /**
     * @var ShopProduct
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopProduct")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main"})
     */
    private $product;

    /**
     * @var ShopProductSpecItem
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopProductSpecItem",
     *      mappedBy="productSpec",
     *      cascade={"persist"}
     * )
     *
     * @ORM\JoinColumn(name="id", referencedColumnName="productSpecId")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $specItems;

    /**
     * @var bool
     *
     * @ORM\Column(name="optional", type="boolean")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $optional = false;

    /**
     * @var int
     *
     * @ORM\Column(name="shopSpecId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $shopSpecId;

    /**
     * @var ShopSpec
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopSpec")
     *
     * @ORM\JoinColumn(name="shopSpecId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $shopSpec;

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
     * Set optional.
     *
     * @param bool $optional
     *
     * @return ShopProductSpec
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
     * @return ShopProductSpecItem
     */
    public function getSpecItems()
    {
        return $this->specItems;
    }

    /**
     * @param ShopSpecItem $item
     *
     * @return ShopProductSpec
     */
    public function setSpecItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * set shopSpecId.
     *
     * @param int $shopSpecId
     *
     * @return ShopProductSpec
     */
    public function setShopSpecId($shopSpecId)
    {
        $this->shopSpecId = $shopSpecId;

        return $this;
    }

    /**
     * Get shopSpecId.
     *
     * @return int
     */
    public function getShopSpecId()
    {
        return $this->shopSpecId;
    }

    /**
     * set productId.
     *
     * @param int $productId
     *
     * @return ShopProductSpec
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get productId.
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set product.
     *
     * @param ShopProduct $product
     *
     * @return ShopProductSpec
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product.
     *
     * @return ShopProduct
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set shopSpec.
     *
     * @param $spec
     *
     * @return ShopProductSpec
     */
    public function setShopSpec($spec)
    {
        $this->shopSpec = $spec;

        return $this;
    }

    /**
     * Get shopSpec.
     *
     * @return ShopSpec
     */
    public function getShopSpec()
    {
        return $this->shopSpec;
    }

    /**
     * Set items.
     *
     * @param array $items
     *
     * @return ShopProductSpec
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
        );
    }
}
