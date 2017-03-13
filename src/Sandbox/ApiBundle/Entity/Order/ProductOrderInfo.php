<?php

namespace Sandbox\ApiBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductOrder.
 *
 * @ORM\Table(name="product_order_info")
 * @ORM\Entity
 */
class ProductOrderInfo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Order\ProductOrder")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="product_info", type="text")
     */
    private $productInfo;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getProductInfo()
    {
        return $this->productInfo;
    }

    /**
     * @param string $productInfo
     */
    public function setProductInfo($productInfo)
    {
        $this->productInfo = $productInfo;
    }
}
