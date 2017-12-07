<?php

namespace Sandbox\ApiBundle\Entity\Feed;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Sandbox\ApiBundle\Entity\User\UserProfile;

/**
 * Feed view.
 *
 * @ORM\Table(name="feed_view")
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\Feed\FeedRepository"
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
     *
     * @Serializer\Groups({"main", "feed"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     *
     * @Serializer\Groups({"main", "feed"})
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="ownerId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $ownerId;

    /**
     * @var UserProfile
     *
     * @Serializer\Groups({"main", "feed"})
     */
    private $owner;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main", "feed"})
     */
    private $creationDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="isDeleted", type="boolean", nullable=false)
     *
     * @Serializer\Groups({})
     */
    private $isDeleted;

    /**
     * @var int
     *
     * @ORM\Column(name="likes_count", type="integer",  nullable=false)
     *
     * @Serializer\Groups({"main", "feed"})
     */
    private $likesCount;

    /**
     * @var int
     *
     * @ORM\Column(name="comments_count", type="integer",  nullable=false)
     *
     * @Serializer\Groups({"main", "feed"})
     */
    private $commentsCount;

    /**
     * @var int
     *
     * @Serializer\Groups({"main", "feed"})
     */
    private $myLikeId;

    /**
     * @var FeedAttachment
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Feed\FeedAttachment",
     *      mappedBy="feed",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="feedId")
     *
     * @Serializer\Groups({"main", "feed"})
     */
    private $attachments;

    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string",  nullable=true)
     *
     * @Serializer\Groups({"main", "feed"})
     */
    private $platform;

    /**
     * Get id.
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
     * Get ownerid.
     *
     * @return string
     */
    public function getOwnerId()
    {
        return $this->ownerId;
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
     * @return UserProfile
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param UserProfile $owner
     *
     * @return FeedView
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get isDeleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
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
