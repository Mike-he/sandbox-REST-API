<?php

namespace Sandbox\ApiBundle\Entity\Feed;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeedLike.
 *
 * @ORM\Table(name="FeedLike")
 * @ORM\Entity
 */
class FeedLike
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
     * @var int
     *
     * @ORM\Column(name="feedId", type="integer", nullable=false)
     */
    private $feedId;

    /**
     * @var string
     *
     * @ORM\Column(name="authorId", type="string", length=64, nullable=false)
     */
    private $authorId;

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
     * Set feedId.
     *
     * @param int $feedId
     *
     * @return FeedLike
     */
    public function setFeedId($feedId)
    {
        $this->feedId = $feedId;

        return $this;
    }

    /**
     * Get feedId.
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
     * @param string $authorId
     *
     * @return FeedLike
     */
    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;

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
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return FeedLike
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