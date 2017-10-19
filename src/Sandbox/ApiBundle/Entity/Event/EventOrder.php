<?php

namespace Sandbox\ApiBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * EventOrder.
 *
 * @ORM\Table(name="event_order")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Event\EventOrderRepository")
 */
class EventOrder
{
    const CLIENT_STATUS_IN_PROCESS = 'in_process';
    const CLIENT_STATUS_PASSED = 'passed';

    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PAID = 'paid';
    const STATUS_UNPAID = 'unpaid';
    const STATUS_COMPLETED = 'completed';

    const LETTER_HEAD = 'E';

    const CHANNEL_ACCOUNT = 'account';
    const EVENT_MAP = 'event';
    const ENTITY_PATH = 'Event\EventOrder';

    const PAYMENT_SUBJECT = 'SANDBOX3-活动报名支付';
    const PAYMENT_BODY = 'EVENT ORDER PAYMENT';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "client_event"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="eventId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $eventId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Event\Event")
     * @ORM\JoinColumn(name="eventId", referencedColumnName="id")
     *
     * @Serializer\Groups({"main", "client_event"})
     */
    private $event;

    /**
     * @var string
     *
     * @ORM\Column(name="orderNumber", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({"main", "client_event"})
     */
    private $orderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="payChannel", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"main", "client_event"})
     */
    private $payChannel;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "client_event"})
     */
    private $userId;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     *
     * @Serializer\Groups({"main", "client_event"})
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64, nullable=false)
     *
     * @Serializer\Groups({"main", "client_event"})
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="paymentDate", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "client_event"})
     */
    private $paymentDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cancelledDate", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "client_event"})
     */
    private $cancelledDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main", "client_event"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main", "client_event"})
     */
    private $modificationDate;

    /**
     * @var float
     *
     * @ORM\Column(name="serviceFee", type="float", options={"default": 0})
     */
    private $serviceFee = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "client_event"})
     */
    private $customerId;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin_event"})
     */
    private $user;

    /**
     * @var
     *
     * @Serializer\Groups({"client_event"})
     */
    private $registration;

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
     * Set eventId.
     *
     * @param int $eventId
     *
     * @return EventOrder
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId.
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * Set orderNumber.
     *
     * @param string $orderNumber
     *
     * @return EventOrder
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
     * @return EventOrder
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
     * @return EventOrder
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
     * @return EventOrder
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
     * Set status.
     *
     * @param string $status
     *
     * @return EventOrder
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
     * @return EventOrder
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
     * @return EventOrder
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
     * @return EventOrder
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
     * @return EventOrder
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
     * EventOrder constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
        $this->modificationDate = new \DateTime('now');
    }

    /**
     * @return mixed
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * @param mixed $registration
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;
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
