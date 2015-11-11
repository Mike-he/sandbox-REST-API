<?php

namespace Sandbox\AdminApiBundle\Data\Banner;

/**
 * Banner Position Incoming Data.
 */
class BannerPosition
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
