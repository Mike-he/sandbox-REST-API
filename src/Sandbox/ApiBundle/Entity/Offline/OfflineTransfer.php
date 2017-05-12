<?php

namespace Sandbox\ApiBundle\Entity\Offline;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * OfflineTransfer.
 *
 * @ORM\Table(name="offline_transfer")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Offline\OfflineTransferRepository")
 */
class OfflineTransfer
{
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PENDING = 'pending';
    const STATUS_RETURNED = 'returned';
    const STATUS_PAID = 'paid';
    const STATUS_VERIFY = 'verify';
    const STATUS_REJECT_REFUND = 'reject_refund';
    const STATUS_ACCEPT_REFUND = 'accept_refund';
    const STATUS_CLOSED = 'closed';

    const TYPE_TOPUP = 'topup';

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
     * @var string
     *
     * @ORM\Column(name="order_number", type="string", length=128)
     *
     * @Serializer\Groups({"main"})
     */
    private $orderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=32)
     *
     * @Serializer\Groups({"main"})
     */
    private $type;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     * @Serializer\Groups({"main"})
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="account_name", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $accountName;

    /**
     * @var string
     *
     * @ORM\Column(name="account_no", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $accountNo;

    /**
     * @var string
     *
     * @ORM\Column(name="transfer_status", type="string", length=16)
     *
     * @Serializer\Groups({"main"})
     */
    private $transferStatus = self::STATUS_UNPAID;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creationDate", type="datetime")
     *
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modificationDate", type="datetime")
     *
     * @Serializer\Groups({"main"})
     */
    private $modificationDate;

    /**
     * @var array
     *
     * @Serializer\Groups({"main"})
     */
    private $attachments;

    /**
     * @ORM\OneToMany(targetEntity="Sandbox\ApiBundle\Entity\Offline\OfflineTransferAttachment", mappedBy="transfer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id", referencedColumnName="transferId")
     * })
     *
     * @Serializer\Groups({"main"})
     */
    private $transferAttachments;

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
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param int $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return string
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * @param string $accountName
     */
    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;
    }

    /**
     * @return string
     */
    public function getAccountNo()
    {
        return $this->accountNo;
    }

    /**
     * @param string $accountNo
     */
    public function setAccountNo($accountNo)
    {
        $this->accountNo = $accountNo;
    }

    /**
     * @return string
     */
    public function getTransferStatus()
    {
        return $this->transferStatus;
    }

    /**
     * @param string $transferStatus
     */
    public function setTransferStatus($transferStatus)
    {
        $this->transferStatus = $transferStatus;
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
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param array $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
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
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getTransferAttachments()
    {
        return $this->transferAttachments;
    }

    /**
     * @param mixed $transferAttachments
     */
    public function setTransferAttachments($transferAttachments)
    {
        $this->transferAttachments = $transferAttachments;
    }
}
