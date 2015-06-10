<?php

namespace Sandbox\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeedLike
 *
 * @ORM\Table(name="ezFeedLike")
 * @ORM\Entity
 */
class FeedLike
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
     * @param  int      $fid
     * @return FeedLike
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
     * @param  string   $authorid
     * @return FeedLike
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
     * Set creationdate
     *
     * @param  string   $creationdate
     * @return FeedLike
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
