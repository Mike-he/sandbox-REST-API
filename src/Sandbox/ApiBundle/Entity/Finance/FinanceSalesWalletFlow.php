<?php

namespace Sandbox\ApiBundle\Entity\Finance;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * FinanceSalesWalletFlow.
 *
 * @ORM\Table(name="finance_sales_wallet_flows")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Finance\FinanceSalesWalletFlowsRepository")
 */
class FinanceSalesWalletFlow
{
    const MONTHLY_ORDER_AMOUNT = '秒租月结入账';
    const REALTIME_ORDERS_AMOUNT = '订单实时入账';
    const REALTIME_BILLS_AMOUNT = '账单实时入账';
    const REALTIME_SERVICE_ORDERS_AMOUNT = '服务订单实时入账';
    const WITHDRAW_AMOUNT = '提现';
    const WITHDRAW_FAILED_AMOUNT = '提现失败';
    const REFUND_ORDERS_AMOUNT = '订单退款';

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
     * @ORM\Column(name="company_id", type="integer")
     */
    private $companyId;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="change_amount", type="string", length=255)
     */
    private $changeAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="wallet_total_amount", type="string", length=255)
     */
    private $walletTotalAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="order_number", type="string", length=255, nullable=true)
     */
    private $orderNumber;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
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
     * Set title.
     *
     * @param string $title
     *
     * @return FinanceSalesWalletFlow
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
     * Set changeAmount.
     *
     * @param string $changeAmount
     *
     * @return FinanceSalesWalletFlow
     */
    public function setChangeAmount($changeAmount)
    {
        $this->changeAmount = $changeAmount;

        return $this;
    }

    /**
     * Get changeAmount.
     *
     * @return string
     */
    public function getChangeAmount()
    {
        return $this->changeAmount;
    }

    /**
     * Set walletTotalAmount.
     *
     * @param string $walletTotalAmount
     *
     * @return FinanceSalesWalletFlow
     */
    public function setWalletTotalAmount($walletTotalAmount)
    {
        $this->walletTotalAmount = $walletTotalAmount;

        return $this;
    }

    /**
     * Get walletTotalAmount.
     *
     * @return string
     */
    public function getWalletTotalAmount()
    {
        return $this->walletTotalAmount;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return FinanceSalesWalletFlow
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
}
