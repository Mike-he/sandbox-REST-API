<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminPermissionMap
 *
 * @ORM\Table(
 *      name="AdminPermissionMap",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="username_permissionId_UNIQUE", columns={"username", "permissionId"})
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_AdminPermissionMap_username_idx", columns={"username"}),
 *          @ORM\Index(name="fk_AdminPermissionMap_permissionId_idx", columns={"permissionId"})
 *      }
 * )
 * @ORM\Entity
 */
class AdminPermissionMap
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=64, nullable=false)
     */
    private $username;

    /**
     * @var integer
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param  string             $username
     * @return AdminPermissionMap
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set permissionId
     *
     * @param  integer            $permissionId
     * @return AdminPermissionMap
     */
    public function setPermissionId($permissionId)
    {
        $this->permissionId = $permissionId;

        return $this;
    }

    /**
     * Get permissionId
     *
     * @return integer
     */
    public function getPermissionId()
    {
        return $this->permissionId;
    }

    /**
     * Set creationDate
     *
     * @param  \DateTime          $creationDate
     * @return AdminPermissionMap
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Get permission
     *
     * @return AdminPermission
     */
    public function getPermission()
    {
        return $this->permission;
    }
}
