<?php

namespace Sandbox\AdminApiBundle\Data\Product;

/**
 * Product Recommend Position Incoming Data.
 */
class ProductRecommendPosition
{
    const ACTION_UP = 'up';
    const ACTION_DOWN = 'down';
    const ACTION_TOP = 'top';

    /**
     * @var string
     */
    private $action;

    /**
     * @var int
     */
    private $cityId;

    /**
     * @var int
     */
    private $buildingId;

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

    /**
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * @param int $cityId
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;
    }

    /**
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @param int $buildingId
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;
    }
}
