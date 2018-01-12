<?php

namespace Sandbox\ApiBundle\Entity\Service;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ServiceOrder.
 *
 * @ORM\Table(name="service_order")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Service\ServiceOrderRepository")
 */
class ServiceOrder
{
    const CLIENT_STATUS_PENDING = 'pending';
    const CLIENT_STATUS_IN_PROCESS = 'in_process';
    const CLIENT_STATUS_PASSED = 'passed';

    const STATUS_PAID = 'paid';
    const STATUS_UNPAID = 'unpaid';
    const STATUS_COMPLETED = 'completed';

    const LETTER_HEAD = 'F';

    const CHANNEL_ACCOUNT = 'account';
    const Service_MAP = 'service';
    const ENTITY_PATH = 'Service\ServiceOrder';

    const PAYMENT_SUBJECT = 'SANDBOX3-服务购买支付';
    const PAYMENT_BODY = 'SERVICE ORDER PAYMENT';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "client_service"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="service_id", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $serviceId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Service\Service")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     *
     * @Serializer\Groups({"main", "client_service"})
     */
    private $service;

    /**
     * @var string
     *
     * @ORM\Column(name="order_number", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({"main", "client_service"})
     */
    private $orderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="pay_channel", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"main", "client_service"})
     */
    private $payChannel;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "client_service"})
     */
    private $userId;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     *
     * @Serializer\Groups({"main", "client_service"})
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(name="company_id", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "client_service"})
     */
    private $companyId;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64, nullable=false)
     *
     * @Serializer\Groups({"main", "client_service"})
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="payment_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "client_service"})
     */
    private $paymentDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cancelled_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "client_service"})
     */
    private $cancelledDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main", "client_service"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modification_date", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main", "client_service"})
     */
    private $modificationDate;

    /**
     * @var float
     *
     * @ORM\Column(name="service_fee", type="float", options={"default": 0})
     */
    private $serviceFee = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "client_service"})
     */
    private $customerId;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin_service"})
     */
    private $user;

    /**
     * @var bool
     *
     * @ORM\Column(name="invoiced", type="boolean", nullable=false)
     */
    private $invoiced = false;

    /**
     * @var object
     */
    private $purchaseForm;

    /**
     * @Serializer\Groups({"main", "client_service"})
     */
    private $companyName;


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
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param array $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

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
     * Set serviceId.
     *
     * @param int $serviceId
     *
     * @return ServiceOrder
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Get serviceId.
     *
     * @return int
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * Set orderNumber.
     *
     * @param string $orderNumber
     *
     * @return ServiceOrder
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
     * Set payChannel.
     *
     * @param string $payChannel
     *
     * @return ServiceOrder
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return ServiceOrder
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
     * @return ServiceOrder
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
     * Set userId.
     *
     * @param int $companyId
     *
     * @return ServiceOrder
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get companyIdId.
     *
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return ServiceOrder
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set paymentDate.
     *
     * @param \DateTime $paymentDate
     *
     * @return ServiceOrder
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
     * Set cancelledDate.
     *
     * @param \DateTime $cancelledDate
     *
     * @return ServiceOrder
     */
    public function setCancelledDate($cancelledDate)
    {
        $this->cancelledDate = $cancelledDate;

        return $this;
    }

    /**
     * Get cancelledDate.
     *
     * @return \DateTime
     */
    public function getCancelledDate()
    {
        return $this->cancelledDate;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return ServiceOrder
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
     * @return ServiceOrder
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
     * ServiceOrder constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
        $this->modificationDate = new \DateTime('now');
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
     * @return mixed
     */
    public function getPurchaseForm()
    {
        return $this->purchaseForm;
    }

    /**
     * @param mixed $purchaseForm
     */
    public function setPurchaseForm($purchaseForm)
    {
        $this->purchaseForm = $purchaseForm;
    }

    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param mixed $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }
}
