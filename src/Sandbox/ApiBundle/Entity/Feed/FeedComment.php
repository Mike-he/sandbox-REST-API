<?php

namespace Sandbox\ApiBundle\Entity\Feed;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeedComment.
 *
 * @ORM\Table(
 *  name="FeedComment",
 *  indexes={
 *      @ORM\Index(name="fk_feedComment_feedId_idx", columns={"feedId"})
 *  }
 * )
 * @ORM\Entity
 */
class FeedComment
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
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Feed\Feed", inversedBy="comments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="feedId", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $feed;

    /**
     * @var string
     *
     * @ORM\Column(name="authorID", type="string", length=64, nullable=false)
     */
    private $authorId;

    /**
     * @var string
     *
     * @ORM\Column(name="payload", type="text", nullable=false)
     */
    private $payload;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
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
     * Set Feeds.
     *
     * @param Feed $feed
     *
     * @return FeedComment
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
}
