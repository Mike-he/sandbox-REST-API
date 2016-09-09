<?php

namespace Sandbox\ApiBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductOrderRecord.
 *
 * @ORM\Table(name="product_order_record")
 * @ORM\Entity
 */
class ProductOrderRecord
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="orderId", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var ProductOrder
     *
     * @ORM\OneToOne(targetEntity="ProductOrder")
     * @ORM\JoinColumn(name="orderId", referencedColumnName="id")
     */
    private $order;

    /**
     * @var int
     *
     * @ORM\Column(name="cityId", type="integer", nullable=false)
     */
    private $cityId;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer", nullable=false)
     */
    private $buildingId;

    /**
     * @var string
     *
     * @ORM\Column(name="roomType", type="string", nullable=false)
     */
    private $roomType;

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
     * @return ProductOrderRecord
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
     * @param ProductOrder $order
     *
     * @return ProductOrderRecord
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order.
     *
     * @return ProductOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set cityId.
     *
     * @param int $cityId
     *
     * @return ProductOrderRecord
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * Get cityId.
     *
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * Set buildingId.
     *
     * @param int $buildingId
     *
     * @return ProductOrderRecord
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;

        return $this;
    }

    /**
     * Get buildingId.
     *
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * Set roomType.
     *
     * @param int $roomType
     *
     * @return ProductOrderRecord
     */
    public function setRoomType($roomType)
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * Get roomType.
     *
     * @return int
     */
    public function getRoomType()
    {
        return $this->roomType;
    }
}
