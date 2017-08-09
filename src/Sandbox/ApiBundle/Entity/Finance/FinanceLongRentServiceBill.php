<?php

namespace Sandbox\ApiBundle\Entity\Finance;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * FinanceLongRentServiceBill.
 *
 * @ORM\Table(name="finance_long_rent_service_bill")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Finance\FinanceLongRentServiceBillRepository")
 */
class FinanceLongRentServiceBill
{
    const TYPE_BILL_SERVICE_FEE = 'service_fee';
    const TYPE_BILL_POUNDAGE = 'poundage';

    const SERVICE_FEE_LETTER_HEAD = 'SR';

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
     * @var string
     *
     * @ORM\Column(name="serial_number", type="string", length=50)
     *
     * @Serializer\Groups({"main"})
     */
    private $serialNumber;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     *
     * @Serializer\Groups({"main"})
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50)
     *
     * @Serializer\Groups({"main"})
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Lease\LeaseBill")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bill_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $bill;

    /**
     * @var int
     *
     * @ORM\Column(name="company_id", type="integer")
     *
     * @Serializer\Groups({"main"})
     */
    private $companyId;

    /**
     * @var float
     *
     * @ORM\Column(name="service_fee", type="float", precision=6, scale=3)
     * @Serializer\Groups({"main"})
     */
    private $serviceFee;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
     *
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modification_date", type="datetime")
     *
     * @Serializer\Groups({"main"})
     */
    private $modificationDate;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * @param string $serialNumber
     */
    public function setSerialNumber($serialNumber)
    {
        $this->serialNumber = $serialNumber;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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
     * @return mixed
     */
    public function getBill()
    {
        return $this->bill;
    }

    /**
     * @param mixed $bill
     */
    public function setBill($bill)
    {
        $this->bill = $bill;
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
     * @return float
     */
    public function getServiceFee()
    {
        return $this->serviceFee;
    }

    /**
     * @param float $serviceFee
     */
    public function setServiceFee($serviceFee)
    {
        $this->serviceFee = $serviceFee;
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

    /**
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param \DateTime $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("bill")
     * @Serializer\Groups({"main"})
     */
    public function degenerateBill()
    {
        return [
            'id' => $this->bill->getId(),
            'serial_number' => $this->bill->getSerialNumber(),
            'lease' => [
                'id' => $this->bill->getLease()->getId(),
                'serial_number' => $this->bill->getLease()->getSerialNumber(),
            ],
        ];
    }
}
