<?php

namespace Sandbox\ApiBundle\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * ProductAppointment.
 *
 * @ORM\Table(name="product_appointment")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Product\ProductAppointmentRepository")
 */
class ProductAppointment
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_WITHDRAWN = 'withdrawn';
    const APPOINTMENT_NUMBER_LETTER = 'Y';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "client_appointment_list"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="product_id", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $productId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Product\Product
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Serializer\Groups({"main", "client_appointment_list"})
     */
    private $product;

    /**
     * @var string
     *
     * @ORM\Column(name="applicant_name", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({"main", "client_appointment_detail"})
     */
    private $applicantName;

    /**
     * @var string
     *
     * @ORM\Column(name="applicant_company", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({"main", "client_appointment_detail"})
     */
    private $applicantCompany;

    /**
     * @var string
     * @Assert\Regex("/^\d+$/")
     *
     * @ORM\Column(name="applicant_phone", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({"main", "client_appointment_detail"})
     */
    private $applicantPhone;

    /**
     * @var string
     * @Assert\Email()
     *
     * @ORM\Column(name="applicant_email", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({"main", "client_appointment_detail"})
     */
    private $applicantEmail;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="start_rent_date", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main", "client_appointment_list"})
     */
    private $startRentDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_rent_date", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main", "client_appointment_list"})
     */
    private $endRentDate;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="rent_time_length", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "client_appointment_list"})
     */
    private $rentTimeLength;

    /**
     * @var string
     *
     * @ORM\Column(name="rent_time_unit", type="string", length=64, nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $rentTimeUnit;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64, nullable=false)
     *
     * @Serializer\Groups({"main", "client_appointment_list"})
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=2048, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="appointment_number", type="string", length=64)
     *
     * @Serializer\Groups({"main", "client_appointment_list"})
     */
    private $appointmentNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     *
     * @Serializer\Groups({"main", "client_appointment_list"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modification_date", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="update")
     *
     * @Serializer\Groups({"main", "admin_appointment"})
     */
    private $modificationDate;

    /**
     * @var mixed
     *
     * @Serializer\Groups({"main", "admin_appointment"})
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="rent_type", type="string", length=20, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $rentType;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"main", "client_appointment_detail"})
     */
    private $address;

    /**
     * @var int
     */
    private $profileId;

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
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return \DateTime
     */
    public function getEndRentDate()
    {
        return $this->endRentDate;
    }

    /**
     * @param \DateTime $endRentDate
     */
    public function setEndRentDate($endRentDate)
    {
        $this->endRentDate = $endRentDate;
    }

    /**
     * @return string
     */
    public function getAppointmentNumber()
    {
        return $this->appointmentNumber;
    }

    /**
     * @param string $appointmentNumber
     */
    public function setAppointmentNumber($appointmentNumber)
    {
        $this->appointmentNumber = $appointmentNumber;
    }

    /**
     * @return int
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * @param int $profileId
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return ProductAppointment
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set productId.
     *
     * @param int $productId
     *
     * @return ProductAppointment
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get productId.
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set applicantName.
     *
     * @param string $applicantName
     *
     * @return ProductAppointment
     */
    public function setApplicantName($applicantName)
    {
        $this->applicantName = $applicantName;

        return $this;
    }

    /**
     * Get applicantName.
     *
     * @return string
     */
    public function getApplicantName()
    {
        return $this->applicantName;
    }

    /**
     * Set applicantCompany.
     *
     * @param string $applicantCompany
     *
     * @return ProductAppointment
     */
    public function setApplicantCompany($applicantCompany)
    {
        $this->applicantCompany = $applicantCompany;

        return $this;
    }

    /**
     * Get applicantCompany.
     *
     * @return string
     */
    public function getApplicantCompany()
    {
        return $this->applicantCompany;
    }

    /**
     * Set applicantPhone.
     *
     * @param string $applicantPhone
     *
     * @return ProductAppointment
     */
    public function setApplicantPhone($applicantPhone)
    {
        $this->applicantPhone = $applicantPhone;

        return $this;
    }

    /**
     * Get applicantPhone.
     *
     * @return string
     */
    public function getApplicantPhone()
    {
        return $this->applicantPhone;
    }

    /**
     * Set applicantEmail.
     *
     * @param string $applicantEmail
     *
     * @return ProductAppointment
     */
    public function setApplicantEmail($applicantEmail)
    {
        $this->applicantEmail = $applicantEmail;

        return $this;
    }

    /**
     * Get applicantEmail.
     *
     * @return string
     */
    public function getApplicantEmail()
    {
        return $this->applicantEmail;
    }

    /**
     * Set startRentDate.
     *
     * @param \DateTime $startRentDate
     *
     * @return ProductAppointment
     */
    public function setStartRentDate($startRentDate)
    {
        $this->startRentDate = $startRentDate;

        return $this;
    }

    /**
     * Get startRentDate.
     *
     * @return \DateTime
     */
    public function getStartRentDate()
    {
        return $this->startRentDate;
    }

    /**
     * Set rentTimeLength.
     *
     * @param int $rentTimeLength
     *
     * @return ProductAppointment
     */
    public function setRentTimeLength($rentTimeLength)
    {
        $this->rentTimeLength = $rentTimeLength;

        return $this;
    }

    /**
     * Get rentTimeLength.
     *
     * @return int
     */
    public function getRentTimeLength()
    {
        return $this->rentTimeLength;
    }

    /**
     * Set rentTimeUnit.
     *
     * @param string $rentTimeUnit
     *
     * @return ProductAppointment
     */
    public function setRentTimeUnit($rentTimeUnit)
    {
        $this->rentTimeUnit = $rentTimeUnit;

        return $this;
    }

    /**
     * Get rentTimeUnit.
     *
     * @return string
     */
    public function getRentTimeUnit()
    {
        return $this->rentTimeUnit;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return ProductAppointment
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return ProductAppointment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return ProductAppointment
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
     * @return ProductAppointment
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
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getRentType()
    {
        return $this->rentType;
    }

    /**
     * @param string $rentType
     */
    public function setRentType($rentType)
    {
        $this->rentType = $rentType;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }
}
