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
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PENDING = 'pending';
    const STATUS_RETURNED = 'returned';
    const STATUS_PAID = 'paid';
    const STATUS_VERIFY = 'verify';
    const STATUS_REJECT_REFUND = 'reject_refund';
    const STATUS_ACCEPT_REFUND = 'accept_refund';

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
     * @ORM\Column(name="accountName", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main", "current_order", "admin_detail", "client_order", "admin_order" ,"client"})
     */
    private $accountName;

    /**
     * @var string
     *
     * @ORM\Column(name="accountNo", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main", "current_order", "admin_detail", "client_order", "admin_order" ,"client"})
     */
    private $accountNo;

    /**
     * @var string
     *
     * @ORM\Column(name="transferStatus", type="string", length=16)
     *
     * @Serializer\Groups({"main", "current_order", "admin_detail", "client_order", "admin_order" ,"client"})
     */
    private $transferStatus = self::STATUS_UNPAID;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "current_order", "admin_detail", "client_order", "admin_order" ,"client"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "current_order", "admin_detail", "client_order", "admin_order" ,"client"})
     */
    private $modificationDate;

    /**
     * @ORM\OneToMany(targetEntity="Sandbox\ApiBundle\Entity\Order\TransferAttachment", mappedBy="transfer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id", referencedColumnName="transferId")
     * })
     *
     * @Serializer\Groups({"main", "current_order", "admin_detail", "client_order", "admin_order" ,"client"})
     */
    private $transferAttachments;

    /**
     * @var array
     *
     * @Serializer\Groups({"main"})
     */
    private $attachments;

    /**
     * Set transferAttachments.
     *
     * @param array $transferAttachments
     *
     * @return OrderOfflineTransfer
     */
    public function setTransferAttachments($transferAttachments)
    {
        $this->transferAttachments = $transferAttachments;

        return $this;
    }

    /**
     * Get transferAttachments.
     *
     * @return array
     */
    public function getTransferAttachments()
    {
        return $this->transferAttachments;
    }

    /**
     * Set attachment.
     *
     * @param array $attachment
     *
     * @return OrderOfflineTransfer
     */
    public function setAttachments($attachment)
    {
        $this->attachments = $attachment;

        return $this;
    }

    /**
     * Get attachment.
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return OrderOfflineTransfer
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
     * Set accountName.
     *
     * @param string $name
     *
     * @return OrderOfflineTransfer
     */
    public function setAccountName($name)
    {
        $this->accountName = $name;

        return $this;
    }

    /**
     * Get accountName.
     *
     * @return string
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * Set transferStatus.
     *
     * @param string $status
     *
     * @return OrderOfflineTransfer
     */
    public function setTransferStatus($status)
    {
        $this->transferStatus = $status;

        return $this;
    }

    /**
     * Get transferStatus.
     *
     * @return string
     */
    public function getTransferStatus()
    {
        return $this->transferStatus;
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
     * Set accountNo.
     *
     * @param string $accountNo
     *
     * @return OrderOfflineTransfer
     */
    public function setAccountNo($accountNo)
    {
        $this->accountNo = $accountNo;

        return $this;
    }

    /**
     * Get accountNo.
     *
     * @return string
     */
    public function getAccountNo()
    {
        return $this->accountNo;
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
        $this->setModificationDate($now);
    }
}
