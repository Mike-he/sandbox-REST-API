<?php

namespace Sandbox\ApiBundle\Entity\Finance;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * FinanceSalesWallet.
 *
 * @ORM\Table(name="finance_sales_wallet")
 * @ORM\Entity
 */
class FinanceSalesWallet
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
     * @ORM\Column(name="withdrawable_amount", type="float")
     */
    private $withdrawableAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="bill_amount", type="float")
     */
    private $billAmount = 0;

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
     * @var int
     *
     * @ORM\Column(name="company_id", type="integer")
     */
    private $companyId;

    /**
     * @var float
     *
     * @ORM\Column(name="total_amount", type="float")
     */
    private $totalAmount = 0;

    /**
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param float $totalAmount
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
    }

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
     * Set withdrawableAmount.
     *
     * @param float $withdrawableAmount
     *
     * @return FinanceSalesWallet
     */
    public function setWithdrawableAmount($withdrawableAmount)
    {
        $this->withdrawableAmount = $withdrawableAmount;

        return $this;
    }

    /**
     * Get withdrawableAmount.
     *
     * @return float
     */
    public function getWithdrawableAmount()
    {
        return $this->withdrawableAmount;
    }

    /**
     * Set billAmount.
     *
     * @param float $billAmount
     *
     * @return FinanceSalesWallet
     */
    public function setBillAmount($billAmount)
    {
        $this->billAmount = $billAmount;

        return $this;
    }

    /**
     * Get billAmount.
     *
     * @return float
     */
    public function getBillAmount()
    {
        return $this->billAmount;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return FinanceSalesWallet
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
     * @return FinanceSalesWallet
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

    /**
     * Set companyId.
     *
     * @param int $companyId
     *
     * @return FinanceSalesWallet
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
