<?php

namespace Sandbox\ShopApiBundle\Data\Shop;

/**
 * Shop Spec Item Incoming Data.
 */
class ShopSpecItemData
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $inventory;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get inventory.
     *
     * @return bool
     */
    public function getInventory()
    {
        return $this->inventory;
    }

    /**
     * Set Inventory.
     *
     * @param bool $inventory
     */
    public function setInventory($inventory)
    {
        $this->inventory = $inventory;
    }
}
