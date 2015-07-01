<?php

namespace Sandbox\ApiBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;

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
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer")
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="productId", type="integer")
     */
    private $productId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Product\Product
     *
     * @ORM\OneToOne(targetEntity="Sandbox\ApiBundle\Entity\Product\Product")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id")
     */
    private $product;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime")
     */
    private $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal")
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
     * @ORM\Column(name="paymentDate", type="datetime")
     */
    private $paymentDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cancelledDate", type="datetime")
     */
    private $cancelledDate;

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
}
