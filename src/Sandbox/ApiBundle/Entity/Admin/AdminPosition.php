<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AdminPosition.
 *
 * @ORM\Table(name="admin_position")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Admin\AdminPositionRepository")
 */
class AdminPosition
{
    const PLATFORM_OFFICIAL = 'official';
    const PLATFORM_SALES = 'sales';
    const PLATFORM_SHOP = 'shop';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "admin", "admin_bind_view"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     * @Serializer\Groups({"main", "admin", "admin_bind_view"})
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="parentPositionId", type="integer", nullable=true)
     * @Serializer\Groups({"main", "admin"})
     */
    private $parentPositionId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Admin\AdminPosition")
     * @ORM\JoinColumn(name="parentPositionId", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({"main", "admin"})
     */
    private $parentPosition;

    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=64)
     * @Serializer\Groups({"main", "admin", "admin_position_bind_view"})
     */
    private $platform;

    /**
     * @var int
     *
     * @ORM\Column(name="salesCompanyId", type="integer", nullable=true)
     * @Serializer\Groups({"main", "admin", "admin_position_bind_view"})
     */
    private $salesCompanyId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany")
     * @ORM\JoinColumn(name="salesCompanyId", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({"main", "admin"})
     */
    private $salesCompany;

    /**
     * @var bool
     *
     * @ORM\Column(name="isHidden", type="boolean")
     */
    private $isHidden = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="isSuperAdmin", type="boolean")
     * @Serializer\Groups({"main", "admin"})
     */
    private $isSuperAdmin = false;

    /**
     * @var int
     *
     * @ORM\Column(name="iconId", type="integer")
     */
    private $iconId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Admin\AdminPositionIcons")
     * @ORM\JoinColumn(name="iconId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main", "admin"})
     */
    private $icon;

    /**
     * @var string
     *
     * @ORM\Column(name="sortTime", type="string", length=15)
     * @Serializer\Groups({"main", "admin"})
     */
    private $sortTime;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modificationDate", type="datetime")
     */
    private $modificationDate;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Admin\AdminPositionPermissionMap",
     *      mappedBy="position"
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="positionId", onDelete="CASCADE")
     * @Serializer\Groups({"main", "admin"})
     */
    private $permissionMappings;

    /**
     * @var array
     */
    private $permissions;

    /**
     * @var string
     */
    private $currentPlatform;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $permissionGroups;

    /**
     * set permissionMappings.
     *
     * @return AdminPosition
     */
    public function setPermissionMappings($permissionMappings)
    {
        $this->permissionMappings = $permissionMappings;

        return $this;
    }

    /**
     * get permissionMappings.
     *
     * @return array
     */
    public function getPermissionMappings()
    {
        return $this->permissionMappings;
    }

    /**
     * set currentPlatform.
     *
     * @return AdminPosition
     */
    public function setCurrentPlatform($currentPlatform)
    {
        $this->currentPlatform = $currentPlatform;

        return $this;
    }

    /**
     * get currentPlatform.
     *
     * @return string
     */
    public function getCurrentPlatform()
    {
        return $this->currentPlatform;
    }

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
     * set permissions.
     *
     * @return AdminPosition
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * get permissions.
     *
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
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
     * @return mixed
     */
    public function getParentPosition()
    {
        return $this->parentPosition;
    }

    /**
     * @param mixed $parentPosition
     */
    public function setParentPosition($parentPosition)
    {
        $this->parentPosition = $parentPosition;
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
     * @return mixed
     */
    public function getSalesCompany()
    {
        return $this->salesCompany;
    }

    /**
     * @param mixed $salesCompany
     */
    public function setSalesCompany($salesCompany)
    {
        $this->salesCompany = $salesCompany;
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
     * Set icon.
     *
     * @param AdminPositionIcons $icon
     *
     * @return AdminPosition
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon.
     *
     * @return AdminPositionIcons
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getSortTime()
    {
        return $this->sortTime;
    }

    /**
     * @param string $sortTime
     */
    public function setSortTime($sortTime)
    {
        $this->sortTime = $sortTime;
    }

    /**
     * @return array
     */
    public function getPermissionGroups()
    {
        return $this->permissionGroups;
    }

    /**
     * @param array $permissionGroups
     */
    public function setPermissionGroups($permissionGroups)
    {
        $this->permissionGroups = $permissionGroups;
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

    public function __construct()
    {
        $this->setSortTime(round(microtime(true) * 1000));
    }
}
