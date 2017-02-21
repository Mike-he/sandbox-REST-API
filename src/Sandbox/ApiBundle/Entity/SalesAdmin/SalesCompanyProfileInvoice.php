<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * SalesCompanyProfileInvoice.
 *
 * @ORM\Table(name="sales_company_profile_invoices")
 * @ORM\Entity
 */
class SalesCompanyProfileInvoice
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
     * @ORM\Column(name="title", type="string", length=255)
     *
     * @Serializer\Groups({"finance"})
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=255)
     *
     * @Serializer\Groups({"finance"})
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="taxpayer_id", type="string", length=255)
     *
     * @Serializer\Groups({"finance"})
     */
    private $taxpayerId;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"finance"})
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"finance"})
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_name", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"finance"})
     */
    private $bankAccountName;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_number", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"finance"})
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
     * @return SalesCompanyProfileInvoice
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
     * Set title.
     *
     * @param string $title
     *
     * @return SalesCompanyProfileInvoice
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set category.
     *
     * @param string $category
     *
     * @return SalesCompanyProfileInvoice
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set taxpayerId.
     *
     * @param string $taxpayerId
     *
     * @return SalesCompanyProfileInvoice
     */
    public function setTaxpayerId($taxpayerId)
    {
        $this->taxpayerId = $taxpayerId;

        return $this;
    }

    /**
     * Get taxpayerId.
     *
     * @return string
     */
    public function getTaxpayerId()
    {
        return $this->taxpayerId;
    }

    /**
     * Set address.
     *
     * @param string $address
     *
     * @return SalesCompanyProfileInvoice
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return SalesCompanyProfileInvoice
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set bankAccountName.
     *
     * @param string $bankAccountName
     *
     * @return SalesCompanyProfileInvoice
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
     * @return SalesCompanyProfileInvoice
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
     * @return SalesCompanyProfileInvoice
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
     * @return SalesCompanyProfileInvoice
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
