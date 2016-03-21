<?php

namespace Sandbox\ApiBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sandbox\ApiBundle\Entity\User\User;

/**
 * ProductOrder.
 *
 * @ORM\Table(name="ProductOrder")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Order\OrderRepository")
 */
class ProductOrder
{
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PAID = 'paid';
    const STATUS_UNPAID = 'unpaid';
    const STATUS_COMPLETED = 'completed';

    const ACTION_TYPE = 'product_order';
    const ACTION_INVITE_ADD = 'invite_add';
    const ACTION_APPOINT_ADD = 'appoint_add';
    const ACTION_INVITE_REMOVE = 'invite_remove';
    const ACTION_APPOINT_REMOVE = 'appoint_remove';
    const ACTION_START = 'start';
    const ACTION_END = 'end';
    const LETTER_HEAD = 'P';

    const RESERVE_TYPE = 'reserve';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "client", "admin_detail", "current_order"})
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
     * @var string
     *
     * @ORM\Column(name="payChannel", type="string", length=16, nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $payChannel;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_detail", "current_order"})
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="productId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $productId;

    /**
     * @var int
     *
     * @ORM\Column(name="ruleId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $ruleId;

    /**
     * @var int
     *
     * @ORM\Column(name="membershipBindId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $membershipBindId;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2)
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="discountPrice", type="decimal", precision=10, scale=2)
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
     * @var string
     *
     * @ORM\Column(name="ruleName", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $ruleName;

    /**
     * @var string
     *
     * @ORM\Column(name="ruleDescription", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $ruleDescription;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime")
     *
     * @Serializer\Groups({"main", "client", "admin_detail", "current_order"})
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime")
     *
     * @Serializer\Groups({"main", "client", "admin_detail", "current_order"})
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
     * @Serializer\Groups({"main", "client", "admin_detail", "current_order"})
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
     * @var string
     *
     * @ORM\Column(name="productInfo", type="text", nullable=false)
     *
     * @Serializer\Groups({"main", "client", "admin_detail", "admin_order"})
     */
    private $productInfo;

    /**
     * @var int
     *
     * @ORM\Column(name="adminId", type="integer", nullable=true)
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $adminId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=true)
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $type;

    /**
     * @var int
     */
    private $rentPeriod;

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
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return ProductOrder
     */
    public function setUser($user)
    {
        $this->user = $user;
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
     * Set ruleId.
     *
     * @param int $ruleId
     *
     * @return ProductOrder
     */
    public function setRuleId($ruleId)
    {
        $this->ruleId = $ruleId;

        return $this;
    }

    /**
     * Get ruleId.
     *
     * @return int
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * Set membershipBindId.
     *
     * @param int $membershipBindId
     *
     * @return ProductOrder
     */
    public function setMembershipBindId($membershipBindId)
    {
        $this->membershipBindId = $membershipBindId;

        return $this;
    }

    /**
     * Get membershipBindId.
     *
     * @return int
     */
    public function getMembershipBindId()
    {
        return $this->membershipBindId;
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
     * Set ruleName.
     *
     * @param string $ruleName
     *
     * @return ProductOrder
     */
    public function setRuleName($ruleName)
    {
        $this->ruleName = $ruleName;

        return $this;
    }

    /**
     * Get ruleName.
     *
     * @return string
     */
    public function getRuleName()
    {
        return $this->ruleName;
    }

    /**
     * Set ruleDescription.
     *
     * @param string $ruleDescription
     *
     * @return ProductOrder
     */
    public function setRuleDescription($ruleDescription)
    {
        $this->ruleDescription = $ruleDescription;

        return $this;
    }

    /**
     * Get ruleDescription.
     *
     * @return string
     */
    public function getRuleDescription()
    {
        return $this->ruleDescription;
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
     * Set productInfo.
     *
     * @param string $productInfo
     *
     * @return ProductOrder
     */
    public function setProductInfo($productInfo)
    {
        $this->productInfo = $productInfo;

        return $this;
    }

    /**
     * Get productInfo.
     *
     * @return string
     */
    public function getProductInfo()
    {
        return $this->productInfo;
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
     * Set payChannel.
     *
     * @param string $payChannel
     *
     * @return ProductOrder
     */
    public function setPayChannel($payChannel)
    {
        $this->payChannel = $payChannel;

        return $this;
    }

    /**
     * Get payChannel.
     *
     * @return string
     */
    public function getPayChannel()
    {
        return $this->payChannel;
    }

    /**
     * Set adminId.
     *
     * @param int $adminId
     *
     * @return ProductOrder
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
     * Set type.
     *
     * @param string $type
     *
     * @return ProductOrder
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set rentPeriod.
     *
     * @param int $rentPeriod
     *
     * @return ProductOrder
     */
    public function setRentPeriod($rentPeriod)
    {
        $this->rentPeriod = $rentPeriod;

        return $this;
    }

    /**
     * Get rentPeriod.
     *
     * @return int
     */
    public function getRentPeriod()
    {
        return $this->rentPeriod;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }
}
