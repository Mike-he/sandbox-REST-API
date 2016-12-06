<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AdminPermissionGroupMap.
 *
 * @ORM\Table(
 *     name="admin_permission_group_map",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="group_permission_UNIQUE", columns={"group_id", "permission_id"})
 *     },
 *     indexes={
 *          @ORM\Index(name="fk_adminPermissionGroup_group_idx", columns={"group_id"}),
 *          @ORM\Index(name="fk_adminPermission_permission_idx", columns={"permission_id"})
 *     }
 * )
 * @ORM\Entity
 */
class AdminPermissionGroupMap
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
     * @ORM\ManyToOne(targetEntity="AdminPermissionGroups")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity="AdminPermission")
     * @ORM\JoinColumn(name="permission_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $permission;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
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
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return AdminPermissionGroupMap
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
}
