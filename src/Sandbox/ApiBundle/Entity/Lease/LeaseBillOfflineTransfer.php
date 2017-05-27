<?php

namespace Sandbox\ApiBundle\Entity\Lease;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * LeaseBillOfflineTransfer.
 *
 * @ORM\Table(name="lease_bill_offline_transfer")
 * @ORM\Entity
 */
class LeaseBillOfflineTransfer
{
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PENDING = 'pending';
    const STATUS_RETURNED = 'returned';
    const STATUS_PAID = 'paid';
    const STATUS_VERIFY = 'verify';
    const STATUS_CLOSED = 'closed';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Lease\LeaseBill")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bill_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     *
     * @Serializer\Groups({"main"})
     */
    private $bill;

    /**
     * @var string
     *
     * @ORM\Column(name="account_name", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main" ,"client","lease_bill"})
     */
    private $accountName;

    /**
     * @var string
     *
     * @ORM\Column(name="account_no", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $accountNo;

    /**
     * @var string
     *
     * @ORM\Column(name="transfer_status", type="string", length=16)
     *
     * @Serializer\Groups({"main" ,"client","lease_bill"})
     */
    private $transferStatus = self::STATUS_UNPAID;

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
     * @Serializer\Groups({"main" ,"client","lease_bill"})
     */
    private $modificationDate;

    /**
     * @var array
     *
     * @Serializer\Groups({"main","client","lease_bill"})
     */
    private $attachments;

    /**
     * @ORM\OneToMany(targetEntity="Sandbox\ApiBundle\Entity\Lease\LeaseBillTransferAttachment", mappedBy="transfer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id", referencedColumnName="transfer_id")
     * })
     *
     * @Serializer\Groups({"main" ,"client","lease_bill"})
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
     * @return mixed
     */
    public function getBill()
    {
        return $this->bill;
    }

    /**
     * @param mixed $bill
     */
    public function setBill($bill)
    {
        $this->bill = $bill;
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
