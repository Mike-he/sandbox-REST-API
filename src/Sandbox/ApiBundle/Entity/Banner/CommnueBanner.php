<?php

namespace Sandbox\ApiBundle\Entity\Banner;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Banner.
 *
 * @ORM\Table(name="commnue_banner")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Banner\CommnueBannerRepository")
 */
class CommnueBanner
{
    const SOURCE_EVENT = 'event';
    const SOURCE_URL = 'url';
    const SOURCE_BLANK_BLOCK = 'blank_block';
    const SOURCE_MATERIAL = 'material';

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
     * @ORM\Column(name="title", type="string", length=255)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="cover", type="text")
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $cover;

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
     * @var string
     *
     * @ORM\Column(name="sortTime", type="string", length=15)
     *
     * @Serializer\Groups({"main"})
     */
    private $sortTime;

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
     * @var string
     */
    private $sourceCat;

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
     * Set title.
     *
     * @param string $title
     *
     * @return CommnueBanner
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
     * Set content.
     *
     * @param string $content
     *
     * @return CommnueBanner
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
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
     * Set cover.
     *
     * @param string $cover
     *
     * @return CommnueBanner
     */
    public function setCover($cover)
    {
        $this->cover = $cover;

        return $this;
    }

    /**
     * Get cover.
     *
     * @return string
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * Set source.
     *
     * @param string $source
     *
     * @return CommnueBanner
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
     * @return CommnueBanner
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
     * Set sortTime.
     *
     * @param string $sortTime
     *
     * @return CommnueBanner
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

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Banner
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
     * @return Banner
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
     * Set source_cat.
     *
     * @param string $sourceCat
     *
     * @return Banner
     */
    public function setSourceCat($sourceCat)
    {
        $this->sourceCat = $sourceCat;

        return $this;
    }

    /**
     * Get source_cat.
     *
     * @return string
     */
    public function getSourceCat()
    {
        return $this->sourceCat;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
        $this->setSortTime(round(microtime(true) * 1000));
    }
}
