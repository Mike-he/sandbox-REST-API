<?php

namespace Sandbox\ApiBundle\Entity\Bulletin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * BulletinType.
 *
 * @ORM\Table(name="bulletin_types")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Bulletin\BulletinTypeRepository")
 */
class BulletinType
{
    const TYPE_CONFLICT_CODE = 400001;
    const TYPE_CONFLICT_MESSAGE = 'Bulletin name already exists';

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
     * @ORM\Column(name="name", type="string", length=64)
     *
     * @Serializer\Groups({"main", "admin", "client"})
     */
    private $name;

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
     * @var string
     *
     * @ORM\Column(name="sortTime", type="string", length=15)
     * @Serializer\Groups({"main", "admin"})
     */
    private $sortTime;

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
     * Set name.
     *
     * @param string $name
     *
     * @return BulletinType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set deleted.
     *
     * @param bool $deleted
     *
     * @return BulletinType
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
     * @return BulletinType
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
     * Set sortTime.
     *
     * @param string $sortTime
     *
     * @return BulletinType
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
        $this->setSortTime(round(microtime(true) * 1000));
    }
}
