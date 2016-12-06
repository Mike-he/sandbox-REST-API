<?php

namespace Sandbox\ApiBundle\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(name="userId", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="productId", type="integer", nullable=false)
     */
    private $productId;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="applicantName", type="string", length=255, nullable=false)
     */
    private $applicantName;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="applicantCompany", type="string", length=255, nullable=false)
     */
    private $applicantCompany;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Regex("/^\d+$/")
     *
     * @ORM\Column(name="applicantPhone", type="string", length=255, nullable=false)
     */
    private $applicantPhone;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     *
     * @ORM\Column(name="applicantEmail", type="string", length=255, nullable=false)
     */
    private $applicantEmail;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="startRentDate", type="datetime", nullable=false)
     */
    private $startRentDate;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="rentTimeLength", type="integer", nullable=false)
     */
    private $rentTimeLength;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="rentTimeUnit", type="string", length=64, nullable=false)
     */
    private $rentTimeUnit;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64, nullable=false)
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=2048, nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     */
    private $modificationDate;

    /**
     * @var mixed
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="rent_type", type="string", length=20, nullable=true)
     */
    private $rentType;

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
     * ProductAppointment constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
        $this->modificationDate = new \DateTime('now');
    }
}
