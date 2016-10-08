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
}
