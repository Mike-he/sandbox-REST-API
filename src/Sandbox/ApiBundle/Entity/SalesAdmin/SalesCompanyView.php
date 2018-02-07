<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;

/**
 * SalesCompanyView.
 *
 * @ORM\Table(name="sales_company_view")
 * @ORM\Entity(
 *      repositoryClass="Sandbox\ApiBundle\Repository\SalesAdmin\SalesCompanyViewRepository"
 * )
 */
class SalesCompanyView
{
    const TYPE_COMPANY = 'sales_company';
    const TYPE_COMPANY_APPLY = 'sales_company_apply';

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
     * @ORM\Column(name="number", type="string", length=255)
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
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

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
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
}
