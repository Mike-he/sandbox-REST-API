<?php

namespace Sandbox\ApiBundle\Entity\Finance;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * FinanceSummary.
 *
 * @ORM\Table(name="finance_summary")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Finance\FinanceSummaryRepository")
 */
class FinanceSummary
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
     * @var float
     *
     * @ORM\Column(name="short_rent_balance", type="float")
     */
    private $shortRentBalance;

    /**
     * @var int
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
     * @var int
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
     * @var int
     *
     * @ORM\Column(name="long_rent_bill_count", type="integer")
     */
    private $longRentBillCount;

    /**
     * @var float
     *
     * @ORM\Column(name="event_order_balance", type="float")
     */
    private $eventOrderBalance;

    /**
     * @var int
     *
     * @ORM\Column(name="event_order_count", type="integer")
     */
    private $eventOrderCount;

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
     * @ORM\Column(name="summary_date", type="datetime")
     */
    private $summaryDate;

    /**
     * @var int
     *
     * @ORM\Column(name="company_id", type="integer")
     */
    private $companyId;

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
     * @return \DateTime
     */
    public function getSummaryDate()
    {
        return $this->summaryDate;
    }

    /**
     * @param \DateTime $summaryDate
     */
    public function setSummaryDate($summaryDate)
    {
        $this->summaryDate = $summaryDate;
    }

    /**
     * @return float
     */
    public function getEventOrderBalance()
    {
        return $this->eventOrderBalance;
    }

    /**
     * @param float $eventOrderBalance
     */
    public function setEventOrderBalance($eventOrderBalance)
    {
        $this->eventOrderBalance = $eventOrderBalance;
    }

    /**
     * @return int
     */
    public function getEventOrderCount()
    {
        return $this->eventOrderCount;
    }

    /**
     * @param int $eventOrderCount
     */
    public function setEventOrderCount($eventOrderCount)
    {
        $this->eventOrderCount = $eventOrderCount;
    }

    /**
     * Set shortRentBalance.
     *
     * @param float $shortRentBalance
     *
     * @return FinanceSummary
     */
    public function setShortRentBalance($shortRentBalance)
    {
        $this->shortRentBalance = $shortRentBalance;

        return $this;
    }

    /**
     * Get shortRentBalance.
     *
     * @return float
     */
    public function getShortRentBalance()
    {
        return $this->shortRentBalance;
    }

    /**
     * Set shortRentCount.
     *
     * @param int $shortRentCount
     *
     * @return FinanceSummary
     */
    public function setShortRentCount($shortRentCount)
    {
        $this->shortRentCount = $shortRentCount;

        return $this;
    }

    /**
     * Get shortRentCount.
     *
     * @return int
     */
    public function getShortRentCount()
    {
        return $this->shortRentCount;
    }

    /**
     * Set longRentBalance.
     *
     * @param float $longRentBalance
     *
     * @return FinanceSummary
     */
    public function setLongRentBalance($longRentBalance)
    {
        $this->longRentBalance = $longRentBalance;

        return $this;
    }

    /**
     * Get longRentBalance.
     *
     * @return float
     */
    public function getLongRentBalance()
    {
        return $this->longRentBalance;
    }

    /**
     * Set longRentCount.
     *
     * @param int $longRentCount
     *
     * @return FinanceSummary
     */
    public function setLongRentCount($longRentCount)
    {
        $this->longRentCount = $longRentCount;

        return $this;
    }

    /**
     * Get longRentCount.
     *
     * @return int
     */
    public function getLongRentCount()
    {
        return $this->longRentCount;
    }

    /**
     * Set longRentBillBalance.
     *
     * @param float $longRentBillBalance
     *
     * @return FinanceSummary
     */
    public function setLongRentBillBalance($longRentBillBalance)
    {
        $this->longRentBillBalance = $longRentBillBalance;

        return $this;
    }

    /**
     * Get longRentBillBalance.
     *
     * @return float
     */
    public function getLongRentBillBalance()
    {
        return $this->longRentBillBalance;
    }

    /**
     * Set longRentBillCount.
     *
     * @param int $longRentBillCount
     *
     * @return FinanceSummary
     */
    public function setLongRentBillCount($longRentBillCount)
    {
        $this->longRentBillCount = $longRentBillCount;

        return $this;
    }

    /**
     * Get longRentBillCount.
     *
     * @return int
     */
    public function getLongRentBillCount()
    {
        return $this->longRentBillCount;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return FinanceSummary
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
     * Set companyId.
     *
     * @param int $companyId
     *
     * @return FinanceSummary
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
}
