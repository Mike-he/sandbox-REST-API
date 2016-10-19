<?php

namespace Sandbox\ApiBundle\Entity\Bulletin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * BulletinPostAttachment.
 *
 * @ORM\Table(name="bulletin_post_attachment")
 * @ORM\Entity
 */
class BulletinPostAttachment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "admin", "client"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="postId", type="integer")
     *
     * @Serializer\Groups({"main"})
     */
    private $postId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Bulletin\BulletinPost
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Bulletin\BulletinPost")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="postId", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $post;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     *
     * @Serializer\Groups({"main", "admin", "client"})
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="attachmentType", type="string", length=255)
     *
     * @Serializer\Groups({"main", "admin", "client"})
     */
    private $attachmentType;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255)
     *
     * @Serializer\Groups({"main", "admin", "client"})
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="preview", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "admin", "client"})
     */
    private $preview;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer")
     *
     * @Serializer\Groups({"main", "admin", "client"})
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
     * Set postId.
     *
     * @param int $postId
     *
     * @return BulletinPostAttachment
     */
    public function setPostId($postId)
    {
        $this->postId = $postId;

        return $this;
    }

    /**
     * Get postId.
     *
     * @return int
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * Set post.
     *
     * @param $post
     *
     * @return $this
     */
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post.
     *
     * @return BulletinPostAttachment
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return BulletinPostAttachment
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
     * @return BulletinPostAttachment
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
     * @return BulletinPostAttachment
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
     * @return BulletinPostAttachment
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
     * @return BulletinPostAttachment
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
