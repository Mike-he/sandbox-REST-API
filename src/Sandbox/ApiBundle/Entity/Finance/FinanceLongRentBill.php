<?php

namespace Sandbox\ApiBundle\Entity\Finance;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * FinanceLongRentBill.
 *
 * @ORM\Table(name="finance_long_rent_bill")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Finance\FinanceLongRentBillRepository")
 */
class FinanceLongRentBill
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

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
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     *
     * @Serializer\Groups({"main"})
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=15)
     *
     * @Serializer\Groups({"main"})
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var int
     *
     * @ORM\Column(name="company_id", type="integer")
     *
     * @Serializer\Groups({"main"})
     */
    private $companyId;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
     *
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modification_date", type="datetime")
     *
     * @Serializer\Groups({"main"})
     */
    private $modificationDate;

    /**
     * @var array
     */
    private $attachments;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Finance\FinanceBillAttachment",
     *      mappedBy="bill"
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="bill_id")
     */
    private $billAttachment;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Finance\FinanceBillInvoiceInfo",
     *      mappedBy="bill"
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="bill_id")
     */
    private $billInvoice;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * @param int $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
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
    public function getBillAttachment()
    {
        return $this->billAttachment;
    }

    /**
     * @param mixed $billAttachment
     */
    public function setBillAttachment($billAttachment)
    {
        $this->billAttachment = $billAttachment;
    }

    /**
     * @return mixed
     */
    public function getBillInvoice()
    {
        return $this->billInvoice;
    }

    /**
     * @param mixed $billInvoice
     */
    public function setBillInvoice($billInvoice)
    {
        $this->billInvoice = $billInvoice;
    }
}
