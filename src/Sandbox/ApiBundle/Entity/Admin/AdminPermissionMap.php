<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * AdminPermissionMap.
 *
 * @ORM\Table(
 *      name="admin_permission_map",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="adminId_permissionId_UNIQUE", columns={"userId", "permissionId", "buildingId"})
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_AdminPermissionMap_userId_idx", columns={"userId"}),
 *          @ORM\Index(name="fk_AdminPermissionMap_permissionId_idx", columns={"permissionId"}),
 *          @ORM\Index(name="fk_AdminPermissionMap_buildingId_idx", columns={"buildingId"})
 *      }
 * )
 * @ORM\Entity
 */
class AdminPermissionMap
{
    const OP_LEVEL_VIEW = 1;
    const OP_LEVEL_EDIT = 2;
    const OP_LEVEL_USER_BANNED = 3;

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
     * @ORM\Column(name="userId", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="permissionId", type="integer", nullable=false)
     */
    private $permissionId;

    /**
     * @ORM\ManyToOne(targetEntity="AdminPermission")
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
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer", nullable=true)
     * @Serializer\Groups({"main", "login", "admin", "auth", "admin_basic"})
     */
    private $buildingId;

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
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
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
     * Set opLevel.
     *
     * @param int $opLevel
     *
     * @return AdminPermissionMap
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
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @param int $buildingId
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;
    }

    /**
     * AdminPermissionMap constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
    }
}
