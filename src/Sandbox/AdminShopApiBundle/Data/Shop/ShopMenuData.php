<?php

namespace Sandbox\AdminShopApiBundle\Data\Shop;

/**
 * ShopMenu Data.
 */
class ShopMenuData
{
    /**
     * @var array
     */
    private $add;

    /**
     * @var array
     */
    private $modify;

    /**
     * @var array
     */
    private $remove;

    /**
     * Set add.
     *
     * @param array $add
     *
     * @return ShopMenuData
     */
    public function setAdd($add)
    {
        $this->add = $add;

        return $this;
    }

    /**
     * Get add.
     *
     * @return array
     */
    public function getAdd()
    {
        return $this->add;
    }

    /**
     * Set modify.
     *
     * @param array $modify
     *
     * @return ShopMenuData
     */
    public function setModify($modify)
    {
        $this->modify = $modify;

        return $this;
    }

    /**
     * Get modify.
     *
     * @return array
     */
    public function getModify()
    {
        return $this->modify;
    }

    /**
     * Set remove.
     *
     * @param array $remove
     *
     * @return ShopMenuData
     */
    public function setRemove($remove)
    {
        $this->remove = $remove;

        return $this;
    }

    /**
     * Get remove.
     *
     * @return array
     */
    public function getRemove()
    {
        return $this->remove;
    }
}
