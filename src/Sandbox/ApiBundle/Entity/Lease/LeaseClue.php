<?php

namespace Sandbox\ApiBundle\Entity\Lease;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="lease_clues")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Lease\LeaseClueRepository")
 */
class LeaseClue
{
    const LEASE_CLUE_LETTER_HEAD = 'LC';

    const LEASE_CLUE_STATUS_CLUE = 'clue';
    const LEASE_CLUE_STATUS_OFFER = 'offer';
    const LEASE_CLUE_STATUS_CONTRACT = 'contract';
    const LEASE_CLUE_STATUS_CLOSED = 'closed';

    const KEYWORD_NUMBER = 'number';
    const KEYWORD_CUSTOMER_PHONE = 'customer_phone';
    const KEYWORD_CUSTOMER_NAME = 'customer_name';
    const KEYWORD_ROOM_NAME = 'room_name';

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

    private $building;

    /**
     * @var Sandbox\ApiBundle\Entity\Product\Product
     *
     * @ORM\Column(name="product_id",type="integer", nullable=true)
     */
    private $productId;

    private $product;

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
     * @var int
     *
     * @ORM\Column(name="lessee_customer", type="integer", length=20)
     */
    private $lesseeCustomer;

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
     * @var int
     *
     * @ORM\Column(name="cycle", type="integer", nullable=true)
     */
    private $cycle;

    /**
     * @var float
     *
     * @ORM\Column(name="monthly_rent", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $monthlyRent;

    /**
     * @var int
     *
     * @ORM\Column(name="number", type="integer", nullable=true)
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=15)
     */
    private $status;

    /**
     * @var Sandbox\ApiBundle\Entity\Product\ProductAppointment
     *
     * @ORM\Column(name="product_appointment_id", type="integer", nullable=true)
     */
    private $productAppointmentId;

    private $productAppointment;

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

    private $customer;

    /**
     * @var int
     *
     * @ORM\Column(name="company_id", type="integer")
     */
    private $companyId;

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
     * @return int
     */
    public function getCycle()
    {
        return $this->cycle;
    }

    /**
     * @param int $cycle
     */
    public function setCycle($cycle)
    {
        $this->cycle = $cycle;
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
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
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
     * @return Sandbox\ApiBundle\Entity\Product\ProductAppointment
     */
    public function getProductAppointmentId()
    {
        return $this->productAppointmentId;
    }

    /**
     * @param Sandbox\ApiBundle\Entity\Product\ProductAppointment $productAppointmentId
     */
    public function setProductAppointmentId($productAppointmentId)
    {
        $this->productAppointmentId = $productAppointmentId;
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
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return mixed
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param mixed $building
     */
    public function setBuilding($building)
    {
        $this->building = $building;
    }

    /**
     * @return mixed
     */
    public function getProductAppointment()
    {
        return $this->productAppointment;
    }

    /**
     * @param mixed $productAppointment
     */
    public function setProductAppointment($productAppointment)
    {
        $this->productAppointment = $productAppointment;
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
}
