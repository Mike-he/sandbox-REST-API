<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * AdminPermissionMap.
 *
 * @ORM\Table(
 *      name="sales_admin_permission_map",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="adminId_permissionId_buildingId_UNIQUE", columns={"adminId", "permissionId", "buildingId"})
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_AdminPermissionMap_adminId_idx", columns={"adminId"}),
 *          @ORM\Index(name="fk_AdminPermissionMap_permissionId_idx", columns={"permissionId"}),
 *          @ORM\Index(name="fk_AdminPermissionMap_buildingId_idx", columns={"buildingId"})
 *      }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\SalesAdmin\SalesAdminPermissionMapRepository"
 * )
 */
class SalesAdminPermissionMap
{
    const OP_LEVEL_VIEW = 1;
    const OP_LEVEL_EDIT = 2;
    const OP_LEVEL_SYNC = 3;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="adminId", type="integer", nullable=false)
     */
    private $adminId;

    /**
     * @var int
     *
     * @ORM\Column(name="permissionId", type="integer", nullable=false)
     */
    private $permissionId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @ORM\ManyToOne(targetEntity="SalesAdminPermission")
     * @ORM\JoinColumn(name="permissionId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main", "login", "admin", "auth", "admin_basic"})
     **/
    private $permission;

    /**
     * @var int
     *
     * @ORM\Column(name="opLevel", type="integer", nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth", "admin_basic"})
     */
    private $opLevel;

    /**
     * @ORM\ManyToOne(targetEntity="SalesAdmin")
     * @ORM\JoinColumn(name="adminId", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $admin;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer", nullable=true)
     * @Serializer\Groups({"main", "login", "auth"})
     */
    private $buildingId;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     */
    private $building;

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
     * Set $adminId.
     *
     * @param int $adminId
     *
     * @return SalesAdminPermissionMap
     */
    public function setAdminId($adminId)
    {
        $this->adminId = $adminId;

        return $this;
    }

    /**
     * Get adminId.
     *
     * @return int
     */
    public function getAdminId()
    {
        return $this->adminId;
    }

    /**
     * Set permissionId.
     *
     * @param int $permissionId
     *
     * @return SalesAdminPermissionMap
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
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return SalesAdminPermissionMap
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
     * Set permission.
     *
     * @param SalesAdminPermission $permission
     *
     * @return SalesAdminPermissionMap
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Get permission.
     *
     * @return SalesAdminPermission
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Set opLevel.
     *
     * @param int $opLevel
     *
     * @return SalesAdminPermissionMap
     */
    public function setOpLevel($opLevel)
    {
        $this->opLevel = $opLevel;

        return $this;
    }

    /**
     * Get opLevel.
     *
     * @return int
     */
    public function getOpLevel()
    {
        return $this->opLevel;
    }

    /**
     * Set $admin.
     *
     * @param SalesAdmin $admin
     *
     * @return SalesAdminPermissionMap
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin.
     *
     * @return SalesAdmin
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set buildingId.
     *
     * @param int $buildingId
     *
     * @return int
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;

        return $this;
    }

    /**
     * Get buildingId.
     *
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @return array
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param array $building
     */
    public function setBuilding($building)
    {
        $this->building = $building;
    }
}
