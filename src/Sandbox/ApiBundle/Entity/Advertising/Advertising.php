<?php

namespace Sandbox\ApiBundle\Entity\Advertising;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Advertising.
 *
 * @ORM\Table(name="Advertising")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Advertising\AdvertisingRepository")
 */
class Advertising
{
    const SOURCE_EVENT = 'event';
    const SOURCE_NEWS = 'news';
    const SOURCE_URL = 'url';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="text")
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=64)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $source;

    /**
     * @var int
     *
     * @ORM\Column(name="sourceId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $sourceId;

    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $visible = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="isSaved", type="boolean", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $isSaved = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="isDefault", type="boolean", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $isDefault = false;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $attachments;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     *
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
     *
     * @Serializer\Groups({"main"})
     */
    private $modificationDate;

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
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Set source.
     *
     * @param string $source
     *
     * @return Advertising
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set sourceId.
     *
     * @param int $sourceId
     *
     * @return Advertising
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    /**
     * Get sourceId.
     *
     * @return int
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * Set visible.
     *
     * @param bool $visible
     *
     * @return Advertising
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible.
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set isSaved.
     *
     * @param bool $isSaved
     *
     * @return Advertising
     */
    public function setIsSaved($isSaved)
    {
        $this->isSaved = $isSaved;

        return $this;
    }

    /**
     * Get isSaved.
     *
     * @return bool
     */
    public function getIsSaved()
    {
        return $this->isSaved;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Advertising
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
     * @return Advertising
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
     * @param AdvertisingAttachment $attachments
     *
     * @return Advertising
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * @return AdvertisingAttachment
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * @param bool $isDefault
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
    }
}
