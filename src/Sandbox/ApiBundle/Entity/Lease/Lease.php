<?php

namespace Sandbox\ApiBundle\Entity\Lease;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;
use Sandbox\ApiBundle\Entity\User\User;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="leases")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Lease\LeaseRepository")
 */
class Lease
{
    const LEASE_STATUS_DRAFTING = 'drafting';
    const LEASE_STATUS_RECONFIRMING = 'reconfirming';
    const LEASE_STATUS_CONFIRMING = 'confirming';
    const LEASE_STATUS_CONFIRMED = 'confirmed';
    const LEASE_STATUS_PERFORMING = 'performing';
    const LEASE_STATUS_END = 'end';
    const LEASE_STATUS_OVERTIME = 'expired';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "lease_bill", "lease_list"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="serial_number", type="string", length=50, nullable=true)
     *
     * @Serializer\Groups({"main", "client","lease_bill", "lease_list"})
     */
    private $serialNumber;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumn(name="drawee", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Serializer\Groups({"client"})
     */
    private $drawee;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Serializer\Groups({"client"})
     */
    private $product;

    /**
     * Person in charge.
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumn(name="supervisor", referencedColumnName="id", onDelete="SET NULL")
     */
    private $supervisor;

    /**
     * House used purpose.
     *
     * @var string
     *
     * @ORM\Column(name="purpose", type="text", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $purpose;

    /**
     * @var string
     *
     * @ORM\Column(name="other_expenses", type="text", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $otherExpenses;

    /**
     * @var string
     *
     * @ORM\Column(name="supplementary_terms", type="text", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $supplementaryTerms;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $endDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="termination_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $terminationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modification_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $modificationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=15, nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $status;

    /**
     * @var float
     *
     * @ORM\Column(name="monthly_rent", type="decimal", precision=10, scale=2, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $monthlyRent;

    /**
     * @var float
     *
     * @ORM\Column(name="total_rent", type="decimal", precision=10, scale=2, nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $totalRent;

    /**
     * @var float
     *
     * @ORM\Column(name="deposit", type="decimal", precision=10, scale=2, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $deposit;

    /**
     * @var ProductAppointment
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Product\ProductAppointment")
     * @ORM\JoinColumn(name="product_appointment_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Serializer\Groups({"main"})
     */
    private $productAppointment;

    /**
     * @var string
     *
     * @ORM\Column(name="lessee_name", type="string", length=40, nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $lesseeName;

    /**
     * @var string
     *
     * @ORM\Column(name="lessee_address", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $lesseeAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="lessee_contact", type="string", length=20, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $lesseeContact;

    /**
     * @var string
     *
     * @ORM\Column(name="lessee_phone", type="string", length=128, nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $lesseePhone;

    /**
     * @var string
     *
     * @ORM\Column(name="lessee_email", type="string", length=128, nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $lesseeEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="lessor_name", type="string", length=40, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $lessorName;

    /**
     * @var string
     *
     * @ORM\Column(name="lessor_address", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $lessorAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="lessor_contact", type="string", length=20, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $lessorContact;

    /**
     * @var string
     *
     * @ORM\Column(name="lessor_phone", type="string", length=128, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $lessorPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="lessor_email", type="string", length=128, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $lessorEmail;

    /**
     * @var LeaseRentTypes
     *
     * @ORM\ManyToMany(targetEntity="LeaseRentTypes")
     * @ORM\JoinTable(
     *      name="lease_has_rent_types",
     *      joinColumns={@ORM\JoinColumn(name="lease_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="lease_rent_types_id", referencedColumnName="id")}
     * )
     *
     * @Serializer\Groups({"main"})
     */
    private $leaseRentTypes;

    /**
     * @var array
     * @Serializer\Groups({"main"})
     */
    private $bills;

    /**
     * @var int
     * @Serializer\Groups({"lease_list"})
     */
    private $paidLeaseBillsAmount;

    /**
     * @var int
     * @Serializer\Groups({"lease_list"})
     */
    private $otherBillsAmount;

    /**
     * @var int
     * @Serializer\Groups({"lease_list"})
     */
    private $totalLeaseBillsAmount;

    /**
     * @var int
     * @Serializer\Groups({"main"})
     */
    private $unpaidLeaseBillsAmount;

    /**
     * @var User
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinTable(
     *      name="lease_has_invited_persons",
     *      joinColumns={@ORM\JoinColumn(name="lease_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     *
     * @Serializer\Groups({"main"})
     */
    private $invitedPeople;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="effective_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $effectiveDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="confirmation_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $confirmationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiration_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $expirationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="reconfirmation_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $reconfirmationDate;

    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
        $this->modificationDate = new \DateTime('now');
        $this->leaserentTypes = new ArrayCollection();
        $this->invitedPeople = new ArrayCollection();
    }

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
     * @return User
     */
    public function getDrawee()
    {
        return $this->drawee;
    }

    /**
     * @param User $drawee
     */
    public function setDrawee($drawee)
    {
        $this->drawee = $drawee;
    }

    /**
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return User
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * @param User $supervisor
     */
    public function setSupervisor($supervisor)
    {
        $this->supervisor = $supervisor;
    }

    /**
     * @return string
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * @param string $purpose
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
    }

    /**
     * @return string
     */
    public function getOtherExpenses()
    {
        return $this->otherExpenses;
    }

    /**
     * @param string $otherExpenses
     */
    public function setOtherExpenses($otherExpenses)
    {
        $this->otherExpenses = $otherExpenses;
    }

    /**
     * @return string
     */
    public function getSupplementaryTerms()
    {
        return $this->supplementaryTerms;
    }

    /**
     * @param string $supplementaryTerms
     */
    public function setSupplementaryTerms($supplementaryTerms)
    {
        $this->supplementaryTerms = $supplementaryTerms;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return \DateTime
     */
    public function getTerminationDate()
    {
        return $this->terminationDate;
    }

    /**
     * @param \DateTime $terminationDate
     */
    public function setTerminationDate($terminationDate)
    {
        $this->terminationDate = $terminationDate;
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
     * @return float
     */
    public function getMonthlyRent()
    {
        return $this->monthlyRent;
    }

    /**
     * @param float $monthlyRent
     */
    public function setMonthlyRent($monthlyRent)
    {
        $this->monthlyRent = $monthlyRent;
    }

    /**
     * @return float
     */
    public function getTotalRent()
    {
        return $this->totalRent;
    }

    /**
     * @param float $totalRent
     */
    public function setTotalRent($totalRent)
    {
        $this->totalRent = $totalRent;
    }

    /**
     * @return float
     */
    public function getDeposit()
    {
        return $this->deposit;
    }

    /**
     * @param float $deposit
     */
    public function setDeposit($deposit)
    {
        $this->deposit = $deposit;
    }

    /**
     * @return ProductAppointment
     */
    public function getProductAppointment()
    {
        return $this->productAppointment;
    }

    /**
     * @param ProductAppointment $productAppointment
     */
    public function setProductAppointment($productAppointment)
    {
        $this->productAppointment = $productAppointment;
    }

    /**
     * @return string
     */
    public function getLesseeName()
    {
        return $this->lesseeName;
    }

    /**
     * @param string $lesseeName
     */
    public function setLesseeName($lesseeName)
    {
        $this->lesseeName = $lesseeName;
    }

    /**
     * @return string
     */
    public function getLesseeAddress()
    {
        return $this->lesseeAddress;
    }

    /**
     * @param string $lesseeAddress
     */
    public function setLesseeAddress($lesseeAddress)
    {
        $this->lesseeAddress = $lesseeAddress;
    }

    /**
     * @return string
     */
    public function getLesseePhone()
    {
        return $this->lesseePhone;
    }

    /**
     * @param string $lesseePhone
     */
    public function setLesseePhone($lesseePhone)
    {
        $this->lesseePhone = $lesseePhone;
    }

    /**
     * @return string
     */
    public function getLesseeEmail()
    {
        return $this->lesseeEmail;
    }

    /**
     * @param string $lesseeEmail
     */
    public function setLesseeEmail($lesseeEmail)
    {
        $this->lesseeEmail = $lesseeEmail;
    }

    /**
     * @return string
     */
    public function getLessorName()
    {
        return $this->lessorName;
    }

    /**
     * @param string $lessorName
     */
    public function setLessorName($lessorName)
    {
        $this->lessorName = $lessorName;
    }

    /**
     * @return string
     */
    public function getLessorAddress()
    {
        return $this->lessorAddress;
    }

    /**
     * @param string $lessorAddress
     */
    public function setLessorAddress($lessorAddress)
    {
        $this->lessorAddress = $lessorAddress;
    }

    /**
     * @return string
     */
    public function getLessorPhone()
    {
        return $this->lessorPhone;
    }

    /**
     * @param string $lessorPhone
     */
    public function setLessorPhone($lessorPhone)
    {
        $this->lessorPhone = $lessorPhone;
    }

    /**
     * @return string
     */
    public function getLessorEmail()
    {
        return $this->lessorEmail;
    }

    /**
     * @param string $lessorEmail
     */
    public function setLessorEmail($lessorEmail)
    {
        $this->lessorEmail = $lessorEmail;
    }

    /**
     * @return LeaseRentTypes
     */
    public function getLeaseRentTypes()
    {
        return $this->leaseRentTypes;
    }

    /**
     * @param LeaseRentTypes $leaseRentType
     */
    public function addLeaseRentTypes($leaseRentType)
    {
        $this->leaseRentTypes[] = $leaseRentType;
    }

    /**
     * @param LeaseRentTypes $leaseRentType
     */
    public function removeLeaseRentTypes($leaseRentType)
    {
        return $this->leaseRentTypes->removeElement($leaseRentType);
    }

    /**
     * @return string
     */
    public function getLesseeContact()
    {
        return $this->lesseeContact;
    }

    /**
     * @param string $lesseeContact
     */
    public function setLesseeContact($lesseeContact)
    {
        $this->lesseeContact = $lesseeContact;
    }

    /**
     * @return string
     */
    public function getLessorContact()
    {
        return $this->lessorContact;
    }

    /**
     * @param string $lessorContact
     */
    public function setLessorContact($lessorContact)
    {
        $this->lessorContact = $lessorContact;
    }

    /**
     * @return array
     */
    public function getBills()
    {
        return $this->bills;
    }

    /**
     * @param array $bills
     */
    public function setBills($bills)
    {
        $this->bills = $bills;
    }

    /**
     * @return int
     */
    public function getTotalLeaseBillsAmount()
    {
        return $this->totalLeaseBillsAmount;
    }

    /**
     * @param int $totalLeaseBillsAmount
     */
    public function setTotalLeaseBillsAmount($totalLeaseBillsAmount)
    {
        $this->totalLeaseBillsAmount = $totalLeaseBillsAmount;
    }

    /**
     * @return int
     */
    public function getPaidLeaseBillsAmount()
    {
        return $this->paidLeaseBillsAmount;
    }

    /**
     * @param int $paidLeaseBillsAmount
     */
    public function setPaidLeaseBillsAmount($paidLeaseBillsAmount)
    {
        $this->paidLeaseBillsAmount = $paidLeaseBillsAmount;
    }

    /**
     * @return int
     */
    public function getOtherBillsAmount()
    {
        return $this->otherBillsAmount;
    }

    /**
     * @param int $otherBillsAmount
     */
    public function setOtherBillsAmount($otherBillsAmount)
    {
        $this->otherBillsAmount = $otherBillsAmount;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("drawee")
     * @Serializer\Groups({"main", "lease_list"})
     */
    public function getDraweeId()
    {
        return is_null($this->drawee) ?
            null : $this->drawee->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("supervisor")
     * @Serializer\Groups({"main", "lease_list"})
     */
    public function getSurpervisorId()
    {
        return is_null($this->supervisor) ?
            null : $this->supervisor->getId();
    }

    /**
     * @return int
     */
    public function getUnpaidLeaseBillsAmount()
    {
        return $this->unpaidLeaseBillsAmount;
    }

    /**
     * @param int $unpaidLeaseBillsAmount
     */
    public function setUnpaidLeaseBillsAmount($unpaidLeaseBillsAmount)
    {
        $this->unpaidLeaseBillsAmount = $unpaidLeaseBillsAmount;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("product")
     * @Serializer\Groups({"main", "lease_list"})
     */
    public function degenerateProduct()
    {
        return [
            'id' => $this->product->getId(),
            'unit_price' => $this->product->getUnitPrice(),
            'base_price' => $this->product->getBasePrice(),
            'room' => [
                'id' => $this->product->getRoom()->getId(),
                'number' => $this->product->getRoom()->getNumber(),
                'name' => $this->product->getRoom()->getName(),
                'type' => $this->product->getRoom()->getType(),
                'area' => $this->product->getRoom()->getArea(),
                'allowed_people' => $this->product->getRoom()->getAllowedPeople(),
                'building' => [
                    'name' => $this->product->getRoom()->getBuilding()->getName(),
                    'address' => $this->product->getRoom()->getBuilding()->getAddress(),
                ],
                'city' => $this->product->getRoom()->getBuilding()->getCity()->getName(),
                'attachment' => $this->product->getRoom()->degenerateAttachment(),
            ],
        ];
    }

    /**
     * @return User
     */
    public function getInvitedPeople()
    {
        return $this->invitedPeople;
    }

    /**
     * @param User $invitedPeople
     */
    public function addInvitedPeople($invitedPeople)
    {
        $this->invitedPeople[] = $invitedPeople;
    }

    /**
     * @param User $invitedPeople
     */
    public function removeInvitedPeople($invitedPeople)
    {
        $this->invitedPeople[] = $invitedPeople;
    }

    /**
     * @return \DateTime
     */
    public function getEffectiveDate()
    {
        return $this->effectiveDate;
    }

    /**
     * @param \DateTime $effectiveDate
     */
    public function setEffectiveDate($effectiveDate)
    {
        $this->effectiveDate = $effectiveDate;
    }

    /**
     * @return \DateTime
     */
    public function getConfirmationDate()
    {
        return $this->confirmationDate;
    }

    /**
     * @param \DateTime $confirmationDate
     */
    public function setConfirmationDate($confirmationDate)
    {
        $this->confirmationDate = $confirmationDate;
    }

    /**
     * @return \DateTime
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @param \DateTime $expirationDate
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    /**
     * @return \DateTime
     */
    public function getReconfirmationDate()
    {
        return $this->reconfirmationDate;
    }

    /**
     * @param \DateTime $reconfirmationDate
     */
    public function setReconfirmationDate($reconfirmationDate)
    {
        $this->reconfirmationDate = $reconfirmationDate;
    }
}
