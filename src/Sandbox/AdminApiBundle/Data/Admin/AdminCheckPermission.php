<?php

namespace Sandbox\AdminApiBundle\Data\Admin;

/**
 * Banner Position Incoming Data.
 */
class AdminCheckPermission
{
    /**
     * @var array
     */
    private $permissions;

    /**
     * @var string
     */
    private $opLevel;

    /**
     * @var string
     */
    private $platform;

    /**
     * @var string
     */
    private $salesCompanyId;

    /**
     * @return string
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param string $permissions
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @return string
     */
    public function getOpLevel()
    {
        return $this->opLevel;
    }

    /**
     * @param string $opLevel
     */
    public function setOpLevel($opLevel)
    {
        $this->opLevel = $opLevel;
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    /**
     * @return string
     */
    public function getSalesCompanyId()
    {
        return $this->salesCompanyId;
    }

    /**
     * @param string $salesCompanyId
     */
    public function setSalesCompanyId($salesCompanyId)
    {
        $this->salesCompanyId = $salesCompanyId;
    }
}
