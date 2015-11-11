<?php

namespace Sandbox\ApiBundle\Entity\Food;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * FoodItem.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class FoodItem implements JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="foodId", type="integer")
     */
    private $foodId;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;

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
     * Set foodId.
     *
     * @param int $foodId
     *
     * @return FoodItem
     */
    public function setFoodId($foodId)
    {
        $this->foodId = $foodId;

        return $this;
    }

    /**
     * Get foodId.
     *
     * @return int
     */
    public function getFoodId()
    {
        return $this->foodId;
    }

    /**
     * Set quantity.
     *
     * @param int $quantity
     *
     * @return FoodItem
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity.
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    public function jsonSerialize()
    {
        return array(
            'quantity' => $this->quantity,
        );
    }
}
