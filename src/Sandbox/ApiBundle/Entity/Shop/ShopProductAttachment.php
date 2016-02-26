<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ShopProductAttachment.
 *
 * @ORM\Table(name="ShopProductAttachment")
 * @ORM\Entity
 */
class ShopProductAttachment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="productId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $productId;

    /**
     * @var ShopProduct
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopProduct")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main"})
     */
    private $product;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="attachmentType", type="string", length=64)
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $attachmentType;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=64)
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="preview", type="text", nullable=true)
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $preview;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $size;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $creationDate;

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
     * Set productId.
     *
     * @param int $productId
     *
     * @return ShopProductAttachment
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
     * @param ShopProduct $product
     *
     * @return ShopProductAttachment
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product.
     *
     * @return ShopProduct
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return ShopProductAttachment
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set attachmentType.
     *
     * @param string $attachmentType
     *
     * @return ShopProductAttachment
     */
    public function setAttachmentType($attachmentType)
    {
        $this->attachmentType = $attachmentType;

        return $this;
    }

    /**
     * Get attachmentType.
     *
     * @return string
     */
    public function getAttachmentType()
    {
        return $this->attachmentType;
    }

    /**
     * Set filename.
     *
     * @param string $filename
     *
     * @return ShopProductAttachment
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set preview.
     *
     * @param string $preview
     *
     * @return ShopProductAttachment
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;

        return $this;
    }

    /**
     * Get preview.
     *
     * @return string
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * Set size.
     *
     * @param int $size
     *
     * @return ShopProductAttachment
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return ShopProductAttachment
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

    public function __construct()
    {
        $this->setCreationDate(new \DateTime('now'));
    }
}
