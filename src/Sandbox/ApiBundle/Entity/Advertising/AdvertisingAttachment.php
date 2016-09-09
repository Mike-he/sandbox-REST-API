<?php

namespace Sandbox\ApiBundle\Entity\Advertising;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * AdvertisingAttachment.
 *
 * @ORM\Table(name = "advertising_attachment")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Advertising\AdvertisingAttachmentRepository")
 */
class AdvertisingAttachment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="advertisingId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $advertisingId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Advertising\Advertising
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Advertising\Advertising")
     * @ORM\JoinColumn(name="advertisingId", referencedColumnName="id")
     */
    private $advertising;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="attachmentType", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $attachmentType;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="preview", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $preview;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $size;

    /**
     * @var int
     *
     * @ORM\Column(name="height", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $height;

    /**
     * @var int
     *
     * @ORM\Column(name="width", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $width;

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
     * Set advertisingId.
     *
     * @param int $advertisingId
     *
     * @return AdvertisingAttachment
     */
    public function setAdvertisingId($advertisingId)
    {
        $this->advertisingId = $advertisingId;

        return $this;
    }

    /**
     * Get advertisingId.
     *
     * @return int
     */
    public function getAdvertisingId()
    {
        return $this->advertisingId;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return AdvertisingAttachment
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
     * @return AdvertisingAttachment
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
     * @return AdvertisingAttachment
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
     * @return AdvertisingAttachment
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
     * @return AdvertisingAttachment
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
     * Set height.
     *
     * @param int $height
     *
     * @return AdvertisingAttachment
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set width.
     *
     * @param int $width
     *
     * @return AdvertisingAttachment
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set advertising.
     *
     * @param \Sandbox\ApiBundle\Entity\Advertising\Advertising $advertising
     *
     * @return AdvertisingAttachment
     */
    public function setAdvertising(\Sandbox\ApiBundle\Entity\Advertising\Advertising $advertising = null)
    {
        $this->advertising = $advertising;

        return $this;
    }

    /**
     * Get advertising.
     *
     * @return \Sandbox\ApiBundle\Entity\Advertising\Advertising
     */
    public function getAdvertising()
    {
        return $this->advertising;
    }
}
