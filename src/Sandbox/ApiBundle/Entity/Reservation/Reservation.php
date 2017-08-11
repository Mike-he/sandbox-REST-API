<?php

namespace Sandbox\ApiBundle\Entity\Reservation;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\ManyToOne;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Reservation
 *
 * @ORM\Table(name="reservation")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Reservation\ReservationRepository")
 */
class Reservation
{
    const UNGRABED = 'ungrabed';
    const GRABED = 'grabed';
    /**
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="serial_number", type="string", length=255)
     */
    private $serialNumber;


    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="product_id", type="integer")
     */
    private $productId;

    /**
     * @var integer
     *
     * @ORM\Column(name="admin_id", type="integer", nullable=true)
     */
    private $adminId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="view_time", type="string", length=64)
     */
    private $viewTime;

    /**
     * @var string
     *
     * @ORM\Column(name="contect_name", type="string", length=64)
     */
    private $contectName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=32)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16)
     */
    private $status = self::UNGRABED;


    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=255)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modification_date", type="datetime", nullable=false)
     */
    private $modificationDate;

    /**
     * @var array
     */
    private $prductInfo;

    /**
     * Get id
     *
     * @return integer 
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
     * Set userId
     *
     * @param integer $userId
     * @return Reservation
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set productId
     *
     * @param integer $productId
     * @return Reservation
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get productId
     *
     * @return integer 
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set adminId
     *
     * @param integer $adminId
     * @return Reservation
     */
    public function setAdminId($adminId)
    {
        $this->adminId = $adminId;

        return $this;
    }

    /**
     * Get adminId
     *
     * @return integer 
     */
    public function getAdminId()
    {
        return $this->adminId;
    }

    /**
     * Set viewTime
     *
     * @param \DateTime $viewTime
     * @return Reservation
     */
    public function setViewTime($viewTime)
    {
        $this->viewTime = $viewTime;

        return $this;
    }

    /**
     * Get viewTime
     *
     * @return \DateTime 
     */
    public function getViewTime()
    {
        return $this->viewTime;
    }

    /**
     * Set contectName
     *
     * @param string $contectName
     * @return Reservation
     */
    public function setContectName($contectName)
    {
        $this->contectName = $contectName;

        return $this;
    }

    /**
     * Get contectName
     *
     * @return string 
     */
    public function getContectName()
    {
        return $this->contectName;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Reservation
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Reservation
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Reservation
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Reservation
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime 
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set modificationDate
     *
     * @param \DateTime $modificationDate
     * @return Reservation
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate
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
    public function getPrductInfo()
    {
        return $this->prductInfo;
    }

    /**
     * @param mixed $prductInfo
     */
    public function setPrductInfo($prductInfo)
    {
        $this->prductInfo = $prductInfo;
    }

}
