<?php

namespace Sandbox\ApiBundle\Entity\Finance;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * FinanceSummary
 *
 * @ORM\Table(name="finance_summary")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Finance\FinanceSummaryRepository")
 */
class FinanceSummary
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
     * @var float
     *
     * @ORM\Column(name="short_rent_balance", type="float")
     */
    private $shortRentBalance;

    /**
     * @var integer
     *
     * @ORM\Column(name="short_rent_count", type="integer")
     */
    private $shortRentCount;

    /**
     * @var float
     *
     * @ORM\Column(name="long_rent_balance", type="float")
     */
    private $longRentBalance;

    /**
     * @var integer
     *
     * @ORM\Column(name="long_rent_count", type="integer")
     */
    private $longRentCount;

    /**
     * @var float
     *
     * @ORM\Column(name="long_rent_bill_balance", type="float")
     */
    private $longRentBillBalance;

    /**
     * @var integer
     *
     * @ORM\Column(name="long_rent_bill_count", type="integer")
     */
    private $longRentBillCount;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="company_id", type="integer")
     */
    private $companyId;


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
     * Set shortRentBalance
     *
     * @param float $shortRentBalance
     * @return FinanceSummary
     */
    public function setShortRentBalance($shortRentBalance)
    {
        $this->shortRentBalance = $shortRentBalance;

        return $this;
    }

    /**
     * Get shortRentBalance
     *
     * @return float 
     */
    public function getShortRentBalance()
    {
        return $this->shortRentBalance;
    }

    /**
     * Set shortRentCount
     *
     * @param integer $shortRentCount
     * @return FinanceSummary
     */
    public function setShortRentCount($shortRentCount)
    {
        $this->shortRentCount = $shortRentCount;

        return $this;
    }

    /**
     * Get shortRentCount
     *
     * @return integer 
     */
    public function getShortRentCount()
    {
        return $this->shortRentCount;
    }

    /**
     * Set longRentBalance
     *
     * @param float $longRentBalance
     * @return FinanceSummary
     */
    public function setLongRentBalance($longRentBalance)
    {
        $this->longRentBalance = $longRentBalance;

        return $this;
    }

    /**
     * Get longRentBalance
     *
     * @return float 
     */
    public function getLongRentBalance()
    {
        return $this->longRentBalance;
    }

    /**
     * Set longRentCount
     *
     * @param integer $longRentCount
     * @return FinanceSummary
     */
    public function setLongRentCount($longRentCount)
    {
        $this->longRentCount = $longRentCount;

        return $this;
    }

    /**
     * Get longRentCount
     *
     * @return integer 
     */
    public function getLongRentCount()
    {
        return $this->longRentCount;
    }

    /**
     * Set longRentBillBalance
     *
     * @param float $longRentBillBalance
     * @return FinanceSummary
     */
    public function setLongRentBillBalance($longRentBillBalance)
    {
        $this->longRentBillBalance = $longRentBillBalance;

        return $this;
    }

    /**
     * Get longRentBillBalance
     *
     * @return float 
     */
    public function getLongRentBillBalance()
    {
        return $this->longRentBillBalance;
    }

    /**
     * Set longRentBillCount
     *
     * @param integer $longRentBillCount
     * @return FinanceSummary
     */
    public function setLongRentBillCount($longRentBillCount)
    {
        $this->longRentBillCount = $longRentBillCount;

        return $this;
    }

    /**
     * Get longRentBillCount
     *
     * @return integer 
     */
    public function getLongRentBillCount()
    {
        return $this->longRentBillCount;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return FinanceSummary
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
     * Set companyId
     *
     * @param integer $companyId
     * @return FinanceSummary
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get companyId
     *
     * @return integer 
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }
}
