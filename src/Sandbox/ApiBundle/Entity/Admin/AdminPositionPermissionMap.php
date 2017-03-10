<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * AdminPositionPermissionMap.
 *
 * @ORM\Table(name="admin_position_permission_map")
 * @ORM\Entity
 */
class AdminPositionPermissionMap
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "admin", "admin_position_bind_view"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="positionId", type="integer")
     */
    private $positionId;

    /**
     * @ORM\ManyToOne(targetEntity="AdminPosition")
     * @ORM\JoinColumn(name="positionId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $position;

    /**
     * @var int
     *
     * @ORM\Column(name="permissionId", type="integer")
     */
    private $permissionId;

    /**
     * @ORM\ManyToOne(targetEntity="AdminPermission")
     * @ORM\JoinColumn(name="permissionId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main", "admin", "admin_position_bind_view"})
     */
    private $permission;

    /**
     * @var int
     *
     * @ORM\Column(name="opLevel", type="integer")
     * @Serializer\Groups({"main", "admin", "admin_position_bind_view"})
     */
    private $opLevel;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
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
     * Set positionId.
     *
     * @param int $positionId
     *
     * @return AdminPositionPermissionMap
     */
    public function setPositionId($positionId)
    {
        $this->positionId = $positionId;

        return $this;
    }

    /**
     * Get positionId.
     *
     * @return int
     */
    public function getPositionId()
    {
        return $this->positionId;
    }

    /**
     * Set permissionId.
     *
     * @param int $permissionId
     *
     * @return AdminPositionPermissionMap
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
     * Set opLevel.
     *
     * @param int $opLevel
     *
     * @return AdminPositionPermissionMap
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
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
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
     * @return AdminPositionPermissionMap
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

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
    }
}
