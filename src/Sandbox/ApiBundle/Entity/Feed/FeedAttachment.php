<?php

namespace Sandbox\ApiBundle\Entity\Feed;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeedAttachment.
 *
 * @ORM\Table(
 *  name="FeedAttachment",
 *  indexes={
 *      @ORM\Index(name="fk_feedAttachment_feedId_idx", columns={"feedId"})
 *  }
 * )
 * @ORM\Entity
 */
class FeedAttachment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Sandbox\ApiBundle\Entity\Feed\Feed
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Feed\Feed", inversedBy="attachments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="feedId", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $feed;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="attachmentType", type="string", length=64, nullable=false)
     */
    private $attachmentType;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=64, nullable=false)
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
     * @ORM\Column(name="size", type="integer", nullable=false)
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
     * Set Feeds.
     *
     * @param Feed $feed
     *
     * @return FeedAttachment
     */
    public function setFeed($feed)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Get Feed.
     *
     * @return Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return FeedAttachment
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
     * @return FeedAttachment
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
     * @return FeedAttachment
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
     * @return FeedAttachment
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
     * @return FeedAttachment
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
}
