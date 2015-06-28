<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * User Portfolio
 *
 * @ORM\Table(name="UserPortfolio")
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\User\UserPortfolioRepository"
 * )
 */
class UserPortfolio
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
     * @ORM\Column(name="userId", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var string
     * @ORM\Column(name="content", type="text", nullable =false)
     */
    private $content;

    /**
     * @var string
     * @ORM\Column(name="attachmentType", type="string", nullable=false)
     */
    private $attachmentType;

    /**
     * @var string
     * @ORM\Column(name="fileName", type="string", nullable=false)
     */
    private $fileName;

    /**
     * @var string
     * @ORM\Column(name="preview", type="string", nullable=true)
     */
    private $preview;

    /**
     * @var int
     * @ORM\Column(name="size", type="integer", nullable=false)
     */
    private $size;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     */
    private $modificationDate;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return UserPortfolio
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return UserPortfolio
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getAttachmentType()
    {
        return $this->attachmentType;
    }

    /**
     * @param string $attachmentType
     *
     * @return UserPortfolio
     */
    public function setAttachmentType($attachmentType)
    {
        $this->attachmentType = $attachmentType;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     *
     * @return UserPortfolio
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @param string $preview
     *
     * @return UserPortfolio
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     *
     * @return UserPortfolio
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     *
     * @return UserPortfolio
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param \DateTime $modificationDate
     *
     * @return UserPortfolio
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    public function __construct()
    {
        $now = new \DateTime("now");
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }
}
