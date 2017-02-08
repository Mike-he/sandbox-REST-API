<?php

namespace Sandbox\ApiBundle\Entity\Finance;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * FinanceBillInvoiceInfo.
 *
 * @ORM\Table(name = "finance_bill_invoice_info")
 * @ORM\Entity
 */
class FinanceBillInvoiceInfo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main"})
     */
    private $id;

    /**
     * @var \Sandbox\ApiBundle\Entity\Finance\FinanceLongRentBill
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Finance\FinanceLongRentBill")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bill_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     *
     * @Serializer\Groups({"main"})
     */
    private $bill;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_json", type="text")
     */
    private $invoiceJson;

    /**
     * @var string
     *
     * @ORM\Column(name="express_json", type="text")
     */
    private $expressJson;

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
     * @return FinanceLongRentBill
     */
    public function getBill()
    {
        return $this->bill;
    }

    /**
     * @param FinanceLongRentBill $bill
     */
    public function setBill($bill)
    {
        $this->bill = $bill;
    }

    /**
     * @return string
     */
    public function getInvoiceJson()
    {
        return $this->invoiceJson;
    }

    /**
     * @param string $invoiceJson
     */
    public function setInvoiceJson($invoiceJson)
    {
        $this->invoiceJson = $invoiceJson;
    }

    /**
     * @return string
     */
    public function getExpressJson()
    {
        return $this->expressJson;
    }

    /**
     * @param string $expressJson
     */
    public function setExpressJson($expressJson)
    {
        $this->expressJson = $expressJson;
    }
}
