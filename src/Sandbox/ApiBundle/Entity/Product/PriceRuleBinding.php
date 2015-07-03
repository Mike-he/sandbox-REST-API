<?php

namespace Sandbox\ApiBundle\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * PriceRuleBinding.
 *
 * @ORM\Table(name="PriceRuleBinding")
 * @ORM\Entity
 */
class PriceRuleBinding
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
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Product\Product", inversedBy="priceRule")
     *
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="productId", referencedColumnName="id")
     * })
     *
     * @Serializer\Groups({"main"})
     */
    private $product;

    /**
     * @var int
     *
     * @ORM\Column(name="priceRuleId", type="integer")
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $priceRuleId;

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
     * Set product.
     *
     * @param Product $product
     *
     * @return PriceRuleBinding
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product.
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set priceRuleId.
     *
     * @param int $priceRuleId
     *
     * @return PriceRuleBinding
     */
    public function setPriceRuleId($priceRuleId)
    {
        $this->priceRuleId = $priceRuleId;

        return $this;
    }

    /**
     * Get priceRuleId.
     *
     * @return int
     */
    public function getPriceRuleId()
    {
        return $this->priceRuleId;
    }
}
