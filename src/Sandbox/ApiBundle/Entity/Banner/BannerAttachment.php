<?php

namespace Sandbox\ApiBundle\Entity\Banner;

use Doctrine\ORM\Mapping as ORM;

/**
 * BannerAttachment.
 *
 * @ORM\Table(name="BannerAttachment")
 * @ORM\Entity
 */
class BannerAttachment
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
     * @ORM\Column(name="bannerId", type="integer")
     */
    private $bannerId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Banner\Banner
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Banner\Banner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bannerId", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $banner;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="attachmentType", type="string", length=64)
     */
    private $attachmentType;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="preview", type="text", nullable=true)
     */
    private $preview;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer")
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
     * Set bannerId.
     *
     * @param int $bannerId
     *
     * @return BannerAttachment
     */
    public function setBannerId($bannerId)
    {
        $this->bannerId = $bannerId;

        return $this;
    }

    /**
     * Get bannerId.
     *
     * @return int
     */
    public function getBannerId()
    {
        return $this->bannerId;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return BannerAttachment
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
     * @return BannerAttachment
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
     * @return BannerAttachment
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
     * @return BannerAttachment
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
     * @return BannerAttachment
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
     * Get Banner.
     *
     * @return Banner
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * Set Banner.
     *
     * @param Banner $banner
     *
     * @return BannerAttachment
     */
    public function setBanner($banner)
    {
        $this->banner = $banner;

        return $this;
    }
}
