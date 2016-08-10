<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;

/**
 * SalesAdminExcludePermission.
 *
 * @ORM\Table(
 *     name="SalesAdminExcludePermission",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="permissionId_companyId_UNIQUE", columns={"permissionId", "salesCompanyId"})
 *      }
 * )
 * @ORM\Entity
 */
class SalesAdminExcludePermission
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
     * @var int
     *
     * @ORM\Column(name="salesCompanyId", type="integer", nullable=false)
     */
    private $salesCompanyId;

    /**
     * @ORM\ManyToOne(targetEntity="SalesCompany")
     * @ORM\JoinColumn(name="salesCompanyId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $salesCompany;

    /**
     * @var int
     *
     * @ORM\Column(name="permissionId", type="integer", nullable=false)
     */
    private $permissionId;

    /**
     * @ORM\ManyToOne(targetEntity="SalesAdminPermission")
     * @ORM\JoinColumn(name="permissionId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $permission;

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
     * Set salesCompanyId.
     *
     * @param int $salesCompanyId
     *
     * @return SalesAdminExcludePermission
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
     * @return SalesAdminExcludePermission
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
}
