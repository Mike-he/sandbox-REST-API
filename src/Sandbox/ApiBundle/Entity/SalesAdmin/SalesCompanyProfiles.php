<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SalesCompanyProfile
 *
 * @ORM\Table(name="sales_company_profiles")
 * @ORM\Entity
 */
class SalesCompanyProfiles
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
     * @ORM\Column(name="sales_company_id", type="integer")
     */
    private $salesCompanyId;

    /**
     * @var string
     *
     * @ORM\Column(name="cover", type="string", length=255, nullable=true)
     */
    private $cover;

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
     * @var mixed
     */
    private $account;

    /**
     * @var mixed
     */
    private $express;

    /**
     * @var mixed
     */
    private $invoice;


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
     * Set cover
     *
     * @param string $cover
     * @return SalesCompanyProfile
     */
    public function setCover($cover)
    {
        $this->cover = $cover;

        return $this;
    }

    /**
     * Get cover
     *
     * @return string 
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return SalesCompanyProfile
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
     * @return SalesCompanyProfile
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
     * Set salesCompanyId
     *
     * @param integer $salesCompanyId
     * @return SalesCompanyProfile
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
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @return mixed
     */
    public function getExpress()
    {
        return $this->express;
    }

    /**
     * @param mixed $express
     */
    public function setExpress($express)
    {
        $this->express = $express;
    }

    /**
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param mixed $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }
}
