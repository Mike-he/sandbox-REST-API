<?php

namespace Sandbox\ApiBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderMap.
 *
 * @ORM\Table(name="OrderMap")
 * @ORM\Entity
 */
class OrderMap
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
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64)
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="orderNumber", type="string")
     */
    private $orderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="chargeId", type="string", length=128)
     */
    private $chargeId;

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
     * Set type.
     *
     * @param string $type
     *
     * @return OrderMap
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set orderNumber.
     *
     * @param string $orderNumber
     *
     * @return OrderMap
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * Get orderNumber.
     *
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * Set chargeId.
     *
     * @param string $chargeId
     *
     * @return OrderMap
     */
    public function setChargeId($chargeId)
    {
        $this->chargeId = $chargeId;

        return $this;
    }

    /**
     * Get chargeId.
     *
     * @return string
     */
    public function getChargeId()
    {
        return $this->chargeId;
    }
}
