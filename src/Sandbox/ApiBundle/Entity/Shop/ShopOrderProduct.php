<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ShopOrderProduct.
 *
 * @ORM\Table(name="ShopOrderProduct")
 * @ORM\Entity
 */
class ShopOrderProduct
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
     * @ORM\Column(name="orderId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $orderId;

    /**
     * @var ShopOrder
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopOrder")
     * @ORM\JoinColumn(name="orderId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main"})
     */
    private $order;

    /**
     * @var int
     *
     * @ORM\Column(name="productId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $productId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopProduct")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id")
     * @Serializer\Groups({"main"})
     **/
    private $product;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopOrderProductSpec",
     *      mappedBy="product",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="productId")
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $shopOrderProductSpecs;

    /**
     * @var array
     */
    private $specs;

    /**
     * @var string
     *
     * @ORM\Column(name="shopProductInfo", type="text")
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $shopProductInfo;

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
     * Set orderId.
     *
     * @param int $orderId
     *
     * @return ShopOrderProduct
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get orderId.
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set order.
     *
     * @param ShopOrder $order
     *
     * @return ShopOrderProduct
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order.
     *
     * @return ShopOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set productId.
     *
     * @param int $productId
     *
     * @return ShopOrderProduct
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
     * @param $product
     *
     * @return ShopOrderProduct
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
     * Set specs.
     *
     * @param array $specs
     *
     * @return ShopOrderProduct
     */
    public function setSpecs($specs)
    {
        $this->specs = $specs;

        return $this;
    }

    /**
     * Get specs.
     *
     * @return array
     */
    public function getSpecs()
    {
        return $this->specs;
    }

    /**
     * Set shopProductInfo.
     *
     * @param string $info
     *
     * @return ShopOrderProduct
     */
    public function setShopProductInfo($info)
    {
        $this->shopProductInfo = $info;

        return $this;
    }

    /**
     * Get shopProductInfo.
     *
     * @return string
     */
    public function getShopProductInfo()
    {
        return $this->shopProductInfo;
    }

    /**
     * Set shopOrderProductSpecs.
     *
     * @param array $shopOrderProductSpecs
     *
     * @return ShopOrderProduct
     */
    public function setShopOrderProductSpecs($shopOrderProductSpecs)
    {
        $this->shopOrderProductSpecs = $shopOrderProductSpecs;

        return $this;
    }

    /**
     * Get shopOrderProductSpecs.
     *
     * @return array
     */
    public function getShopOrderProductSpecs()
    {
        return $this->shopOrderProductSpecs;
    }
}
