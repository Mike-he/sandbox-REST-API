<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * AdminPermissionMap.
 *
 * @ORM\Table(
 *      name="AdminPermissionMap",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="adminId_permissionId_UNIQUE", columns={"adminId", "permissionId"})
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_AdminPermissionMap_adminId_idx", columns={"adminId"}),
 *          @ORM\Index(name="fk_AdminPermissionMap_permissionId_idx", columns={"permissionId"})
 *      }
 * )
 * @ORM\Entity
 */
class AdminPermissionMap
{
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
     * @ORM\ManyToOne(targetEntity="AdminPermission", inversedBy="permissionMap")
     * @ORM\JoinColumn(name="permissionId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     **/
    private $permission;

    /**
     * @ORM\ManyToOne(targetEntity="Admin", inversedBy="permissions")
     * @ORM\JoinColumn(name="adminId", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $admin;

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
     * @return AdminPermissionMap
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
     * @return AdminPermissionMap
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
     * @return AdminPermissionMap
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
     * @param AdminPermission $permission
     *
     * @return AdminPermissionMap
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Get permission.
     *
     * @return AdminPermission
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Set $admin.
     *
     * @param Admin $admin
     *
     * @return AdminPermissionMap
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin.
     *
     * @return Admin
     */
    public function getAdmin()
    {
        return $this->admin;
    }
}
