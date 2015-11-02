<?php

namespace Sandbox\ApiBundle\Entity\Food;

use Doctrine\ORM\Mapping as ORM;

/**
 * FoodOrder.
 *
 * @ORM\Table(name="FoodOrder")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Food\FoodOrderRepository")
 */
class FoodOrder
{
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PAID = 'paid';

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
     * @ORM\Column(name="userId", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="orderNumber", type="string", length=255)
     */
    private $orderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="totalPrice", type="decimal", precision=10, scale=2)
     */
    private $totalPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64)
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="cityId", type="integer")
     */
    private $cityId;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer")
     */
    private $buildingId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
     */
    private $modificationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="paymentDate", type="datetime", nullable=true)
     */
    private $paymentDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cancellationDate", type="datetime", nullable=true)
     */
    private $cancellationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="foodInfo", type="text")
     */
    private $foodInfo;

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
     * @return FoodOrder
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
     * Set orderNumber.
     *
     * @param string $orderNumber
     *
     * @return FoodOrder
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * Get orderNumber.
     *
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * Set totalPrice.
     *
     * @param string $totalPrice
     *
     * @return FoodOrder
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * Get totalPrice.
     *
     * @return string
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return FoodOrder
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
     * Set cityId.
     *
     * @param int $cityId
     *
     * @return FoodOrder
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * Get cityId.
     *
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * Set buildingId.
     *
     * @param int $buildingId
     *
     * @return FoodOrder
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;

        return $this;
    }

    /**
     * Get buildingId.
     *
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return FoodOrder
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
     * @return FoodOrder
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
     * Set paymentDate.
     *
     * @param \DateTime $paymentDate
     *
     * @return FoodOrder
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    /**
     * Get paymentDate.
     *
     * @return \DateTime
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * Set cancellationDate.
     *
     * @param \DateTime $cancellationDate
     *
     * @return FoodOrder
     */
    public function setCancellationDate($cancellationDate)
    {
        $this->cancellationDate = $cancellationDate;

        return $this;
    }

    /**
     * Get cancellationDate.
     *
     * @return \DateTime
     */
    public function getCancellationDate()
    {
        return $this->cancellationDate;
    }

    /**
     * Set foodInfo.
     *
     * @param string $foodInfo
     *
     * @return FoodOrder
     */
    public function setFoodInfo($foodInfo)
    {
        $this->foodInfo = $foodInfo;

        return $this;
    }

    /**
     * Get foodInfo.
     *
     * @return string
     */
    public function getFoodInfo()
    {
        return $this->foodInfo;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
        $this->setStatus(self::STATUS_UNPAID);
    }
}
