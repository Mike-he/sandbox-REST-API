<?php

namespace Sandbox\ApiBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * TopUpOrder.
 *
 * @ORM\Table(name="top_up_order")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Order\TopUpOrderRepository")
 */
class TopUpOrder
{
    const TOP_UP_MAP = 'topup';
    const PAYMENT_SUBJECT = 'SANDBOX3-会员余额充值';
    const PAYMENT_BODY = 'TOPUP ORDER';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "admin_order", "client_order"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="orderNumber", type="string", length=128)
     * @Serializer\Groups({"main", "admin_order", "client_order"})
     */
    private $orderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="payChannel", type="string", length=16, nullable=true)
     * @Serializer\Groups({"main", "admin_order", "client_order"})
     */
    private $payChannel;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer")
     * @Serializer\Groups({"main", "admin_order", "client_order"})
     */
    private $userId;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     * @Serializer\Groups({"main", "admin_order", "client_order"})
     */
    private $price;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     * @Serializer\Groups({"main", "admin_order", "client_order"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
     * @Serializer\Groups({"main", "admin_order", "client_order"})
     */
    private $modificationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="paymentDate", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_order", "client_order"})
     */
    private $paymentDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="refund_to_account", type="boolean", options={"default": 0})
     *
     * @Serializer\Groups({"main", "admin_order", "client_order"})
     */
    private $refundToAccount = false;

    /**
     * @var string
     *
     * @ORM\Column(name="refund_number", type="string", length=128, nullable=true)
     * @Serializer\Groups({"main", "admin_order", "client_order"})
     */
    private $refundNumber;

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
     * Set orderNumber.
     *
     * @param string $orderNumber
     *
     * @return TopUpOrder
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return TopUpOrder
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set price.
     *
     * @param float $price
     *
     * @return TopUpOrder
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return TopUpOrder
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

    /**
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return TopUpOrder
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set payChannel.
     *
     * @param string $payChannel
     *
     * @return TopUpOrder
     */
    public function setPayChannel($payChannel)
    {
        $this->payChannel = $payChannel;

        return $this;
    }

    /**
     * Get payChannel.
     *
     * @return string
     */
    public function getPayChannel()
    {
        return $this->payChannel;
    }

    /**
     * Set paymentDate.
     *
     * @param \DateTime $paymentDate
     *
     * @return TopUpOrder
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    /**
     * Get paymentDate.
     *
     * @return \DateTime
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * @return bool
     */
    public function isRefundToAccount()
    {
        return $this->refundToAccount;
    }

    /**
     * @param bool $refundToAccount
     */
    public function setRefundToAccount($refundToAccount)
    {
        $this->refundToAccount = $refundToAccount;
    }

    /**
     * @return string
     */
    public function getRefundNumber()
    {
        return $this->refundNumber;
    }

    /**
     * @param string $refundNumber
     */
    public function setRefundNumber($refundNumber)
    {
        $this->refundNumber = $refundNumber;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
        $this->setPaymentDate($now);
    }
}
