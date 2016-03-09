<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ShopOrder.
 *
 * @ORM\Table(name="ShopOrder")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Shop\ShopOrderRepository")
 */
class ShopOrder
{
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PAID = 'paid';
    const STATUS_READY = 'ready';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ISSUE = 'issue';
    const STATUS_TO_BE_REFUNDED = 'waiting';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_CANCELLED = 'cancelled';
    const LETTER_HEAD = 'S';
    const PAYMENT_SUBJECT = 'SANDBOX3-店铺订单';
    const PAYMENT_BODY = 'Shop Order';
    const READY_NOTIFICATION = '您的订单已做好, 请到柜台领取';
    const REFUNDED_NOTIFICATION = '您的订单已退款, 点击查看';

    const NOT_PAID_CODE = 400003;
    const NOT_PAID_MESSAGE = 'Order is not paid';
    const NOT_READY_CODE = 400004;
    const NOT_READY_MESSAGE = 'Order is not ready';
    const NOT_READY_OR_PAID_CODE = 400005;
    const NOT_READY_OR_PAID_MESSAGE = 'Order is not paid or not ready';
    const NOT_ISSUE_CODE = 400006;
    const NOT_ISSUE_MESSAGE = 'No issue with this order';
    const NOT_TO_BE_REFUNDED_CODE = 400007;
    const NOT_TO_BE_REFUNDED_MESSAGE = 'Can not refund this order';
    const WRONG_STATUS_CODE = 400008;
    const WRONG_STATUS_MESSAGE = 'Can not update order to this status';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="shopId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $shopId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\Shop")
     * @ORM\JoinColumn(name="shopId", referencedColumnName="id")
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     **/
    private $shop;

    /**
     * @var string
     *
     * @ORM\Column(name="orderNumber", type="string", length=255, unique=true)
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $orderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="payChannel", type="string", length=16, nullable=true)
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $payChannel;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer")
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2)
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64)
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="paymentDate", type="datetime", nullable=true)
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $paymentDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cancelDate", type="datetime", nullable=true)
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $cancelDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $modificationDate;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopOrderProduct",
     *      mappedBy="order",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="orderId")
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $shopOrderProducts;

    /**
     * @var bool
     *
     * @ORM\Column(name="unoriginal", type="boolean", options={"default": false})
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $unoriginal = false;

    /**
     * @var int
     *
     * @ORM\Column(name="linkedOrderId", type="integer", nullable=true)
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $linkedOrderId;

    /**
     * @var ShopOrder
     *
     * @ORM\ManyToOne(
     *      targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopOrder"
     * )
     * @ORM\JoinColumn(name="linkedOrderId", referencedColumnName="id")
     * @Serializer\Groups({"main"})
     */
    private $linkedOrder;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopOrder",
     *      mappedBy="linkedOrder"
     * )
     * @ORM\JoinColumn(name="linkedOrderId", referencedColumnName="id")
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $linkedOrders;

    /**
     * @var array
     */
    private $products;

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
     * Set shopId.
     *
     * @param int $shopId
     *
     * @return ShopOrder
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * Get shopId.
     *
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * Set shop.
     *
     * @param $shop
     *
     * @return ShopOrder
     */
    public function setShop($shop)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * Get shop.
     *
     * @return Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * Set orderNumber.
     *
     * @param string $orderNumber
     *
     * @return ShopOrder
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
     * Set payChannel.
     *
     * @param string $payChannel
     *
     * @return ShopOrder
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return ShopOrder
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
     * Set price.
     *
     * @param string $price
     *
     * @return ShopOrder
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
     * Set status.
     *
     * @param string $status
     *
     * @return ShopOrder
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
     * @return ShopOrder
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
     * Set cancelDate.
     *
     * @param \DateTime $cancelDate
     *
     * @return ShopOrder
     */
    public function setCancelDate($cancelDate)
    {
        $this->cancelDate = $cancelDate;

        return $this;
    }

    /**
     * Get cancelDate.
     *
     * @return \DateTime
     */
    public function getCancelDate()
    {
        return $this->cancelDate;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return ShopOrder
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
     * @return ShopOrder
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
     * Set products.
     *
     * @param array $products
     *
     * @return ShopOrder
     */
    public function setProducts($products)
    {
        $this->products = $products;

        return $this;
    }

    /**
     * Get products.
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set shopOrderproducts.
     *
     * @param array $shopOrderproducts
     *
     * @return ShopOrder
     */
    public function setShopOrderProducts($shopOrderproducts)
    {
        $this->shopOrderProducts = $shopOrderproducts;

        return $this;
    }

    /**
     * Get shopOrderproducts.
     *
     * @return array
     */
    public function getShopOrderProducts()
    {
        return $this->shopOrderProducts;
    }

    /**
     * Set unoriginal.
     *
     * @param bool $unoriginal
     *
     * @return ShopOrder
     */
    public function setUnoriginal($unoriginal)
    {
        $this->unoriginal = $unoriginal;

        return $this;
    }

    /**
     * Get unoriginal.
     *
     * @return bool
     */
    public function IsUnoriginal()
    {
        return $this->unoriginal;
    }

    /**
     * Set linkedOrderId.
     *
     * @param int $linkedOrderId
     *
     * @return ShopOrder
     */
    public function setLinkedOrderId($linkedOrderId)
    {
        $this->linkedOrderId = $linkedOrderId;

        return $this;
    }

    /**
     * Get linkedOrderId.
     *
     * @return int
     */
    public function getLinkedOrderId()
    {
        return $this->linkedOrderId;
    }

    /**
     * Set linkedOrder.
     *
     * @param ShopOrder $linkedOrder
     *
     * @return ShopOrder
     */
    public function setLinkedOrder($linkedOrder)
    {
        $this->linkedOrder = $linkedOrder;

        return $this;
    }

    /**
     * Get linkedOrder.
     *
     * @return ShopOrder
     */
    public function getLinkedOrder()
    {
        return $this->linkedOrder;
    }

    /**
     * Set linkedOrders.
     *
     * @param array $linkedOrders
     *
     * @return ShopOrder
     */
    public function setLinkedOrders($linkedOrders)
    {
        $this->linkedOrders = $linkedOrders;

        return $this;
    }

    /**
     * Get linkedOrders.
     *
     * @return array
     */
    public function getLinkedOrders()
    {
        return $this->linkedOrders;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
        $this->setStatus(self::STATUS_UNPAID);
    }
}
