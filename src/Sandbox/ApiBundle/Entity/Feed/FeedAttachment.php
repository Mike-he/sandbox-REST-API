<?php

namespace Sandbox\ApiBundle\Entity\Feed;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeedAttachment
 *
 * @ORM\Table(name="ezFeedAttachment")
 * @ORM\Entity
 */
class FeedAttachment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer",  nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="fid", type="integer",  nullable=false)
     */
    private $fid;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", nullable=false)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="attachmentType", type="string", nullable=false)
     */
    private $attachmenttype;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", nullable=false)
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="preview", type="string", nullable=false)
     */
    private $preview;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer",  nullable=false)
     */
    private $size;

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set content
     *
     * @param  string           $content
     * @return ezFeedAttachment
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set attachmenttype
     *
     * @param  string           $attachmenttype
     * @return ezFeedAttachment
     */
    public function setAttachmenttype($attachmenttype)
    {
        $this->attachmenttype = $attachmenttype;

        return $this;
    }

    /**
     * Get attachmenttype
     *
     * @return string
     */
    public function getAttachmenttype()
    {
        return $this->attachmenttype;
    }

    /**
     * Set filename
     *
     * @param  string           $filename
     * @return ezFeedAttachment
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set preview
     *
     * @param  string           $preview
     * @return ezFeedAttachment
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;

        return $this;
    }

    /**
     * Get preview
     *
     * @return string
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * Set size
     *
     * @param  int              $size
     * @return ezFeedAttachment
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set fid
     *
     * @param  int              $fid
     * @return ezFeedAttachment
     */
    public function setFid($fid)
    {
        $this->fid = $fid;

        return $this;
    }

    /**
     * Get fid
     *
     * @return int
     */
    public function getFid()
    {
        return $this->fid;
    }
}
