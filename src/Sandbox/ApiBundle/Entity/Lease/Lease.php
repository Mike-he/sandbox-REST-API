<?php

namespace Sandbox\ApiBundle\Entity\Lease;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\User\User;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="leases")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Lease\LeaseRepository")
 */
class Lease
{
    const LEASE_STATUS_DRAFTING = 'drafting'; //未生效
    const LEASE_STATUS_PERFORMING = 'performing'; //生效,履行中
    const LEASE_STATUS_TERMINATED = 'terminated'; //已终止
    const LEASE_STATUS_MATURED = 'matured'; //已到期
    const LEASE_STATUS_END = 'end'; //已结束
    const LEASE_STATUS_CLOSED = 'closed'; //已关闭,作废

    const LEASE_STATUS_RECONFIRMING = 'reconfirming';
    const LEASE_STATUS_CONFIRMING = 'confirming';
    const LEASE_STATUS_CONFIRMED = 'confirmed';
    const LEASE_STATUS_EXPIRED = 'expired';

    const LEASE_LETTER_HEAD = 'C';

    const LEASE_LESSEE_TYPE_ENTERPRISE = 'enterprise';
    const LEASE_LESSEE_TYPE_PERSONAL = 'personal';

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
     * @var Sandbox\ApiBundle\Entity\Room\RoomBuilding
     *
     * @ORM\Column(name="building_id",type="integer", nullable=true)
     */
    private $buildingId;

    /**
     * @var Sandbox\ApiBundle\Entity\Product\Product
     *
     * @ORM\Column(name="product_id",type="integer", nullable=true)
     */
    private $productId;

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
     * @var string 承租方类型
     *
     * @ORM\Column(name="lessee_type", type="string", length=40)
     */
    private $lesseeType;

    /**
     * @var int 承租企业
     *
     * @ORM\Column(name="lessee_enterprise", type="integer", length=20, nullable=true)
     */
    private $lesseeEnterprise;

    /**
     * @var int 承租方联系人
     *
     * @ORM\Column(name="lessee_customer", type="integer", length=20)
     */
    private $lesseeCustomer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list", "room_usage"})
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list", "room_usage"})
     */
    private $endDate;

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
     * @var string
     *
     * @ORM\Column(name="deposit_note", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $depositNote;

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
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=15, nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modification_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $modificationDate;

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
     * @var Sandbox\ApiBundle\Entity\Lease\LeaseClue
     *
     * @ORM\Column(name="lease_clue_id", type="integer", nullable=true)
     */
    private $LeaseClueId;

    /**
     * @var Sandbox\ApiBundle\Entity\Lease\LeaseOffer
     *
     * @ORM\Column(name="lease_offer_id", type="integer", nullable=true)
     */
    private $LeaseOfferId;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumn(name="drawee", referencedColumnName="id", onDelete="SET NULL")
     */
    private $drawee;

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
     * The creation date of formal lease.
     *
     * @var \DateTime
     *
     * @ORM\Column(name="confirming_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "lease_list"})
     */
    private $confirmingDate;

    /**
     * @var ProductAppointment
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Product\ProductAppointment")
     * @ORM\JoinColumn(name="product_appointment_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $productAppointment;

    /**
     * @var string
     *
     * @ORM\Column(name="lessee_name", type="string", length=40, nullable=true)
     */
    private $lesseeName;

    /**
     * @var string
     *
     * @ORM\Column(name="lessee_address", type="string", length=255, nullable=true)
     */
    private $lesseeAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="lessee_contact", type="string", length=20, nullable=true)
     */
    private $lesseeContact;

    /**
     * @var string
     *
     * @ORM\Column(name="lessee_phone", type="string", length=128, nullable=true)
     */
    private $lesseePhone;

    /**
     * @var string
     *
     * @ORM\Column(name="lessee_email", type="string", length=128, nullable=true)
     */
    private $lesseeEmail;

    /**
     * @var array
     * @Serializer\Groups({"main"})
     */
    private $bills;

    /**
     * @var int
     * @Serializer\Groups({"main","lease_list"})
     */
    private $pushedLeaseBillsAmount;

    /**
     * @var int
     * @Serializer\Groups({"main","lease_list"})
     */
    private $otherBillsAmount;

    /**
     * @var int
     * @Serializer\Groups({"main","lease_list"})
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
     * @Serializer\Exclude
     */
    private $invitedPeople;

    /**
     * @var float
     *
     * @Serializer\Groups({"main","lease_list"})
     */
    private $pushedLeaseBillsFees;

    /**
     * @var string
     *
     * @ORM\Column(name="access_no", type="string", length=30, nullable=true)
     */
    private $accessNo;

    /**
     * @var array
     *
     * @Serializer\Groups({"main","lease_list"})
     */
    private $changeLogs;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="confirmed_date", type="datetime", nullable=true)
     * @Serializer\Groups({"main", "lease_list", "client"})
     */
    private $conformedDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_auto", type="boolean")
     *
     * @Serializer\Groups({"main"})
     */
    private $isAuto = false;

    /**
     * @var int
     *
     * @ORM\Column(name="plan_day", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $planDay;

    /**
     * @var int
     *
     * @ORM\Column(name="company_id", type="integer")
     */
    private $companyId;

    public function __construct()
    {
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
    public function getPushedLeaseBillsAmount()
    {
        return $this->pushedLeaseBillsAmount;
    }

    /**
     * @param int $pushedLeaseBillsAmount
     */
    public function setPushedLeaseBillsAmount($pushedLeaseBillsAmount)
    {
        $this->pushedLeaseBillsAmount = $pushedLeaseBillsAmount;
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
     * @Serializer\Groups({"main", "lease_list", "lease_bill"})
     */
    public function getDraweeId()
    {
        return is_null($this->drawee) ?
            null : $this->drawee->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("supervisor")
     * @Serializer\Groups({"main", "lease_list", "room_usage"})
     */
    public function getSupervisorId()
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
     * @Serializer\Groups({"main", "lease_list", "lease_bill"})
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
                    'id' => $this->product->getRoom()->getBuilding()->getId(),
                    'name' => $this->product->getRoom()->getBuilding()->getName(),
                    'address' => $this->product->getRoom()->getBuilding()->getAddress(),
                    'company' => [
                        'id' => $this->product->getRoom()->getBuilding()->getCompanyId(),
                        'name' => $this->product->getRoom()->getBuilding()->getCompany()->getName(),
                    ],
                ],
                'city' => $this->product->getRoom()->getBuilding()->getCity()->getName(),
                'attachment' => $this->product->getRoom()->degenerateAttachment(),
            ],
            'lease_rent_types' => $this->product->getLeaseRentTypes(),
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
        $this->invitedPeople->removeElement($invitedPeople);
    }

    /**
     * @return float
     */
    public function getPushedLeaseBillsFees()
    {
        return $this->pushedLeaseBillsFees;
    }

    /**
     * @param float $pushedLeaseBillsFees
     */
    public function setPushedLeaseBillsFees($pushedLeaseBillsFees)
    {
        $this->pushedLeaseBillsFees = $pushedLeaseBillsFees;
    }

    /**
     * @return int
     */
    public function getRoomId()
    {
        return $this->product->getRoom()->getId();
    }

    /**
     * @return Room
     */
    public function getRoom()
    {
        return $this->product->getRoom();
    }

    /**
     * @return RoomBuilding
     */
    public function getBuilding()
    {
        return $this->product->getRoom()->getBuilding();
    }

    /**
     * @return string
     */
    public function getCityName()
    {
        return $this->product->getRoom()->getCity()->getName();
    }

    /**
     * @return string
     */
    public function getBuildingName()
    {
        return $this->product->getRoom()->getBuilding()->getName();
    }

    /**
     * @return string
     */
    public function getRoomName()
    {
        return $this->product->getRoom()->getName();
    }

    /**
     * @return string
     */
    public function getAccessNo()
    {
        return $this->accessNo;
    }

    /**
     * @param string $accessNo
     */
    public function setAccessNo($accessNo)
    {
        $this->accessNo = $accessNo;
    }

    /**
     * @return array
     */
    public function getInvitedPeopleIds()
    {
        return array_map(
            function ($invitedPerson) {
                return $invitedPerson->getId();
            },
            $this->invitedPeople->toArray()
        );
    }

    /**
     * @return string
     */
    public function getDepositNote()
    {
        return $this->depositNote;
    }

    /**
     * @param string $depositNote
     */
    public function setDepositNote($depositNote)
    {
        $this->depositNote = $depositNote;
    }

    /**
     * @return \DateTime
     */
    public function getConfirmingDate()
    {
        return $this->confirmingDate;
    }

    /**
     * @param \DateTime $confirmingDate
     */
    public function setConfirmingDate($confirmingDate)
    {
        $this->confirmingDate = $confirmingDate;
    }

    /**
     * @return array
     */
    public function getChangeLogs()
    {
        return $this->changeLogs;
    }

    /**
     * @param array $changeLogs
     */
    public function setChangeLogs($changeLogs)
    {
        $this->changeLogs = $changeLogs;
    }

    /**
     * @return \DateTime
     */
    public function getConformedDate()
    {
        return $this->conformedDate;
    }

    /**
     * @param \DateTime $conformedDate
     */
    public function setConformedDate($conformedDate)
    {
        $this->conformedDate = $conformedDate;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("invited_people")
     * @Serializer\Groups({"main", "room_usage"})
     */
    public function degenerateInvitedPeople()
    {
        return array_map(
            function ($invitedPerson) {
                return [
                    'id' => $invitedPerson->getId(),
                    'name' => $invitedPerson->getUserProfile()->getName(),
                    'phone' => $invitedPerson->getPhone(),
                    'email' => $invitedPerson->getEmail(),
                ];
            },
            $this->invitedPeople->toArray()
        );
    }

    /**
     * @return bool
     */
    public function isIsAuto()
    {
        return $this->isAuto;
    }

    /**
     * @param bool $isAuto
     */
    public function setIsAuto($isAuto)
    {
        $this->isAuto = $isAuto;
    }

    /**
     * @return int
     */
    public function getPlanDay()
    {
        return $this->planDay;
    }

    /**
     * @param int $planDay
     */
    public function setPlanDay($planDay)
    {
        $this->planDay = $planDay;
    }

    /**
     * @return string
     */
    public function getLesseeType()
    {
        return $this->lesseeType;
    }

    /**
     * @param string $lesseeType
     */
    public function setLesseeType($lesseeType)
    {
        $this->lesseeType = $lesseeType;
    }

    /**
     * @return int
     */
    public function getLesseeEnterprise()
    {
        return $this->lesseeEnterprise;
    }

    /**
     * @param int $lesseeEnterprise
     */
    public function setLesseeEnterprise($lesseeEnterprise)
    {
        $this->lesseeEnterprise = $lesseeEnterprise;
    }

    /**
     * @return int
     */
    public function getLesseeCustomer()
    {
        return $this->lesseeCustomer;
    }

    /**
     * @param int $lesseeCustomer
     */
    public function setLesseeCustomer($lesseeCustomer)
    {
        $this->lesseeCustomer = $lesseeCustomer;
    }

    /**
     * @return Sandbox\ApiBundle\Entity\Room\RoomBuilding
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @param Sandbox\ApiBundle\Entity\Room\RoomBuilding $buildingId
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;
    }

    /**
     * @return Sandbox\ApiBundle\Entity\Product\Product
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param Sandbox\ApiBundle\Entity\Product\Product $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return Sandbox\ApiBundle\Entity\Lease\LeaseClue
     */
    public function getLeaseClueId()
    {
        return $this->LeaseClueId;
    }

    /**
     * @param Sandbox\ApiBundle\Entity\Lease\LeaseClue $LeaseClueId
     */
    public function setLeaseClueId($LeaseClueId)
    {
        $this->LeaseClueId = $LeaseClueId;
    }

    /**
     * @return mixed
     */
    public function getLeaseOfferId()
    {
        return $this->LeaseOfferId;
    }

    /**
     * @param mixed $LeaseOfferId
     */
    public function setLeaseOfferId($LeaseOfferId)
    {
        $this->LeaseOfferId = $LeaseOfferId;
    }
}
