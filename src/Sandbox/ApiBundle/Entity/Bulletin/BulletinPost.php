<?php

namespace Sandbox\ApiBundle\Entity\Bulletin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * BulletinPost.
 *
 * @ORM\Table(name="bulletin_post")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Bulletin\BulletinPostRepository")
 */
class BulletinPost
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=128)
     *
     * @Serializer\Groups({"main", "admin", "client"})
     */
    private $title;

    /**
     * @var int
     *
     * @ORM\Column(name="typeId", type="integer")
     *
     * @Serializer\Groups({"main"})
     */
    private $typeId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Bulletin\BulletinType")
     * @ORM\JoinColumn(name="typeId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"main", "admin", "client"})
     **/
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     *
     * @Serializer\Groups({"main", "admin", "client"})
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="deleted", type="boolean", options={"default": false})
     *
     * @Serializer\Groups({"main"})
     */
    private $deleted = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin", "client"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin", "client"})
     */
    private $modificationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="sortTime", type="string", length=15)
     * @Serializer\Groups({"main", "admin"})
     */
    private $sortTime;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Bulletin\BulletinPostAttachment",
     *      mappedBy="post",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="postId")
     * @ORM\OrderBy({"id" = "ASC"})
     *
     * @Serializer\Groups({"main", "admin", "client"})
     */
    private $postAttachments;

    /**
     * @var array
     */
    private $attachments;

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
     * Set typeId.
     *
     * @param int $typeId
     *
     * @return BulletinPost
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId.
     *
     * @return int
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set type.
     *
     * @param BulletinType $type
     *
     * @return BulletinPost
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return BulletinType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return BulletinPost
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return BulletinPost
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set deleted.
     *
     * @param bool $deleted
     *
     * @return BulletinPost
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return BulletinPost
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
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return BulletinPost
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param array $attachments
     *
     * @return BulletinPost
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set bulletin post attachments.
     *
     * @param $postAttachments
     *
     * @return BulletinPost
     */
    public function setPostAttachments($postAttachments)
    {
        $this->postAttachments = $postAttachments;

        return $this;
    }

    /**
     * Get bulletin post attachments.
     *
     * @return array
     */
    public function getPostAttachments()
    {
        return $this->postAttachments;
    }

    /**
     * Set sortTime.
     *
     * @param string $sortTime
     *
     * @return BulletinPost
     */
    public function setSortTime($sortTime)
    {
        $this->sortTime = $sortTime;

        return $this;
    }

    /**
     * Get sortTime.
     *
     * @return string
     */
    public function getSortTime()
    {
        return $this->sortTime;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
        $this->setSortTime(round(microtime(true) * 1000));
    }
}
