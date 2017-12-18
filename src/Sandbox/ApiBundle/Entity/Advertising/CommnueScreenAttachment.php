<?php

namespace Sandbox\ApiBundle\Entity\Advertising;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * CommnueScreenAttachment.
 *
 * @ORM\Table(name = "commnue_screen_attachment")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Advertising\CommnueScreenAttachmentRepository")
 */
class CommnueScreenAttachment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="screenId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $screenId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Advertising\Advertising
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Advertising\CommnueAdvertisingScreen")
     * @ORM\JoinColumn(name="screenId", referencedColumnName="id", onDelete="CASCADE")
     *
     */
    private $screen;

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
     * Set screenId.
     *
     * @param int $screenId
     *
     * @return CommnueScreenAttachment
     */
    public function setScreenId($screenId)
    {
        $this->screenId = $screenId;

        return $this;
    }

    /**
     * Get screenId.
     *
     * @return int
     */
    public function getScreenId()
    {
        return $this->screenId;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return CommnueScreenAttachment
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
     * @return CommnueScreenAttachment
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
     * @return CommnueScreenAttachment
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
     * @return CommnueScreenAttachment
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
     * @return CommnueScreenAttachment
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
     * @return CommnueScreenAttachment
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
     * @return CommnueScreenAttachment
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
     * Set screen.
     *
     * @param \Sandbox\ApiBundle\Entity\Advertising\CommnueAdvertisingScreen $screen
     *
     * @return CommnueScreenAttachment
     */
    public function setScreen(\Sandbox\ApiBundle\Entity\Advertising\CommnueAdvertisingScreen $screen = null)
    {
        $this->screen = $screen;

        return $this;
    }

    /**
     * Get screen.
     *
     * @return \Sandbox\ApiBundle\Entity\Advertising\CommnueAdvertisingScreen
     */
    public function getScreen()
    {
        return $this->screen;
    }
}
