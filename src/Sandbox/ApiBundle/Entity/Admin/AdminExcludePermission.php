<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminExcludePermission.
 *
 * @ORM\Table(
 *     name="admin_exclude_permission",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="permissionId_companyId_UNIQUE", columns={"permissionId", "salesCompanyId"})
 *      }
 * )
 * @ORM\Entity
 */
class AdminExcludePermission
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
     * @ORM\Column(name="platform", type="string", length=16, nullable=false)
     */
    private $platform;

    /**
     * @var int
     *
     * @ORM\Column(name="salesCompanyId", type="integer", nullable=true)
     */
    private $salesCompanyId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany")
     * @ORM\JoinColumn(name="salesCompanyId", referencedColumnName="id", onDelete="SET NULL")
     */
    private $salesCompany;

    /**
     * @var int
     *
     * @ORM\Column(name="permissionId", type="integer", nullable=true)
     */
    private $permissionId;

    /**
     * @ORM\ManyToOne(targetEntity="AdminPermission")
     * @ORM\JoinColumn(name="permissionId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $permission;

    /**
     * @var int
     *
     * @ORM\Column(name="groupId", type="integer", nullable=true)
     */
    private $groupId;

    /**
     * @ORM\ManyToOne(targetEntity="AdminPermissionGroups")
     * @ORM\JoinColumn(name="groupId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $group;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

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
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    /**
     * Set salesCompanyId.
     *
     * @param int $salesCompanyId
     *
     * @return AdminExcludePermission
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
     * Set permissionId.
     *
     * @param int $permissionId
     *
     * @return AdminExcludePermission
     */
    public function setPermissionId($permissionId)
    {
        $this->permissionId = $permissionId;

        return $this;
    }

    /**
     * Get permissionId.
     *
     * @return int
     */
    public function getPermissionId()
    {
        return $this->permissionId;
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
     * @return mixed
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param mixed $permission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param int $groupId
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
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
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * AdminExcludePermission constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
    }
}
