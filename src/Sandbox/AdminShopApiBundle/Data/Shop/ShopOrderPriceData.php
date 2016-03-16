<?php

namespace Sandbox\AdminShopApiBundle\Data\Shop;

/**
 * Shop Order Price Data.
 */
class ShopOrderPriceData
{
    /**
     * @var float
     */
    private $productPrice;

    /**
     * @var float
     */
    private $specPrice;

    /**
     * @var float
     */
    private $itemPrice;

    /**
     * Get productPrice.
     *
     * @return float
     */
    public function getProductPrice()
    {
        return $this->productPrice;
    }

    /**
     * Set productPrice.
     *
     * @param float $productPrice
     */
    public function setProductPrice($productPrice)
    {
        $this->productPrice = $productPrice;
    }

    /**
     * Get specPrice.
     *
     * @return float
     */
    public function getSpecPrice()
    {
        return $this->specPrice;
    }

    /**
     * Set specPrice.
     *
     * @param float $specPrice
     */
    public function setSpecPrice($specPrice)
    {
        $this->specPrice = $specPrice;
    }

    /**
     * Get itemPrice.
     *
     * @return float
     */
    public function getItemPrice()
    {
        return $this->itemPrice;
    }

    /**
     * Set itemPrice.
     *
     * @param float $itemPrice
     */
    public function setItemPrice($itemPrice)
    {
        $this->itemPrice = $itemPrice;
    }
}
