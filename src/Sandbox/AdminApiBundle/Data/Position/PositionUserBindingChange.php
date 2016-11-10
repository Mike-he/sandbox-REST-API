<?php

namespace Sandbox\AdminApiBundle\Data\Position;

/**
 * Bulletin Position Incoming Data.
 */
class PositionUserBindingChange
{
    /**
     * @var array
     */
    private $add;

    /**
     * @var array
     */
    private $delete;

    /**
     * @return array
     */
    public function getAdd()
    {
        return $this->add;
    }

    /**
     * @param array $add
     */
    public function setAdd($add)
    {
        $this->add = $add;
    }

    /**
     * @return array
     */
    public function getDelete()
    {
        return $this->delete;
    }

    /**
     * @param array $delete
     */
    public function setDelete($delete)
    {
        $this->delete = $delete;
    }
}
