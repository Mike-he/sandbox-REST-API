<?php

namespace Sandbox\ApiBundle\Entity\Food;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * FoodItemOption.
 *
 * @ORM\Table(name="food_item_option")
 * @ORM\Entity
 */
class FoodItemOption implements JsonSerializable
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
     * @ORM\Column(name="optionId", type="integer")
     */
    private $optionId;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
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
     * Set optionId.
     *
     * @param int $optionId
     *
     * @return FoodItemOption
     */
    public function setOptionId($optionId)
    {
        $this->optionId = $optionId;

        return $this;
    }

    /**
     * Get optionId.
     *
     * @return int
     */
    public function getOptionId()
    {
        return $this->optionId;
    }

    /**
     * Set quantity.
     *
     * @param int $quantity
     *
     * @return FoodItemOption
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
