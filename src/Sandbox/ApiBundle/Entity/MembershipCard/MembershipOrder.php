<?php

namespace Sandbox\ApiBundle\Entity\MembershipCard;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * MembershipOrder.
 *
 * @ORM\Table(name="membership_order")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\MembershipCard\MembershipCardOrderRepository")
 */
class MembershipOrder
{
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PAID = 'paid';
    const STATUS_UNPAID = 'unpaid';
    const STATUS_COMPLETED = 'completed';

    const UNIT_MONTH = 'month';

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
     * @ORM\Column(name="order_number", type="string", length=64)
     */
    private $orderNumber;

    /**
     * @var MembershipCard
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\MembershipCard\MembershipCard")
     * @ORM\JoinColumn(name="card_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $card;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $user;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(name="valid_period", type="integer")
     */
    private $validPeriod;

    /**
     * @var string
     *
     * @ORM\Column(name="unit_price", type="string")
     */
    private $unitPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="specification", type="string", length=64)
     */
    private $specification;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime")
     */
    private $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64)
     */
    private $status = self::STATUS_COMPLETED;

    /**
     * @var string
     *
     * @ORM\Column(name="pay_channel", type="string", length=16, nullable=true)
     */
    private $payChannel;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="payment_date", type="datetime", nullable=true)
     */
    private $paymentDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cancelled_date", type="datetime", nullable=true)
     */
    private $cancelledDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="invoiced", type="boolean", options={"default": false})
     */
    private $invoiced = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="sales_invoice", type="boolean", options={"default": false})
     */
    private $salesInvoice = false;

    /**
     * @var float
     *
     * @ORM\Column(name="service_fee", type="float", precision=6, scale=3, options={"default": 0})
     */
    private $serviceFee = 0;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modification_date", type="datetime")
     */
    private $modificationDate;

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
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return MembershipCard
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param MembershipCard $card
     */
    public function setCard($card)
    {
        $this->card = $card;
    }

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getValidPeriod()
    {
        return $this->validPeriod;
    }

    /**
     * @param int $validPeriod
     */
    public function setValidPeriod($validPeriod)
    {
        $this->validPeriod = $validPeriod;
    }

    /**
     * @return string
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param string $unitPrice
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getPayChannel()
    {
        return $this->payChannel;
    }

    /**
     * @param string $payChannel
     */
    public function setPayChannel($payChannel)
    {
        $this->payChannel = $payChannel;
    }

    /**
     * @return \DateTime
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * @param \DateTime $paymentDate
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;
    }

    /**
     * @return \DateTime
     */
    public function getCancelledDate()
    {
        return $this->cancelledDate;
    }

    /**
     * @param \DateTime $cancelledDate
     */
    public function setCancelledDate($cancelledDate)
    {
        $this->cancelledDate = $cancelledDate;
    }

    /**
     * @return bool
     */
    public function isInvoiced()
    {
        return $this->invoiced;
    }

    /**
     * @param bool $invoiced
     */
    public function setInvoiced($invoiced)
    {
        $this->invoiced = $invoiced;
    }

    /**
     * @return bool
     */
    public function isSalesInvoice()
    {
        return $this->salesInvoice;
    }

    /**
     * @param bool $salesInvoice
     */
    public function setSalesInvoice($salesInvoice)
    {
        $this->salesInvoice = $salesInvoice;
    }

    /**
     * @return float
     */
    public function getServiceFee()
    {
        return $this->serviceFee;
    }

    /**
     * @param float $serviceFee
     */
    public function setServiceFee($serviceFee)
    {
        $this->serviceFee = $serviceFee;
    }

    /**
     * @return string
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * @param string $specification
     */
    public function setSpecification($specification)
    {
        $this->specification = $specification;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param \DateTime $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }
}
