<?php

namespace Sandbox\ApiBundle\Entity\Feed;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sandbox\ApiBundle\Entity\User\UserProfile;

/**
 * FeedComment.
 *
 * @ORM\Table(
 *  name="FeedComment",
 *  indexes={
 *      @ORM\Index(name="fk_feedComment_feedId_idx", columns={"feedId"})
 *  }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\Feed\FeedCommentRepository"
 * )
 */
class FeedComment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Groups({"main", "feed"})
     */
    private $id;

    /**
     * @var \Sandbox\ApiBundle\Entity\Feed\Feed
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Feed\Feed")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="feedId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $feed;

    /**
     * @var int
     *
     * @ORM\Column(name="feedId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "feed"})
     */
    private $feedId;

    /**
     * @var \Sandbox\ApiBundle\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="authorId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     * @ORM\Column(name="authorId", type="integer", nullable=false)
     */
    private $authorId;

    /**
     * @var UserProfile
     *
     * @Serializer\Groups({"main", "feed"})
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="payload", type="text", nullable=false)
     *
     * @Serializer\Groups({"main", "feed"})
     */
    private $payload;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main", "feed"})
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
     * Set Feed id.
     *
     * @param int
     *
     * @return FeedComment
     */
    public function setFeedId($feedId)
    {
        $this->feedId = $feedId;

        return $this;
    }

    /**
     * Get Feed id.
     *
     * @return int
     */
    public function getFeedId()
    {
        return $this->feedId;
    }

    /**
     * Set authorId.
     *
     * @param string $authorDd
     *
     * @return FeedComment
     */
    public function setAuthorId($authorDd)
    {
        $this->authorId = $authorDd;

        return $this;
    }

    /**
     * Get authorId.
     *
     * @return string
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * Set payload.
     *
     * @param string $payload
     *
     * @return FeedComment
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Get payload.
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return UserProfile
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param UserProfile $author
     *
     * @return FeedComment
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return FeedComment
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

    /**
     * Set feed.
     *
     * @param $feed
     *
     * @return FeedComment
     */
    public function setFeed($feed)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Get feed.
     *
     * @return Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }
}
