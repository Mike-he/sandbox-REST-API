<?php

namespace Sandbox\ApiBundle\Entity\Expert;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ExpertOrder.
 *
 * @ORM\Table(name = "expert_order")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Expert\ExpertOrderRepository")
 */
class ExpertOrder
{
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

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
     * @ORM\Column(name="expert_id", type="integer")
     */
    private $expertId;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="order_number", type="string", length=64)
     */
    private $orderNumber;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="completed_date", type="datetime", nullable=true)
     */
    private $completedDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cancelled_date", type="datetime", nullable=true)
     */
    private $cancelledDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime", nullable=true)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modification_date", type="datetime", nullable=true)
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
     * @return int
     */
    public function getExpertId()
    {
        return $this->expertId;
    }

    /**
     * @param int $expertId
     */
    public function setExpertId($expertId)
    {
        $this->expertId = $expertId;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
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
     * @return \DateTime
     */
    public function getCompletedDate()
    {
        return $this->completedDate;
    }

    /**
     * @param \DateTime $completedDate
     */
    public function setCompletedDate($completedDate)
    {
        $this->completedDate = $completedDate;
    }

    /**
     * @return \DateTime
     */
    public function getCancelledDate()
    {
        return $this->cancelledDate;
    }

    /**
     * @param \DateTime $cancelledDate
     */
    public function setCancelledDate($cancelledDate)
    {
        $this->cancelledDate = $cancelledDate;
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
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
}
