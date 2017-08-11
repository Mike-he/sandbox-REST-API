<?php

namespace Sandbox\ApiBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\User\User;

/**
 * ProductOrder.
 *
 * @ORM\Table(name="product_order")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Order\OrderRepository")
 */
class ProductOrder
{
    const COMBINE_STATUS_ALL = 'all';
    const COMBINE_STATUS_PENDING = 'pending';
    const COMBINE_STATUS_REFUND = 'refund';
    const COMBINE_STATUS_INCOMPLETE = 'incomplete';

    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PAID = 'paid';
    const STATUS_UNPAID = 'unpaid';
    const STATUS_COMPLETED = 'completed';

    const CHANNEL_ACCOUNT = 'account';
    const CHANNEL_ALIPAY = 'alipay';
    const CHANNEL_UNIONPAY = 'upacp';
    const CHANNEL_WECHAT = 'wx';
    const CHANNEL_FOREIGN_CREDIT = 'cnp_f';
    const CHANNEL_UNION_CREDIT = 'cnp_u';
    const CHANNEL_WECHAT_PUB = 'wx_pub';
    const CHANNEL_OFFLINE = 'offline';
    const CHANNEL_SALES_OFFLINE = 'sales_offline';

    const PRODUCT_MAP = 'product';

    const ACTION_TYPE = 'product_order';
    const ACTION_INVITE_ADD = 'invite_add';
    const ACTION_APPOINT_ADD = 'appoint_add';
    const ACTION_INVITE_REMOVE = 'invite_remove';
    const ACTION_APPOINT_REMOVE = 'appoint_remove';
    const ACTION_START = 'start';
    const ACTION_END = 'end';
    const ACTION_ACCEPTED = 'accepted';
    const ACTION_REJECTED = 'rejected';
    const ACTION_OFFICE_ORDER = 'office_order';
    const ACTION_CHANGE_PRICE = 'change_price';
    const ACTION_CANCELLED = 'cancelled';
    const ACTION_RETURNED = 'returned';
    const LETTER_HEAD = 'P';
    const ENTITY_PATH = 'Order\ProductOrder';
    const REFUNDED_STATUS = 'refunded';
    const NEED_TO_REFUND = 'needToRefund';
    const ALL_REFUND = 'all';

    const RESERVE_TYPE = 'reserve';
    const PREORDER_TYPE = 'preorder';
    const OWN_TYPE = 'own';

    const PAYMENT_SUBJECT = 'SANDBOX3-预定房间';
    const PAYMENT_BODY = 'ROOM ORDER';

    const REFUND_TO_ACCOUNT = 'account';
    const REFUND_TO_ORIGIN = 'origin';

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
     * @ORM\Column(name="userId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_detail", "current_order", "client"})
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({"main", "admin_detail"})
     **/
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="payment_user_id", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_detail", "current_order", "client"})
     */
    private $paymentUserId;

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
    private $status = self::STATUS_UNPAID;

    /**
     * @var InvitedPeople
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Order\InvitedPeople",
     *      mappedBy="orderId"
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="orderId")
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
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
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $appointedName;

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
     * @var ProductOrderInfo
     *
     * @ORM\OneToOne(
     *      targetEntity="Sandbox\ApiBundle\Entity\Order\ProductOrderInfo",
     *      mappedBy="order"
     * )
     *
     * @Serializer\Exclude
     */
    private $productInfo;

    /**
     * @var int
     *
     * @ORM\Column(name="adminId", type="integer", nullable=true)
     * @Serializer\Groups({"main", "admin_detail", "client_order", "admin_order"})
     */
    private $adminId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=true)
     * @Serializer\Groups({"main", "admin_detail", "client_order", "admin_order", "client"})
     */
    private $type;

    /**
     * @var bool
     *
     * @ORM\Column(name="rejected", type="boolean", options={"default": false})
     * @Serializer\Groups({"main", "admin_detail", "client_order", "admin_order", "client"})
     */
    private $rejected = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="needToRefund", type="boolean", options={"default": false})
     * @Serializer\Groups({"main", "admin_detail", "client_order", "admin_order", "client"})
     */
    private $needToRefund = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="refunded", type="boolean", options={"default": false})
     * @Serializer\Groups({"main", "admin_detail", "client_order", "admin_order", "client"})
     */
    private $refunded = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="refundProcessed", type="boolean", options={"default": false})
     * @Serializer\Groups({"main", "admin_detail", "client_order", "admin_order", "client"})
     */
    private $refundProcessed = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="refundProcessedDate", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_detail", "admin_order", "client"})
     */
    private $refundProcessedDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="invoiced", type="boolean", options={"default": false})
     * @Serializer\Groups({"main", "admin_detail", "client_order", "admin_order" ,"client"})
     */
    private $invoiced = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="salesInvoice", type="boolean", options={"default": false})
     * @Serializer\Groups({"main", "admin_detail", "admin_order"})
     */
    private $salesInvoice = false;

    /**
     * @var string
     *
     * @ORM\Column(name="refundUrl", type="text", nullable=true)
     * @Serializer\Groups({"main", "admin_detail", "client_order", "admin_order"})
     */
    private $refundUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="refundSSN", type="string", nullable=true)
     * @Serializer\Groups({"main", "admin_detail", "admin_order", "client_order"})
     */
    private $refundSSN;

    /**
     * @var int
     */
    private $rentPeriod;

    /**
     * @var string
     *
     * @ORM\Column(name="actualRefundAmount", type="decimal", precision=10, scale=3, nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_detail", "admin_order", "client_order"})
     */
    private $actualRefundAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="refundProcessFee", type="decimal", precision=10, scale=3, nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_detail", "admin_order", "client_order"})
     */
    private $refundProcessFee;

    /**
     * @var string
     *
     * @ORM\Column(name="refundComment", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_detail", "admin_order"})
     */
    private $refundComment;

    /**
     * @var bool
     *
     * @ORM\Column(name="cancelByUser", type="boolean", options={"default": false})
     * @Serializer\Groups({"main", "admin_detail", "client_order", "admin_order" ,"client"})
     */
    private $cancelByUser = false;

    /**
     * @var OrderOfflineTransfer
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Order\OrderOfflineTransfer",
     *      mappedBy="order"
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="orderId")
     *
     * @Serializer\Groups({"main", "current_order", "admin_detail", "client_order", "admin_order" ,"client"})
     */
    private $transfer;

    /**
     * @var bool
     *
     * @ORM\Column(name="has_evaluated", type="boolean")
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $hasEvaluated = false;

    /**
     * @var string
     *
     * @ORM\Column(name="refundTo", type="string", length=16, nullable=true)
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $refundTo;

    /**
     * @var int
     *
     * @ORM\Column(name="seatId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "current_order", "admin_detail", "client_order", "admin_order" ,"client"})
     */
    private $seatId;

    /**
     * @var float
     *
     * @ORM\Column(name="service_fee", type="float", precision=6, scale=3, options={"default": 0})
     */
    private $serviceFee = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="edit_comment", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_detail", "admin_order", "client"})
     */
    private $editComment;

    /**
     * @var int
     *
     * @ORM\Column(name="edit_admin_id", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_detail", "admin_order"})
     */
    private $editAdminId;

    /**
     * @var bool
     *
     * @ORM\Column(name="tip", type="boolean")
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $tip = false;

    /**
     * @var string
     *
     * @ORM\Column(name="base_price", type="decimal", precision=10, scale=2)
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $basePrice;

    /**
     * @var string
     *
     * @ORM\Column(name="unit_price", type="string")
     *
     * @Serializer\Groups({"main", "client", "admin_detail"})
     */
    private $unitPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_detail", "admin_order"})
     */
    private $customerId;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("product_info")
     * @Serializer\Groups({"main", "client", "admin_detail", "admin_order", "client_evaluation"})
     */
    public function degenerateProductInfo()
    {
        return $this->productInfo ? $this->productInfo->getProductInfo() : null;
    }

    /**
     * @return int
     */
    public function getEditAdminId()
    {
        return $this->editAdminId;
    }

    /**
     * @param int $editAdminId
     */
    public function setEditAdminId($editAdminId)
    {
        $this->editAdminId = $editAdminId;
    }

    /**
     * @return string
     */
    public function getEditComment()
    {
        return $this->editComment;
    }

    /**
     * @param string $editComment
     */
    public function setEditComment($editComment)
    {
        $this->editComment = $editComment;
    }

    /**
     * @return float
     */
    public function getServiceFee()
    {
        return $this->serviceFee;
    }

    /**
     * @param float $serviceFee
     */
    public function setServiceFee($serviceFee)
    {
        $this->serviceFee = $serviceFee;
    }

    /**
     * Set seatId.
     *
     * @param int $seatId
     *
     * @return ProductOrder
     */
    public function setSeatId($seatId)
    {
        $this->seatId = $seatId;

        return $this;
    }

    /**
     * Get seatId.
     *
     * @return int
     */
    public function getSeatId()
    {
        return $this->seatId;
    }

    /**
     * Set refundTo.
     *
     * @param string $refundTo
     *
     * @return ProductOrder
     */
    public function setRefundTo($refundTo)
    {
        $this->refundTo = $refundTo;

        return $this;
    }

    /**
     * Get refundTo.
     *
     * @return string
     */
    public function getRefundTo()
    {
        return $this->refundTo;
    }

    /**
     * Set transfer.
     *
     * @param OrderOfflineTransfer $transfer
     *
     * @return ProductOrder
     */
    public function setTransfer($transfer)
    {
        $this->transfer = $transfer;

        return $this;
    }

    /**
     * Get transfer.
     *
     * @return OrderOfflineTransfer
     */
    public function getTransfer()
    {
        return $this->transfer;
    }

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
     * @return int
     */
    public function getPaymentUserId()
    {
        return $this->paymentUserId;
    }

    /**
     * @param int $paymentUserId
     */
    public function setPaymentUserId($paymentUserId)
    {
        $this->paymentUserId = $paymentUserId;
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
     * Set refundProcessedDate.
     *
     * @param \DateTime $refundProcessedDate
     *
     * @return ProductOrder
     */
    public function setRefundProcessedDate($refundProcessedDate)
    {
        $this->refundProcessedDate = $refundProcessedDate;

        return $this;
    }

    /**
     * Get refundProcessedDate.
     *
     * @return \DateTime
     */
    public function getRefundProcessedDate()
    {
        return $this->refundProcessedDate;
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
     * Set appointedName.
     *
     * @param string $name
     *
     * @return ProductOrder
     */
    public function setAppointedName($name)
    {
        $this->appointedName = $name;

        return $this;
    }

    /**
     * Get appointedName.
     *
     * @return string
     */
    public function getAppointedName()
    {
        return $this->appointedName;
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
        return $this->productInfo ? $this->productInfo->getProductInfo() : null;
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
     * Set rejected.
     *
     * @param bool $rejected
     *
     * @return ProductOrder
     */
    public function setRejected($rejected)
    {
        $this->rejected = $rejected;

        return $this;
    }

    /**
     * Get rejected.
     *
     * @return bool
     */
    public function isRejected()
    {
        return $this->rejected;
    }

    /**
     * Set needToRefund.
     *
     * @param bool $needToRefund
     *
     * @return ProductOrder
     */
    public function setNeedToRefund($needToRefund)
    {
        $this->needToRefund = $needToRefund;

        return $this;
    }

    /**
     * Get needToRefund.
     *
     * @return bool
     */
    public function isNeedToRefund()
    {
        return $this->needToRefund;
    }

    /**
     * Set refundProcessed.
     *
     * @param bool $refundProcessed
     *
     * @return ProductOrder
     */
    public function setRefundProcessed($refundProcessed)
    {
        $this->refundProcessed = $refundProcessed;

        return $this;
    }

    /**
     * Get refundProcessed.
     *
     * @return bool
     */
    public function isRefundProcessed()
    {
        return $this->refundProcessed;
    }

    /**
     * Set refunded.
     *
     * @param bool $refunded
     *
     * @return ProductOrder
     */
    public function setRefunded($refunded)
    {
        $this->refunded = $refunded;

        return $this;
    }

    /**
     * Get refunded.
     *
     * @return bool
     */
    public function isRefunded()
    {
        return $this->refunded;
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

    /**
     * Set invoiced.
     *
     * @param bool $invoiced
     *
     * @return ProductOrder
     */
    public function setInvoiced($invoiced)
    {
        $this->invoiced = $invoiced;

        return $this;
    }

    /**
     * Get invoiced.
     *
     * @return bool
     */
    public function isInvoiced()
    {
        return $this->invoiced;
    }

    /**
     * Set refundUrl.
     *
     * @param string $refundUrl
     *
     * @return ProductOrder
     */
    public function setRefundUrl($refundUrl)
    {
        $this->refundUrl = $refundUrl;

        return $this;
    }

    /**
     * Get refundUrl.
     *
     * @return string
     */
    public function getRefundUrl()
    {
        return $this->refundUrl;
    }

    /**
     * Set refundSSN.
     *
     * @param string $refundSSN
     *
     * @return ProductOrder
     */
    public function setRefundSSN($refundSSN)
    {
        $this->refundSSN = $refundSSN;

        return $this;
    }

    /**
     * Get refundSSN.
     *
     * @return string
     */
    public function getRefundSSN()
    {
        return $this->refundSSN;
    }

    /**
     * Set actualRefundAmount.
     *
     * @param string $actualRefundAmount
     *
     * @return ProductOrder
     */
    public function setActualRefundAmount($actualRefundAmount)
    {
        $this->actualRefundAmount = $actualRefundAmount;

        return $this;
    }

    /**
     * Get actualRefundAmount.
     *
     * @return string
     */
    public function getActualRefundAmount()
    {
        return $this->actualRefundAmount;
    }

    /**
     * Set refundProcessFee.
     *
     * @param string $refundProcessFee
     *
     * @return ProductOrder
     */
    public function setRefundProcessFee($refundProcessFee)
    {
        $this->refundProcessFee = $refundProcessFee;

        return $this;
    }

    /**
     * Get refundProcessFee.
     *
     * @return string
     */
    public function getRefundProcessFee()
    {
        return $this->refundProcessFee;
    }

    /**
     * Set refundComment.
     *
     * @param string $refundComment
     *
     * @return ProductOrder
     */
    public function setRefundComment($refundComment)
    {
        $this->refundComment = $refundComment;

        return $this;
    }

    /**
     * Get refundComment.
     *
     * @return string
     */
    public function getRefundComment()
    {
        return $this->refundComment;
    }

    /**
     * Set salesInvoice.
     *
     * @param bool $salesInvoice
     *
     * @return ProductOrder
     */
    public function setSalesInvoice($salesInvoice)
    {
        $this->salesInvoice = $salesInvoice;

        return $this;
    }

    /**
     * Get salesInvoice.
     *
     * @return bool
     */
    public function isSalesInvoice()
    {
        return $this->salesInvoice;
    }

    /**
     * Set cancelByUser.
     *
     * @param bool $cancelByUser
     *
     * @return ProductOrder
     */
    public function setCancelByUser($cancelByUser)
    {
        $this->cancelByUser = $cancelByUser;

        return $this;
    }

    /**
     * Get cancelByUser.
     *
     * @return bool
     */
    public function isCancelByUser()
    {
        return $this->cancelByUser;
    }

    /**
     * @return bool
     */
    public function hasEvaluated()
    {
        return $this->hasEvaluated;
    }

    /**
     * @param bool $hasEvaluated
     */
    public function setHasEvaluated($hasEvaluated)
    {
        $this->hasEvaluated = $hasEvaluated;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }

    /**
     * @return bool
     */
    public function isTip()
    {
        return $this->tip;
    }

    /**
     * @param bool $tip
     */
    public function setTip($tip)
    {
        $this->tip = $tip;
    }

    /**
     * @return mixed
     */
    public function getBasePrice()
    {
        return $this->basePrice;
    }

    /**
     * @param mixed $basePrice
     */
    public function setBasePrice($basePrice)
    {
        $this->basePrice = $basePrice;
    }

    /**
     * @return mixed
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param mixed $unitPrice
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param mixed $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }
}
