<?php

namespace Sandbox\ApiBundle\Entity\Lease;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="lease_bill")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Lease\LeaseBillRepository")
 */
class LeaseBill
{
    const STATUS_PENDING = 'pending';
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_VERIFY = 'verify';

    const TYPE_LEASE = 'lease';
    const TYPE_OTHER = 'other';

    const ORDER_METHOD_BACKEND = 'backend';
    const ORDER_METHOD_AUTO = 'auto';

    const CHANNEL_ACCOUNT = 'account';
    const CHANNEL_ALIPAY = 'alipay';
    const CHANNEL_UNIONPAY = 'upacp';
    const CHANNEL_WECHAT = 'wx';
    const CHANNEL_FOREIGN_CREDIT = 'cnp_f';
    const CHANNEL_UNION_CREDIT = 'cnp_u';
    const CHANNEL_WECHAT_PUB = 'wx_pub';
    const CHANNEL_OFFLINE = 'offline';
    const CHANNEL_SALES_OFFLINE = 'sales_offline';

    const PAYMENT_SUBJECT = 'SANDBOX3-支付账单';
    const PAYMENT_BODY = 'PAY THE BILLS';

    const LEASE_BILL_LETTER_HEAD = 'B';

    const BILL_MAP = 'lease_bill';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main","lease_bill"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="serial_number", type="string", length=50, nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $serialNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=40, nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $endDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modification_date", type="datetime")
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $modificationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2, nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=15)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=10, nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="lease_id", type="integer")
     */
    private $leaseId;

    /**
     * @var Lease
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Lease\Lease")
     * @ORM\JoinColumn(name="lease_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $lease;

    /**
     * @var float
     *
     * @ORM\Column(name="revised_amount", type="decimal", precision=10, scale=2, nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $revisedAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="revision_note", type="string", length=225, nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $revisionNote;

    /**
     * @var User
     *
     * @ORM\Column(name="reviser", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main","lease_bill"})
     */
    private $reviser;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="payment_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $paymentDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="send_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $sendDate;

    /**
     * @var User
     *
     * @ORM\Column(name="sender", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main","lease_bill"})
     */
    private $sender;

    /**
     * @var User
     *
     * @ORM\Column(name="drawee", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $drawee;

    /**
     * @var integer
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $customerId;

    /**
     * @var int
     *
     * @ORM\Column(name="payment_user_id", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $paymentUserId;

    /**
     * @var string
     *
     * @ORM\Column(name="pay_channel", type="string", length=16, nullable=true)
     *
     * @Serializer\Groups({"main","client", "lease_bill"})
     */
    private $payChannel;

    /**
     * @var string
     *
     * @ORM\Column(name="order_method", type="string", length=15)
     *
     * @Serializer\Groups({"main","lease_bill"})
     */
    private $orderMethod = self::ORDER_METHOD_BACKEND;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Lease\LeaseBillOfflineTransfer",
     *      mappedBy="bill"
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="bill_id")
     *
     * @ORM\OrderBy({"id" = "DESC"})
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $transfer;

    /**
     * @var string
     *
     * @ORM\Column(name="remark", type="text", nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $remark;

    /**
     * @var bool
     *
     * @ORM\Column(name="sales_invoice", type="boolean", nullable=false)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $salesInvoice = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="invoiced", type="boolean", nullable=false)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $invoiced = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * @param string $serialNumber
     */
    public function setSerialNumber($serialNumber)
    {
        $this->serialNumber = $serialNumber;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getLeaseId()
    {
        return $this->leaseId;
    }

    /**
     * @param int $leaseId
     */
    public function setLeaseId($leaseId)
    {
        $this->leaseId = $leaseId;
    }

    /**
     * @return Lease
     */
    public function getLease()
    {
        return $this->lease;
    }

    /**
     * @param Lease $lease
     */
    public function setLease($lease)
    {
        $this->lease = $lease;
    }

    /**
     * @return float
     */
    public function getRevisedAmount()
    {
        return $this->revisedAmount;
    }

    /**
     * @param float $revisedAmount
     */
    public function setRevisedAmount($revisedAmount)
    {
        $this->revisedAmount = $revisedAmount;
    }

    /**
     * @return string
     */
    public function getRevisionNote()
    {
        return $this->revisionNote;
    }

    /**
     * @param string $revisionNote
     */
    public function setRevisionNote($revisionNote)
    {
        $this->revisionNote = $revisionNote;
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
    public function getSendDate()
    {
        return $this->sendDate;
    }

    /**
     * @param \DateTime $sendDate
     */
    public function setSendDate($sendDate)
    {
        $this->sendDate = $sendDate;
    }

    /**
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param User $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return User
     */
    public function getDrawee()
    {
        return $this->drawee;
    }

    /**
     * @param User $drawee
     */
    public function setDrawee($drawee)
    {
        $this->drawee = $drawee;
    }

    /**
     * @return int
     */
    public function getPaymentUserId()
    {
        return $this->paymentUserId;
    }

    /**
     * @param int $paymentUserId
     */
    public function setPaymentUserId($paymentUserId)
    {
        $this->paymentUserId = $paymentUserId;
    }

    /**
     * @return string
     */
    public function getOrderMethod()
    {
        return $this->orderMethod;
    }

    /**
     * @param string $orderMethod
     */
    public function setOrderMethod($orderMethod)
    {
        $this->orderMethod = $orderMethod;
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
     * @return User
     */
    public function getReviser()
    {
        return $this->reviser;
    }

    /**
     * @param User $reviser
     */
    public function setReviser($reviser)
    {
        $this->reviser = $reviser;
    }

    /**
     * @return mixed
     */
    public function getTransfer()
    {
        return $this->transfer;
    }

    /**
     * @param mixed $transfer
     */
    public function setTransfer($transfer)
    {
        $this->transfer = $transfer;
    }

    /**
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
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
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param int $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }
}
