<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SalesCompanyProfileAccount.
 *
 * @ORM\Table(name="sales_company_profile_account")
 * @ORM\Entity
 */
class SalesCompanyProfileAccount
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="sales_company_id", type="integer")
     */
    private $salesCompanyId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="sales_company_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $salesCompany;

    /**
     * @var string
     *
     * @ORM\Column(name="sales_company_name", type="string", length=128)
     */
    private $salesCompanyName;

    /**
     * @var string
     *
     * @ORM\Column(name="business_scope", type="string", length=1024)
     */
    private $businessScope;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_name", type="string", length=255)
     */
    private $bankAccountName;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_number", type="string", length=255)
     */
    private $bankAccountNumber;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modification_date", type="datetime")
     */
    private $modificationDate;

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
     * Set salesCompanyId.
     *
     * @param int $salesCompanyId
     *
     * @return SalesCompanyProfileAccount
     */
    public function setSalesCompanyId($salesCompanyId)
    {
        $this->salesCompanyId = $salesCompanyId;

        return $this;
    }

    /**
     * Get salesCompanyId.
     *
     * @return int
     */
    public function getSalesCompanyId()
    {
        return $this->salesCompanyId;
    }

    /**
     * @return mixed
     */
    public function getSalesCompany()
    {
        return $this->salesCompany;
    }

    /**
     * @param mixed $salesCompany
     */
    public function setSalesCompany($salesCompany)
    {
        $this->salesCompany = $salesCompany;
    }

    /**
     * Set salesCompanyName.
     *
     * @param string $salesCompanyName
     *
     * @return SalesCompanyProfileAccount
     */
    public function setSalesCompanyName($salesCompanyName)
    {
        $this->salesCompanyName = $salesCompanyName;

        return $this;
    }

    /**
     * Get salesCompanyName.
     *
     * @return string
     */
    public function getSalesCompanyName()
    {
        return $this->salesCompanyName;
    }

    /**
     * Set businessScope.
     *
     * @param string $businessScope
     *
     * @return SalesCompanyProfileAccount
     */
    public function setBusinessScope($businessScope)
    {
        $this->businessScope = $businessScope;

        return $this;
    }

    /**
     * Get businessScope.
     *
     * @return string
     */
    public function getBusinessScope()
    {
        return $this->businessScope;
    }

    /**
     * Set bankAccountName.
     *
     * @param string $bankAccountName
     *
     * @return SalesCompanyProfileAccount
     */
    public function setBankAccountName($bankAccountName)
    {
        $this->bankAccountName = $bankAccountName;

        return $this;
    }

    /**
     * Get bankAccountName.
     *
     * @return string
     */
    public function getBankAccountName()
    {
        return $this->bankAccountName;
    }

    /**
     * Set bankAccountNumber.
     *
     * @param string $bankAccountNumber
     *
     * @return SalesCompanyProfileAccount
     */
    public function setBankAccountNumber($bankAccountNumber)
    {
        $this->bankAccountNumber = $bankAccountNumber;

        return $this;
    }

    /**
     * Get bankAccountNumber.
     *
     * @return string
     */
    public function getBankAccountNumber()
    {
        return $this->bankAccountNumber;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return SalesCompanyProfileAccount
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
     * @return SalesCompanyProfileAccount
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
}
