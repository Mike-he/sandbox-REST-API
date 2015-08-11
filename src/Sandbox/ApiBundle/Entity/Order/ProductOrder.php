<?php

namespace Sandbox\ApiBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ProductOrder.
 *
 * @ORM\Table(name="ProductOrder")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Order\OrderRepository")
 */
class ProductOrder
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="orderNumber", type="string")
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $orderNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="productId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $productId;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal")
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="discountPrice", type="decimal")
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $discountPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64)
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $status;

    /**
     * @var InvitedPeople
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Order\InvitedPeople",
     *      mappedBy="orderId"
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="orderId")
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $invitedPeople;

    /**
     * @var int
     *
     *
     * @ORM\Column(name="appointedPerson", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $appointed;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $location;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime")
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime")
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $endDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="paymentDate", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $paymentDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cancelledDate", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $cancelledDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $modificationDate;

    /**
     * @var \Sandbox\ApiBundle\Entity\Product\Product
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Product\Product")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $product;

    /**
     * @var bool
     *
     * @ORM\Column(name="isRenew", type="boolean")
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $isRenew = false;

    /**
     * Set isRenew.
     *
     * @param bool $isRenew
     *
     * @return ProductOrder
     */
    public function setIsRenew($isRenew)
    {
        $this->isRenew = $isRenew;

        return $this;
    }

    /**
     * Get isRenew.
     *
     * @return bool
     */
    public function getIsRenew()
    {
        return $this->isRenew;
    }

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
     * @return ProductOrder
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
     * @return ProductOrder
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
     * Set product.
     *
     * @param Product $product
     *
     * @return ProductOrder
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get Product.
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set startDate.
     *
     * @param \DateTime $startDate
     *
     * @return ProductOrder
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate.
     *
     * @param \DateTime $endDate
     *
     * @return ProductOrder
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate.
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set price.
     *
     * @param string $price
     *
     * @return ProductOrder
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set discountPrice.
     *
     * @param string $discountPrice
     *
     * @return ProductOrder
     */
    public function setDiscountPrice($discountPrice)
    {
        $this->discountPrice = $discountPrice;

        return $this;
    }

    /**
     * Get discountPrice.
     *
     * @return string
     */
    public function getDiscountPrice()
    {
        return $this->discountPrice;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return ProductOrder
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
     * Set paymentDate.
     *
     * @param \DateTime $paymentDate
     *
     * @return ProductOrder
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
     * Set cancelledDate.
     *
     * @param \DateTime $cancelledDate
     *
     * @return ProductOrder
     */
    public function setCancelledDate($cancelledDate)
    {
        $this->cancelledDate = $cancelledDate;

        return $this;
    }

    /**
     * Get cancelledDate.
     *
     * @return \DateTime
     */
    public function getCancelledDate()
    {
        return $this->cancelledDate;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return ProductOrder
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
     * @return ProductOrder
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
     * Set invitedPeople.
     *
     * @param InvitedPeople $invitedPeople
     *
     * @return ProductOrder
     */
    public function setInvitedPeople($invitedPeople)
    {
        $this->invitedPeople = $invitedPeople;

        return $this;
    }

    /**
     * Get invitedPeople.
     *
     * @return InvitedPeople
     */
    public function getInvitedPeople()
    {
        return $this->invitedPeople;
    }

    /**
     * Set appointed.
     *
     * @param int $userId
     *
     * @return ProductOrder
     */
    public function setAppointed($userId)
    {
        $this->appointed = $userId;

        return $this;
    }

    /**
     * Get appointed.
     *
     * @return int
     */
    public function getAppointed()
    {
        return $this->appointed;
    }

    /**
     * Set location.
     *
     * @param string $location
     *
     * @return ProductOrder
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set orderNumber.
     *
     * @param string $orderNumber
     *
     * @return ProductOrder
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

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }
}
