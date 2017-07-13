<?php

namespace Sandbox\ApiBundle\Entity\Lease;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="lease_offer")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Lease\LeaseOfferRepository")
 */
class LeaseOffer
{
    const LEASE_OFFER_LETTER_HEAD = 'LO';

    const LEASE_OFFER_STATUS_OFFER = 'offer';
    const LEASE_OFFER_STATUS_CONTRACT = 'contract';
    const LEASE_OFFER_STATUS_CLOSED = 'closed';

    const LEASE_OFFER_LESSEE_TYPE_ENTERPRISE = 'enterprise';
    const LEASE_OFFER_LESSEE_TYPE_PERSONAL = 'personal';

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
     * @ORM\Column(name="serial_number", type="string", length=50)
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
     * @var string 出租方名称
     *
     * @ORM\Column(name="lessor_name", type="string", length=40, nullable=true)
     */
    private $lessorName;

    /**
     * @var string 出租方地址
     *
     * @ORM\Column(name="lessor_address", type="string", length=255, nullable=true)
     */
    private $lessorAddress;

    /**
     * @var string 出租方联系人
     *
     * @ORM\Column(name="lessor_contact", type="string", length=20, nullable=true)
     */
    private $lessorContact;

    /**
     * @var string 出租方电话
     *
     * @ORM\Column(name="lessor_phone", type="string", length=128, nullable=true)
     */
    private $lessorPhone;

    /**
     * @var string 出租方邮箱
     *
     * @ORM\Column(name="lessor_email", type="string", length=128, nullable=true)
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
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @var float 月租金
     *
     * @ORM\Column(name="monthly_rent", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $monthlyRent;

    /**
     * @var float 合同总租金
     *
     * @ORM\Column(name="total_rent", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $totalRent;

    /**
     * @var float 租赁押金
     *
     * @ORM\Column(name="deposit", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $deposit;

    /**
     * @var LeaseRentTypes
     *
     * @ORM\ManyToMany(targetEntity="LeaseRentTypes")
     * @ORM\JoinTable(
     *      name="lease_offer_has_rent_types",
     *      joinColumns={@ORM\JoinColumn(name="lease_offer_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="lease_rent_types_id", referencedColumnName="id")}
     * )
     */
    private $leaseRentTypes;

    /**
     * @var string 房屋使用用途
     *
     * @ORM\Column(name="purpose", type="text", nullable=true)
     */
    private $purpose;

    /**
     * @var string 其他费用说明
     *
     * @ORM\Column(name="other_expenses", type="text", nullable=true)
     */
    private $otherExpenses;

    /**
     * @var string 补充条款
     *
     * @ORM\Column(name="supplementary_terms", type="text", nullable=true)
     */
    private $supplementaryTerms;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=15)
     */
    private $status;

    /**
     * @var Sandbox\ApiBundle\Entity\Lease\LeaseClue
     *
     * @ORM\Column(name="lease_clue_id", type="integer", nullable=true)
     */
    private $LeaseClueId;

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

    public function __construct()
    {
        $this->leaseRentTypes = new ArrayCollection();
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
}
