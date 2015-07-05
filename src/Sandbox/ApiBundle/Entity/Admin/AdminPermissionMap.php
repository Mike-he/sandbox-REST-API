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
     * @Serializer\Groups({"main"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="adminId", type="integer", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $adminId;

    /**
     * @var int
     *
     * @ORM\Column(name="permissionId", type="integer", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $permissionId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @ORM\OneToOne(targetEntity="AdminPermission"))
     * @ORM\JoinColumn(name="permissionId", referencedColumnName="id")
     * @Serializer\Groups({"main", "login", "admin"})
     **/
    private $permission;

    /**
     * @ORM\ManyToOne(targetEntity="Admin", inversedBy="permissions")
     * @ORM\JoinColumn(name="adminId", referencedColumnName="id")
     * @Serializer\Groups({"main"})
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
    public function SetPermission($permission)
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
