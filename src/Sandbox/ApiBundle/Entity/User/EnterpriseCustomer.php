<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * EnterpriseCustomer
 *
 * @ORM\Table(name="enterprise_customer")
 * @ORM\Entity
 */
class EnterpriseCustomer
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="company_id", type="integer", nullable=false)
     */
    private $companyId;

    /**
     * @var string
     *
     * @Assert\NotBlank();
     * @ORM\Column(name="name", type="string", length=512, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="register_address", type="string", length=512, nullable=true)
     */
    private $registerAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="business_license_number", type="string", length=512, nullable=true)
     */
    private $businessLicenseNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="organization_certificate_code", type="string", length=512, nullable=true)
     */
    private $organizationCertificateCode;

    /**
     * @var string
     *
     * @ORM\Column(name="tax_registration_number", type="string", length=512, nullable=true)
     */
    private $taxRegistrationNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="taxpayer_identification_number", type="string", length=512, nullable=true)
     */
    private $taxpayerIdentificationNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_name", type="string", length=512, nullable=true)
     */
    private $bankName;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_number", type="string", length=512, nullable=true)
     */
    private $bankAccountNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=512, nullable=true)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=512, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="industry", type="string", length=512, nullable=true)
     */
    private $industry;

    /**
     * @var string
     *
     * @ORM\Column(name="mailing_address", type="string", length=512, nullable=true)
     */
    private $mailingAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(name="modification_date", type="datetime", nullable=false)
     */
    private $modificationDate;

    /**
     * @var array
     */
    private $contacts;


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
     * Set name
     *
     * @param string $name
     * @return EnterpriseCustomer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set registerAddress
     *
     * @param string $registerAddress
     * @return EnterpriseCustomer
     */
    public function setRegisterAddress($registerAddress)
    {
        $this->registerAddress = $registerAddress;

        return $this;
    }

    /**
     * Get registerAddress
     *
     * @return string 
     */
    public function getRegisterAddress()
    {
        return $this->registerAddress;
    }

    /**
     * Set businessLicenseNumber
     *
     * @param string $businessLicenseNumber
     * @return EnterpriseCustomer
     */
    public function setBusinessLicenseNumber($businessLicenseNumber)
    {
        $this->businessLicenseNumber = $businessLicenseNumber;

        return $this;
    }

    /**
     * Get businessLicenseNumber
     *
     * @return string 
     */
    public function getBusinessLicenseNumber()
    {
        return $this->businessLicenseNumber;
    }

    /**
     * Set organizationCertificateCode
     *
     * @param string $organizationCertificateCode
     * @return EnterpriseCustomer
     */
    public function setOrganizationCertificateCode($organizationCertificateCode)
    {
        $this->organizationCertificateCode = $organizationCertificateCode;

        return $this;
    }

    /**
     * Get organizationCertificateCode
     *
     * @return string 
     */
    public function getOrganizationCertificateCode()
    {
        return $this->organizationCertificateCode;
    }

    /**
     * Set taxRegistrationNumber
     *
     * @param string $taxRegistrationNumber
     * @return EnterpriseCustomer
     */
    public function setTaxRegistrationNumber($taxRegistrationNumber)
    {
        $this->taxRegistrationNumber = $taxRegistrationNumber;

        return $this;
    }

    /**
     * Get taxRegistrationNumber
     *
     * @return string 
     */
    public function getTaxRegistrationNumber()
    {
        return $this->taxRegistrationNumber;
    }

    /**
     * Set taxpayerIdentificationNumber
     *
     * @param string $taxpayerIdentificationNumber
     * @return EnterpriseCustomer
     */
    public function setTaxpayerIdentificationNumber($taxpayerIdentificationNumber)
    {
        $this->taxpayerIdentificationNumber = $taxpayerIdentificationNumber;

        return $this;
    }

    /**
     * Get taxpayerIdentificationNumber
     *
     * @return string 
     */
    public function getTaxpayerIdentificationNumber()
    {
        return $this->taxpayerIdentificationNumber;
    }

    /**
     * Set bankName
     *
     * @param string $bankName
     * @return EnterpriseCustomer
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * Get bankName
     *
     * @return string 
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Set bankAccountNumber
     *
     * @param string $bankAccountNumber
     * @return EnterpriseCustomer
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
     * Set website
     *
     * @param string $website
     * @return EnterpriseCustomer
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string 
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return EnterpriseCustomer
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set industry
     *
     * @param string $industry
     * @return EnterpriseCustomer
     */
    public function setIndustry($industry)
    {
        $this->industry = $industry;

        return $this;
    }

    /**
     * Get industry
     *
     * @return string 
     */
    public function getIndustry()
    {
        return $this->industry;
    }

    /**
     * Set mailingAddress
     *
     * @param string $mailingAddress
     * @return EnterpriseCustomer
     */
    public function setMailingAddress($mailingAddress)
    {
        $this->mailingAddress = $mailingAddress;

        return $this;
    }

    /**
     * Get mailingAddress
     *
     * @return string 
     */
    public function getMailingAddress()
    {
        return $this->mailingAddress;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return EnterpriseCustomer
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return EnterpriseCustomer
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
     * Set modificationDate
     *
     * @param \DateTime $modificationDate
     * @return EnterpriseCustomer
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
     * @return array
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param array $contacts
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;
    }
}
