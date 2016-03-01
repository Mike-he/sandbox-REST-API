<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ShopOrderProduct.
 *
 * @ORM\Table(name="ShopOrderProductSpec")
 * @ORM\Entity
 */
class ShopOrderProductSpec
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main"})
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
     * @var ShopProductSpec
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopProductSpec")
     * @ORM\JoinColumn(name="specId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main"})
     */
    private $spec;

    /**
     * @var int
     *
     * @ORM\Column(name="productId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $productId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopOrderProduct")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id")
     * @Serializer\Groups({"main"})
     **/
    private $product;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopOrderProductSpecItem",
     *      mappedBy="spec",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="specId")
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $shopOrderProductSpecItems;

    /**
     * @var array
     */
    private $items;

    /**
     * @var string
     *
     * @ORM\Column(name="shopProductSpecInfo", type="text")
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $shopProductSpecInfo;

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
     * Set specId.
     *
     * @param int $specId
     *
     * @return ShopOrderProductSpec
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
     * Set spec.
     *
     * @param $spec
     *
     * @return ShopOrderProductSpec
     */
    public function setSpec($spec)
    {
        $this->spec = $spec;

        return $this;
    }

    /**
     * Get spec.
     *
     * @return ShopProductSpec
     */
    public function getSpec()
    {
        return $this->spec;
    }

    /**
     * Set productId.
     *
     * @param int $productId
     *
     * @return ShopOrderProductSpec
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
     * @param ShopOrderProduct $product
     *
     * @return ShopOrderProductSpec
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product.
     *
     * @return ShopOrderProduct
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set items.
     *
     * @param array $items
     *
     * @return ShopOrderProductSpec
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

    /**
     * Set shopProductSpecInfo.
     *
     * @param string $info
     *
     * @return ShopOrderProductSpec
     */
    public function setShopProductSpecInfo($info)
    {
        $this->shopProductSpecInfo = $info;

        return $this;
    }

    /**
     * Get shopProductSpecInfo.
     *
     * @return string
     */
    public function getShopProductSpecInfo()
    {
        return $this->shopProductSpecInfo;
    }

    /**
     * Set shopOrderProductSpecItems.
     *
     * @param array $shopOrderProductSpecItems
     *
     * @return ShopOrderProduct
     */
    public function setShopOrderProductSpecItems($shopOrderProductSpecItems)
    {
        $this->shopOrderProductSpecItems = $shopOrderProductSpecItems;

        return $this;
    }

    /**
     * Get shopOrderProductSpecItems.
     *
     * @return array
     */
    public function getShopOrderProductSpecItems()
    {
        return $this->shopOrderProductSpecItems;
    }
}
