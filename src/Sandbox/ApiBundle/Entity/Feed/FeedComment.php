<?php

namespace Sandbox\ApiBundle\Entity\Feed;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeedComment
 *
 * @ORM\Table(name="ezFeedComment")
 * @ORM\Entity
 */
class FeedComment
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
     * @ORM\Column(name="fid", type="integer",nullable=false)
     */
    private $fid;

    /**
     * @var string
     *
     * @ORM\Column(name="authorID", type="string", length=64, nullable=false)
     */
    private $authorid;

    /**
     * @var string
     *
     * @ORM\Column(name="payload", type="text", nullable=false)
     */
    private $payload;

    /**
     * @var string
     *
     * @ORM\Column(name="creationDate", type="string", length=15, nullable=false)
     */
    private $creationdate;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fid
     *
     * @param  int         $fid
     * @return FeedComment
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

    /**
     * Set authorid
     *
     * @param  string      $authorid
     * @return FeedComment
     */
    public function setAuthorid($authorid)
    {
        $this->authorid = $authorid;

        return $this;
    }

    /**
     * Get authorid
     *
     * @return string
     */
    public function getAuthorid()
    {
        return $this->authorid;
    }

    /**
     * Set payload
     *
     * @param  string      $payload
     * @return FeedComment
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Get payload
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set creationdate
     *
     * @param  string      $creationdate
     * @return FeedComment
     */
    public function setCreationdate($creationdate)
    {
        $this->creationdate = $creationdate.'000';

        return $this;
    }

    /**
     * Get creationdate
     *
     * @return string
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }
}
