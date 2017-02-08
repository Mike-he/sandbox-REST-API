<?php

namespace Sandbox\ApiBundle\Entity\Finance;

use Doctrine\ORM\Mapping as ORM;

/**
 * FinanceOfficialInvoiceProfile.
 *
 * @ORM\Table(name="finance_official_invoice_profiles")
 * @ORM\Entity
 */
class FinanceOfficialInvoiceProfile
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=64)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=16)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="taxpayer_id", type="string", length=64)
     */
    private $taxpayerId;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_name", type="string", length=64)
     */
    private $bankName;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account", type="string", length=64)
     */
    private $bankAccount;

    /**
     * @var string
     *
     * @ORM\Column(name="company_info", type="text")
     */
    private $companyInfo;

    /**
     * @var string
     *
     * @ORM\Column(name="receiver", type="string", length=16)
     */
    private $receiver;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text")
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", type="string", length=16)
     */
    private $postalCode;

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
     * Set title.
     *
     * @param string $title
     *
     * @return FinanceOfficialInvoiceProfile
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
     * Set type.
     *
     * @param string $type
     *
     * @return FinanceOfficialInvoiceProfile
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set category.
     *
     * @param string $category
     *
     * @return FinanceOfficialInvoiceProfile
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
     * @return FinanceOfficialInvoiceProfile
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
     * Set bankName.
     *
     * @param string $bankName
     *
     * @return FinanceOfficialInvoiceProfile
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * Get bankName.
     *
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Set bankAccount.
     *
     * @param string $bankAccount
     *
     * @return FinanceOfficialInvoiceProfile
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * Get bankAccount.
     *
     * @return string
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * Set companyInfo.
     *
     * @param string $companyInfo
     *
     * @return FinanceOfficialInvoiceProfile
     */
    public function setCompanyInfo($companyInfo)
    {
        $this->companyInfo = $companyInfo;

        return $this;
    }

    /**
     * Get companyInfo.
     *
     * @return string
     */
    public function getCompanyInfo()
    {
        return $this->companyInfo;
    }

    /**
     * Set receiver.
     *
     * @param string $receiver
     *
     * @return FinanceOfficialInvoiceProfile
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver.
     *
     * @return string
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set address.
     *
     * @param string $address
     *
     * @return FinanceOfficialInvoiceProfile
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
     * @return FinanceOfficialInvoiceProfile
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
     * Set postalCode.
     *
     * @param string $postalCode
     *
     * @return FinanceOfficialInvoiceProfile
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode.
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }
}
