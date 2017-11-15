<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SalesCompanyApply
 *
 * @ORM\Table(name="sales_company_apply")
 * @ORM\Entity()
 */
class SalesCompanyApply
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REFUSED = 'rejected';
    const STATUS_CLOSED = 'closed';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="contacter", type="string", length=255)
     */
    private $contacter;

    /**
     * @var string
     *
     * @ORM\Column(name="contacter_phone", type="string", length=255)
     */
    private $contacterPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="contacter_email", type="string", length=255)
     */
    private $contacterEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="financial_contacter", type="string", length=255, nullable=true)
     */
    private $financialContacter;

    /**
     * @var string
     *
     * @ORM\Column(name="financial_contacter_phone", type="string", length=255, nullable=true)
     */
    private $financialContacterPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="financial_contacter_email", type="string", length=255, nullable=true)
     */
    private $financialContacterEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=1024)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64)
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var string
     *
     * @ORM\Column(name="room_types", type="string", length=255)
     */
    private $roomTypes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modification_date", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $modificationDate;


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
     * Set name
     *
     * @param string $name
     * @return SalesCompanyApply
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
     * Set phone
     *
     * @param string $phone
     * @return SalesCompanyApply
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
     * Set website
     *
     * @param string $website
     * @return SalesCompanyApply
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
     * @return string
     */
    public function getContacter()
    {
        return $this->contacter;
    }

    /**
     * @param string $contacter
     */
    public function setContacter($contacter)
    {
        $this->contacter = $contacter;
    }

    /**
     * @return string
     */
    public function getContacterPhone()
    {
        return $this->contacterPhone;
    }

    /**
     * @param string $contacterPhone
     */
    public function setContacterPhone($contacterPhone)
    {
        $this->contacterPhone = $contacterPhone;
    }

    /**
     * @return string
     */
    public function getContacterEmail()
    {
        return $this->contacterEmail;
    }

    /**
     * @param string $contacterEmail
     */
    public function setContacterEmail($contacterEmail)
    {
        $this->contacterEmail = $contacterEmail;
    }

    /**
     * @return string
     */
    public function getFinancialContacter()
    {
        return $this->financialContacter;
    }

    /**
     * @param string $financialContacter
     */
    public function setFinancialContacter($financialContacter)
    {
        $this->financialContacter = $financialContacter;
    }

    /**
     * @return string
     */
    public function getFinancialContacterPhone()
    {
        return $this->financialContacterPhone;
    }

    /**
     * @param string $financialContacterPhone
     */
    public function setFinancialContacterPhone($financialContacterPhone)
    {
        $this->financialContacterPhone = $financialContacterPhone;
    }

    /**
     * @return string
     */
    public function getFinancialContacterEmail()
    {
        return $this->financialContacterEmail;
    }

    /**
     * @param string $financialContacterEmail
     */
    public function setFinancialContacterEmail($financialContacterEmail)
    {
        $this->financialContacterEmail = $financialContacterEmail;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return SalesCompanyApply
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return SalesCompanyApply
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return SalesCompanyApply
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
     * @return string
     */
    public function getRoomTypes()
    {
        return $this->roomTypes;
    }

    /**
     * @param string $roomTypes
     */
    public function setRoomTypes($roomTypes)
    {
        $this->roomTypes = $roomTypes;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return SalesCompanyApply
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
     * @return SalesCompanyApply
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
}
