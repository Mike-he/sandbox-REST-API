<?php

namespace Sandbox\ApiBundle\Entity\Food;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * FoodAttachment.
 *
 * @ORM\Table(name="FoodAttachment")
 * @ORM\Entity
 */
class FoodAttachment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="foodId", type="integer")
     *
     * @Serializer\Groups({"main"})
     */
    private $foodId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Food\Food
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Food\Food")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="foodId", referencedColumnName="id", onDelete="CASCADE")
     * })
     *
     * @Serializer\Groups({"main"})
     */
    private $food;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     *
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="attachmentType", type="string", length=64)
     *
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $attachmentType;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255)
     *
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="preview", type="text")
     *
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $preview;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer")
     *
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $size;

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
     * Set foodId.
     *
     * @param int $foodId
     *
     * @return FoodAttachment
     */
    public function setFoodId($foodId)
    {
        $this->foodId = $foodId;

        return $this;
    }

    /**
     * Get foodId.
     *
     * @return int
     */
    public function getFoodId()
    {
        return $this->foodId;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return FoodAttachment
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
     * @return FoodAttachment
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
     * @return FoodAttachment
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
     * @return FoodAttachment
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
     * @return FoodAttachment
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
     * Get Food.
     *
     * @return Food
     */
    public function getFood()
    {
        return $this->food;
    }

    /**
     * Set Food.
     *
     * @param Food $food
     *
     * @return FoodAttachment
     */
    public function setFood($food)
    {
        $this->food = $food;

        return $this;
    }
}
