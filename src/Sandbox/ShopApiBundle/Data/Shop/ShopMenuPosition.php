<?php

namespace Sandbox\ShopApiBundle\Data\Shop;

/**
 * Shop Menu Position Incoming Data.
 */
class ShopMenuPosition
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
