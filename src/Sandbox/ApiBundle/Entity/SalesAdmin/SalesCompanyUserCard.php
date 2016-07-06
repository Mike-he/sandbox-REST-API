<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * SalesCompanyUserCard.
 *
 * @ORM\Table(name="SalesCompanyUserCard")
 * @ORM\Entity
 */
class SalesCompanyUserCard
{
    const TYPE_OFFICIAL = 'official';
    const TYPE_SALES = 'sales';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64, nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="companyId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $companyId;

    /**
     * @var string
     *
     * @ORM\Column(name="cardUrl", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $cardUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="cardBackgroundUrl", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $cardBackgroundUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="cardNumberColor", type="string", length=64, nullable=true)
     *
     * @Serializer\Groups({"main", "client"})
     */
    private $cardNumberColor;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
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
     * Set type.
     *
     * @param string $type
     *
     * @return SalesCompanyUserCard
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set companyId.
     *
     * @param int $companyId
     *
     * @return SalesCompanyUserCard
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get companyId.
     *
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * Set cardUrl.
     *
     * @param string $cardUrl
     *
     * @return SalesCompanyUserCard
     */
    public function setCardUrl($cardUrl)
    {
        $this->cardUrl = $cardUrl;

        return $this;
    }

    /**
     * Get cardUrl.
     *
     * @return string
     */
    public function getCardUrl()
    {
        return $this->cardUrl;
    }

    /**
     * Set cardBackgroundUrl.
     *
     * @param string $cardBackgroundUrl
     *
     * @return SalesCompanyUserCard
     */
    public function setCardBackgroundUrl($cardBackgroundUrl)
    {
        $this->cardBackgroundUrl = $cardBackgroundUrl;

        return $this;
    }

    /**
     * Get cardBackgroundUrl.
     *
     * @return string
     */
    public function getCardBackgroundUrl()
    {
        return $this->cardBackgroundUrl;
    }

    /**
     * Set cardNumberColor.
     *
     * @param string $cardNumberColor
     *
     * @return SalesCompanyUserCard
     */
    public function setCardNumberColor($cardNumberColor)
    {
        $this->cardNumberColor = $cardNumberColor;

        return $this;
    }

    /**
     * Get cardNumberColor.
     *
     * @return string
     */
    public function getCardNumberColor()
    {
        return $this->cardNumberColor;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return SalesCompanyUserCard
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
     * @return SalesCompanyUserCard
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
     * SalesCompanyUserCard constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
        $this->modificationDate = new \DateTime('now');
    }
}
