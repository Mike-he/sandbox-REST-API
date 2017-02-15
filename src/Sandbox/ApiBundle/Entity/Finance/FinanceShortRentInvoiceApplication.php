<?php

namespace Sandbox\ApiBundle\Entity\Finance;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;
use JMS\Serializer\Annotation as Serializer;

/**
 * FinanceShortRentInvoiceApplication.
 *
 * @ORM\Table(name="finance_short_rent_invoice_applications")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Finance\FinanceShortRentInvoiceApplicationRepository")
 */
class FinanceShortRentInvoiceApplication
{
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_REVOKED = 'revoked';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "sales_admin_list", "sales_admin_detail"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="company_id", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $companyId;

    /**
     * @var SalesCompany
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $company;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     * @Serializer\Groups({"main", "sales_admin_list", "sales_admin_detail"})
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_no", type="text")
     * @Serializer\Groups({"main", "sales_admin_list", "sales_admin_detail"})
     */
    private $invoiceNo;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16)
     * @Serializer\Groups({"main", "sales_admin_list", "sales_admin_detail"})
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
     * @Serializer\Groups({"main", "sales_admin_list", "sales_admin_detail"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="confirm_date", type="datetime", nullable=true)
     * @Serializer\Groups({"main", "sales_admin_detail"})
     */
    private $confirmDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="revoke_date", type="datetime", nullable=true)
     * @Serializer\Groups({"main", "sales_admin_detail"})
     */
    private $revokeDate;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_ids", type="text")
     */
    private $invoiceIds;

    /**
     * @var array
     * @Serializer\Groups({"main", "sales_admin_detail"})
     */
    private $invoices;

    /**
     * @var int
     *
     * @ORM\Column(name="official_profile_id", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $officialProfileId;

    /**
     * @var FinanceOfficialInvoiceProfile
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Finance\FinanceOfficialInvoiceProfile")
     * @ORM\JoinColumn(name="official_profile_id", referencedColumnName="id")
     * @Serializer\Groups({"main", "sales_admin_detail"})
     */
    private $officialProfile;

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
     * @return array
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * @param array $invoices
     */
    public function setInvoices($invoices)
    {
        $this->invoices = $invoices;
    }

    /**
     * @return FinanceOfficialInvoiceProfile
     */
    public function getOfficialProfile()
    {
        return $this->officialProfile;
    }

    /**
     * @param FinanceOfficialInvoiceProfile $officialProfile
     */
    public function setOfficialProfile($officialProfile)
    {
        $this->officialProfile = $officialProfile;
    }

    /**
     * @return int
     */
    public function getOfficialProfileId()
    {
        return $this->officialProfileId;
    }

    /**
     * @param int $officialProfileId
     */
    public function setOfficialProfileId($officialProfileId)
    {
        $this->officialProfileId = $officialProfileId;
    }

    /**
     * @return SalesCompany
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param SalesCompany $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * Set companyId.
     *
     * @param int $companyId
     *
     * @return FinanceShortRentInvoiceApplication
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
     * Set amount.
     *
     * @param float $amount
     *
     * @return FinanceShortRentInvoiceApplication
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
     * Set invoiceNo.
     *
     * @param string $invoiceNo
     *
     * @return FinanceShortRentInvoiceApplication
     */
    public function setInvoiceNo($invoiceNo)
    {
        $this->invoiceNo = $invoiceNo;

        return $this;
    }

    /**
     * Get invoiceNo.
     *
     * @return string
     */
    public function getInvoiceNo()
    {
        return $this->invoiceNo;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return FinanceShortRentInvoiceApplication
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
     * @return FinanceShortRentInvoiceApplication
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
     * Set confirmDate.
     *
     * @param \DateTime $confirmDate
     *
     * @return FinanceShortRentInvoiceApplication
     */
    public function setConfirmDate($confirmDate)
    {
        $this->confirmDate = $confirmDate;

        return $this;
    }

    /**
     * Get confirmDate.
     *
     * @return \DateTime
     */
    public function getConfirmDate()
    {
        return $this->confirmDate;
    }

    /**
     * Set revokeDate.
     *
     * @param \DateTime $revokeDate
     *
     * @return FinanceShortRentInvoiceApplication
     */
    public function setRevokeDate($revokeDate)
    {
        $this->revokeDate = $revokeDate;

        return $this;
    }

    /**
     * Get revokeDate.
     *
     * @return \DateTime
     */
    public function getRevokeDate()
    {
        return $this->revokeDate;
    }

    /**
     * Set invoiceIds.
     *
     * @param string $invoiceIds
     *
     * @return FinanceShortRentInvoiceApplication
     */
    public function setInvoiceIds($invoiceIds)
    {
        $this->invoiceIds = $invoiceIds;

        return $this;
    }

    /**
     * Get invoiceIds.
     *
     * @return string
     */
    public function getInvoiceIds()
    {
        return $this->invoiceIds;
    }
}
