<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * AdminPermissionMap.
 *
 * @ORM\Table(
 *      name="ShopAdminPermissionMap",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="adminId_permissionId_shopId_UNIQUE", columns={"adminId", "permissionId", "shopId"})
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_AdminPermissionMap_adminId_idx", columns={"adminId"}),
 *          @ORM\Index(name="fk_AdminPermissionMap_permissionId_idx", columns={"permissionId"}),
 *          @ORM\Index(name="fk_AdminPermissionMap_shopId_idx", columns={"shopId"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Shop\ShopAdminPermissionMapRepository")
 */
class ShopAdminPermissionMap
{
    const OP_LEVEL_VIEW = 1;
    const OP_LEVEL_EDIT = 2;

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
     * @ORM\ManyToOne(targetEntity="ShopAdminPermission")
     * @ORM\JoinColumn(name="permissionId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     **/
    private $permission;

    /**
     * @var int
     *
     * @ORM\Column(name="opLevel", type="integer", nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     */
    private $opLevel;

    /**
     * @ORM\ManyToOne(targetEntity="ShopAdmin")
     * @ORM\JoinColumn(name="adminId", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $admin;

    /**
     * @var int
     *
     * @ORM\Column(name="shopId", type="integer", nullable=true)
     * @Serializer\Groups({"main", "login", "auth"})
     */
    private $shopId;

    /**
     * @var array
     *
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     */
    private $shop;

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
     * @return ShopAdminPermissionMap
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
     * @return ShopAdminPermissionMap
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
     * @return ShopAdminPermissionMap
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
     * @param ShopAdminPermission $permission
     *
     * @return ShopAdminPermissionMap
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Get permission.
     *
     * @return ShopAdminPermissionMap
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
     * @return ShopAdminPermissionMap
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
     * @param ShopAdmin $admin
     *
     * @return ShopAdminPermissionMap
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin.
     *
     * @return ShopAdmin
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set shopId.
     *
     * @param int $shopId
     *
     * @return int
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * Get shopId.
     *
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @return array
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param array $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }
}
