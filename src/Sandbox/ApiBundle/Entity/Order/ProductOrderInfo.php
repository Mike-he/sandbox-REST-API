<?php

namespace Sandbox\ApiBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

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
     * @var int
     *
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var string
     *
     * @ORM\Column(name="product_info", type="text")
     *
     * @Serializer\Groups({"admin_detail"})
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
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
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
