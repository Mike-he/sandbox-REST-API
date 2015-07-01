<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\OneToOne(targetEntity="AdminPermission"))
     * @ORM\JoinColumn(name="permissionId", referencedColumnName="id")
     **/
    private $permission;

    /**
     * @ORM\ManyToOne(targetEntity="Admin", inversedBy="permissionIds")
     * @ORM\JoinColumn(name="adminId", referencedColumnName="id")
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
     * Get permission.
     *
     * @return AdminPermission
     */
    public function getPermission()
    {
        return $this->permission;
    }
}
