<?php

namespace Sandbox\ApiBundle\Entity\Finance;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * FinanceShortRentInvoice.
 *
 * @ORM\Table(name="finance_short_rent_invoices")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Finance\FinanceShortRentInvoiceRepository")
 */
class FinanceShortRentInvoice
{
    const STATUS_PENDING = 'pending';
    const STATUS_INCOMPLETE = 'incomplete';
    const STATUS_COMPLETED = 'completed';
    const DETAIL_APPLICATION = 'application';
    const DETAIL_SERVICE_FEE = 'serviceFee';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "sales_admin_detail"})
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     * @Serializer\Groups({"main", "sales_admin_detail"})
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16)
     * @Serializer\Groups({"main", "sales_admin_detail"})
     */
    private $status = self::STATUS_INCOMPLETE;

    /**
     * @var \DateTime
     * @ORM\Column(name="creationDate", type="datetime")
     * @Serializer\Groups({"main", "sales_admin_detail"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modificationDate", type="datetime")
     */
    private $modificationDate;

    /**
     * @var int
     *
     * @ORM\Column(name="companyId", type="integer")
     */
    private $companyId;

    /**
     * @var string
     *
     * @ORM\Column(name="detail", type="string")
     */
    private $detail;

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
     * Set amount.
     *
     * @param float $amount
     *
     * @return FinanceShortRentInvoice
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return FinanceShortRentInvoice
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
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return FinanceShortRentInvoice
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
     * @return FinanceShortRentInvoice
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
     * Set companyId.
     *
     * @param int $companyId
     *
     * @return FinanceShortRentInvoice
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get companyId.
     *
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * Set detail.
     *
     * @param string $detail
     *
     * @return FinanceShortRentInvoice
     */
    public function setDetail($detail)
    {
        $this->status = $detail;

        return $this;
    }

    /**
     * Get detail.
     *
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }
}
