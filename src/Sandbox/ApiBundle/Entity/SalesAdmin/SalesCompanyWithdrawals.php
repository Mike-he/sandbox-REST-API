<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * SalesCompanyWithdrawals
 *
 * @ORM\Table(name="sales_company_withdrawals")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\SalesAdmin\SalesCompanyWithdrawalsRepository")
 */
class SalesCompanyWithdrawals
{
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_FAILED = 'failed';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="sales_company_id", type="integer")
     */
    private $salesCompanyId;

    /**
     * @var SalesCompany
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany")
     * @ORM\JoinColumn(name="sales_company_id", referencedColumnName="id")
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $salesCompany;

    /**
     * @var string
     *
     * @ORM\Column(name="sales_company_name", type="string", length=128)
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $salesCompanyName;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_name", type="string", length=255)
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $bankAccountName;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_number", type="string", length=255)
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $bankAccountNumber;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(name="creation_date", type="datetime")
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $creationDate;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", precision=10, scale=2)
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $amount;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(name="modification_date", type="datetime")
     */
    private $modificationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16)
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="successTime", type="datetime", nullable=true)
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $successTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="failureTime", type="datetime", nullable=true)
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $failureTime;

    /**
     * @var integer
     *
     * @ORM\Column(name="salesAdminId", type="integer")
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $salesAdminId;

    /**
     * @var integer
     *
     * @ORM\Column(name="officialAdminId", type="integer", nullable=true)
     */
    private $officialAdminId;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set salesCompanyId
     *
     * @param integer $salesCompanyId
     * @return SalesCompanyWithdrawals
     */
    public function setSalesCompanyId($salesCompanyId)
    {
        $this->salesCompanyId = $salesCompanyId;

        return $this;
    }

    /**
     * Get salesCompanyId
     *
     * @return integer 
     */
    public function getSalesCompanyId()
    {
        return $this->salesCompanyId;
    }

    /**
     * @return SalesCompany
     */
    public function getSalesCompany()
    {
        return $this->salesCompany;
    }

    /**
     * @param SalesCompany $salesCompany
     */
    public function setSalesCompany($salesCompany)
    {
        $this->salesCompany = $salesCompany;
    }

    /**
     * Set salesCompanyName
     *
     * @param string $salesCompanyName
     * @return SalesCompanyWithdrawals
     */
    public function setSalesCompanyName($salesCompanyName)
    {
        $this->salesCompanyName = $salesCompanyName;

        return $this;
    }

    /**
     * Get salesCompanyName
     *
     * @return string 
     */
    public function getSalesCompanyName()
    {
        return $this->salesCompanyName;
    }

    /**
     * Set bankAccountName
     *
     * @param string $bankAccountName
     * @return SalesCompanyWithdrawals
     */
    public function setBankAccountName($bankAccountName)
    {
        $this->bankAccountName = $bankAccountName;

        return $this;
    }

    /**
     * Get bankAccountName
     *
     * @return string 
     */
    public function getBankAccountName()
    {
        return $this->bankAccountName;
    }

    /**
     * Set bankAccountNumber
     *
     * @param string $bankAccountNumber
     * @return SalesCompanyWithdrawals
     */
    public function setBankAccountNumber($bankAccountNumber)
    {
        $this->bankAccountNumber = $bankAccountNumber;

        return $this;
    }

    /**
     * Get bankAccountNumber
     *
     * @return string 
     */
    public function getBankAccountNumber()
    {
        return $this->bankAccountNumber;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return SalesCompanyWithdrawals
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime 
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return SalesCompanyWithdrawals
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set modificationDate
     *
     * @param \DateTime $modificationDate
     * @return SalesCompanyWithdrawals
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate
     *
     * @return \DateTime 
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return SalesCompanyWithdrawals
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set successTime
     *
     * @param \DateTime $successTime
     * @return SalesCompanyWithdrawals
     */
    public function setSuccessTime($successTime)
    {
        $this->successTime = $successTime;

        return $this;
    }

    /**
     * Get successTime
     *
     * @return \DateTime 
     */
    public function getSuccessTime()
    {
        return $this->successTime;
    }

    /**
     * Set failureTime
     *
     * @param \DateTime $failureTime
     * @return SalesCompanyWithdrawals
     */
    public function setFailureTime($failureTime)
    {
        $this->failureTime = $failureTime;

        return $this;
    }

    /**
     * Get failureTime
     *
     * @return \DateTime 
     */
    public function getFailureTime()
    {
        return $this->failureTime;
    }

    /**
     * Set salesAdminId
     *
     * @param integer $salesAdminId
     * @return SalesCompanyWithdrawals
     */
    public function setSalesAdminId($salesAdminId)
    {
        $this->salesAdminId = $salesAdminId;

        return $this;
    }

    /**
     * Get salesAdminId
     *
     * @return integer 
     */
    public function getSalesAdminId()
    {
        return $this->salesAdminId;
    }

    /**
     * Set officialAdminId
     *
     * @param integer $officialAdminId
     * @return SalesCompanyWithdrawals
     */
    public function setOfficialAdminId($officialAdminId)
    {
        $this->officialAdminId = $officialAdminId;

        return $this;
    }

    /**
     * Get officialAdminId
     *
     * @return integer 
     */
    public function getOfficialAdminId()
    {
        return $this->officialAdminId;
    }
}
