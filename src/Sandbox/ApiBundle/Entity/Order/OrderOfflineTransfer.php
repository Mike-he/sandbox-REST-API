<?php

namespace Sandbox\ApiBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * OrderOfflineTransfer.
 *
 * @ORM\Table(name="OrderOfflineTransfer")
 * @ORM\Entity
 */
class OrderOfflineTransfer
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="orderId", type="integer")
     *
     * @Serializer\Groups({"main"})
     */
    private $orderId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Order\ProductOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="orderId", referencedColumnName="id")
     * })
     *
     * @Serializer\Groups({"main"})
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="transferNo", type="string", length=64)
     *
     * @Serializer\Groups({"main", "current_order", "admin_detail", "client_order", "admin_order" ,"client"})
     */
    private $transferNo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "current_order", "admin_detail", "client_order", "admin_order" ,"client"})
     */
    private $creationDate;

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
     * @return OrderOfflineTransfer
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
     * @return OrderOfflineTransfer
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
     * Set transferNo.
     *
     * @param string $transferNo
     *
     * @return OrderOfflineTransfer
     */
    public function setTransferNo($transferNo)
    {
        $this->transferNo = $transferNo;

        return $this;
    }

    /**
     * Get transferNo.
     *
     * @return string
     */
    public function getTransferNo()
    {
        return $this->transferNo;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return OrderOfflineTransfer
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
    }
}
