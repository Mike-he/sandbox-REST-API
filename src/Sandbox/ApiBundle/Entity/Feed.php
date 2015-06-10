<?php

namespace Sandbox\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feed
 *
 * @ORM\Table(name="ezFeed")
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Entity\FeedRepository"
 * )
 */
class Feed
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
     * @var string
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

    private $attachments;

    /**
     * Get feedid
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
     * @param  string $content
     * @return Feed
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
        return $this->ownerid;
    }

    /**
     * Set parentid
     *
     * @param  int  $parentid
     * @return Feed
     */
    public function setParentid($parentid)
    {
        $this->parentid = $parentid;

        return $this;
    }

    /**
     * Get parentid
     *
     * @return int
     */
    public function getParentid()
    {
        return $this->parentid;
    }

    /**
     * Set parenttype
     *
     * @param  string $parenttype
     * @return Feed
     */
    public function setParenttype($parenttype)
    {
        $this->parenttype = $parenttype;

        return $this;
    }

    /**
     * Get parenttype
     *
     * @return string
     */
    public function getParenttype()
    {
        return $this->parenttype;
    }

    /**
     * Set ownerid
     *
     * @param  string $ownerid
     * @return Feed
     */
    public function setOwnerid($ownerid)
    {
        $this->ownerid = $ownerid;

        return $this;
    }

    /**
     * Get ownerid
     *
     * @return string
     */
    public function getOwnerid()
    {
        return $this->ownerid;
    }

    /**
     * Set creationdate
     *
     * @param  string $creationdate
     * @return Feed
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

    /**
     * Set attachments
     *
     * @param  string $attachments
     * @return Feed
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Get attachments
     *
     * @return string
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Used for array deduplication/array diff
     * hence why we only return the id
     */
    public function __toString()
    {
        return $this->getId();
    }

    public function __construct()
    {
        $this->setCreationdate(time());
    }
}
