<?php

namespace Sandbox\ApiBundle\Entity\Reservation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Reservation.
 *
 * @ORM\Table(name="reservation")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Reservation\ReservationRepository")
 */
class Reservation
{
    const UNGRABED = 'ungrabed';
    const GRABED = 'grabbed';
    /**
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
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="product_id", type="integer")
     */
    private $productId;

    /**
     * @var int
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
     * @ORM\Column(name="modification_date", type="datetime", nullable=true)
     */
    private $modificationDate = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="grab_date", type="datetime", nullable=true)
     *
     */
    private $grabDate;

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
     * Set userId.
     *
     * @param int $userId
     *
     * @return Reservation
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
     * @return Reservation
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
     * Set adminId.
     *
     * @param int $adminId
     *
     * @return Reservation
     */
    public function setAdminId($adminId)
    {
        $this->adminId = $adminId;

        return $this;
    }

    /**
     * Get adminId.
     *
     * @return int
     */
    public function getAdminId()
    {
        return $this->adminId;
    }

    /**
     * Set viewTime.
     *
     * @param \DateTime $viewTime
     *
     * @return Reservation
     */
    public function setViewTime($viewTime)
    {
        $this->viewTime = $viewTime;

        return $this;
    }

    /**
     * Get viewTime.
     *
     * @return \DateTime
     */
    public function getViewTime()
    {
        return $this->viewTime;
    }

    /**
     * Set contectName.
     *
     * @param string $contectName
     *
     * @return Reservation
     */
    public function setContectName($contectName)
    {
        $this->contectName = $contectName;

        return $this;
    }

    /**
     * Get contectName.
     *
     * @return string
     */
    public function getContectName()
    {
        return $this->contectName;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return Reservation
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return Reservation
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
     * @return Reservation
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
     * @return Reservation
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
     * @return Reservation
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
     * Set grabDate.
     *
     * @param \DateTime $grabDate
     *
     * @return Reservation
     */
    public function setGrabDate($grabDate)
    {
        $this->grabDate = $grabDate;

        return $this;
    }

    /**
     * Get grabDate.
     *
     * @return \DateTime
     */
    public function getGrabDate()
    {
        return $this->grabDate;
    }

}
