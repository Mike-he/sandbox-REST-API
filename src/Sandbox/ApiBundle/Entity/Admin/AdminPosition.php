<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminPosition.
 *
 * @ORM\Table(name="admin_position")
 * @ORM\Entity
 */
class AdminPosition
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="parentPositionId", type="integer", nullable=true)
     */
    private $parentPositionId;

    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=64)
     */
    private $platform;

    /**
     * @var int
     *
     * @ORM\Column(name="salesCompanyId", type="integer", nullable=true)
     */
    private $salesCompanyId;

    /**
     * @var bool
     *
     * @ORM\Column(name="isHidden", type="boolean")
     */
    private $isHidden;

    /**
     * @var bool
     *
     * @ORM\Column(name="isDeleted", type="boolean")
     */
    private $isDeleted;

    /**
     * @var bool
     *
     * @ORM\Column(name="isSuperAdmin", type="boolean")
     */
    private $isSuperAdmin;

    /**
     * @var int
     *
     * @ORM\Column(name="iconId", type="integer")
     */
    private $iconId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
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
     * Set name.
     *
     * @param string $name
     *
     * @return AdminPosition
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
     * Set parentPositionId.
     *
     * @param int $parentPositionId
     *
     * @return AdminPosition
     */
    public function setParentPositionId($parentPositionId)
    {
        $this->parentPositionId = $parentPositionId;

        return $this;
    }

    /**
     * Get parentPositionId.
     *
     * @return int
     */
    public function getParentPositionId()
    {
        return $this->parentPositionId;
    }

    /**
     * Set platform.
     *
     * @param string $platform
     *
     * @return AdminPosition
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get platform.
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set salesCompanyId.
     *
     * @param int $salesCompanyId
     *
     * @return AdminPosition
     */
    public function setSalesCompanyId($salesCompanyId)
    {
        $this->salesCompanyId = $salesCompanyId;

        return $this;
    }

    /**
     * Get salesCompanyId.
     *
     * @return int
     */
    public function getSalesCompanyId()
    {
        return $this->salesCompanyId;
    }

    /**
     * Set isHidden.
     *
     * @param bool $isHidden
     *
     * @return AdminPosition
     */
    public function setIsHidden($isHidden)
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    /**
     * Get isHidden.
     *
     * @return bool
     */
    public function getIsHidden()
    {
        return $this->isHidden;
    }

    /**
     * Set isDeleted.
     *
     * @param bool $isDeleted
     *
     * @return AdminPosition
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted.
     *
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set isSuperAdmin.
     *
     * @param bool $isSuperAdmin
     *
     * @return AdminPosition
     */
    public function setIsSuperAdmin($isSuperAdmin)
    {
        $this->isSuperAdmin = $isSuperAdmin;

        return $this;
    }

    /**
     * Get isSuperAdmin.
     *
     * @return bool
     */
    public function getIsSuperAdmin()
    {
        return $this->isSuperAdmin;
    }

    /**
     * Set iconId.
     *
     * @param int $iconId
     *
     * @return AdminPosition
     */
    public function setIconId($iconId)
    {
        $this->iconId = $iconId;

        return $this;
    }

    /**
     * Get iconId.
     *
     * @return int
     */
    public function getIconId()
    {
        return $this->iconId;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return AdminPosition
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
     * @return AdminPosition
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
}
