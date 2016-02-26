<?php

namespace Sandbox\AdminShopApiBundle\Data\Shop;

/**
 * Shop Product Spec Incoming Data.
 */
class ShopProductSpecData
{
    /**
     * @var array
     */
    private $items;

    /**
     * Get items.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Set items.
     *
     * @param array $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }
}
