<?php

namespace Sandbox\ApiBundle\Entity\Feed;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Task.
 *
 * @ORM\Table(name="FeedView")
 * @ORM\Entity(
 *     repositoryClass="EZLinx\ApiBundle\Entity\FeedViewRepository"
 * )
 */
class FeedView
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
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     */
    private $content;

    /**
     * @var int
     *
     * @ORM\Column(name="parentID", type="integer",  nullable=false)
     */
    private $parentid;

    /**
     * @var string
     *
     * @ORM\Column(name="parentType", type="string", nullable=false)
     */
    private $parenttype;

    /**
     * @var string
     *
     * @ORM\Column(name="ownerID", type="string", length=64, nullable=false)
     */
    private $ownerid;

    /**
     * @var string
     *
     * @ORM\Column(name="creationDate", type="string", length=15, nullable=false)
     */
    private $creationdate;

    /**
     * @var int
     *
     * @ORM\Column(name="likes_count", type="integer",  nullable=false)
     */
    private $likesCount;

    /**
     * @var int
     *
     * @ORM\Column(name="comments_count", type="integer",  nullable=false)
     */
    private $commentsCount;

    /**
     * @ORM\OneToMany(targetEntity="FeedAttachmentView", mappedBy="feedview")
     **/
    private $attachments;

    private $myLikeId;

    /**
     * Get feedid.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
     * Get parentid.
     *
     * @return int
     */
    public function getParentid()
    {
        return $this->parentid;
    }

    /**
     * Get parenttype.
     *
     * @return string
     */
    public function getParenttype()
    {
        return $this->parenttype;
    }

    /**
     * Get ownerid.
     *
     * @return string
     */
    public function getOwnerid()
    {
        return $this->ownerid;
    }

    /**
     * Get creationdate.
     *
     * @return string
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * Get attachments.
     *
     * @return string
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Get likes_count.
     *
     * @return int
     */
    public function getLikesCount()
    {
        return $this->likesCount;
    }

    /**
     * Get commentsCount.
     *
     * @return int
     */
    public function getCommentsCount()
    {
        return $this->commentsCount;
    }

    /**
     * Get myLikeId.
     *
     * @return int
     */
    public function getMyLikeId()
    {
        return $this->myLikeId;
    }

    /**
     * Set myLikeId.
     *
     * @param string $myLikeId
     *
     * @return int
     */
    public function setMyLikeId($myLikeId)
    {
        $this->myLikeId = $myLikeId;

        return $this;
    }

    /**
     * Used for array deduplication/array diff
     * hence why we only return the id.
     */
    public function __toString()
    {
        return $this->getId();
    }

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
    }
}
