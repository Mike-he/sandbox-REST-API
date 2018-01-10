<?php

namespace Sandbox\CommnueAdminApiBundle\Data\Advertising;

/**
 * Advertising Position Incoming Data.
 */
class AdvertisingPosition
{
    const ACTION_UP = 'up';
    const ACTION_DOWN = 'down';
    const ACTION_TOP = 'top';

    /**
     * @var string
     */
    private $action;

    /**
     * Get action.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set action.
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
}
