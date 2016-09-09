<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use JMS\Serializer\Annotation as Serializer;

/**
 * ShopSpecItem.
 *
 * @ORM\Table(name="shop_spec_item")
 * @ORM\Entity
 */
class ShopSpecItem implements JsonSerializable
{
    const AUTO_SPEC_ITEM_NAME = 'SPEC ITEM NONE';
    const SHOP_SPEC_ITEM_CONFLICT_MESSAGE = 'SpecItem with the same name already exist in this Spec';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="specId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $specId;

    /**
     * @var ShopSpec
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopSpec")
     * @ORM\JoinColumn(name="specId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main"})
     */
    private $spec;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $name;

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
     * Set name.
     *
     * @param string $name
     *
     * @return ShopSpecItem
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Set specId.
     *
     * @param $specId
     *
     * @return ShopSpecItem
     */
    public function setSpecId($specId)
    {
        $this->specId = $specId;

        return $this;
    }

    /**
     * Get specId.
     *
     * @return int
     */
    public function getSpecId()
    {
        return $this->specId;
    }

    /**
     * Get Spec.
     *
     * @return ShopSpec
     */
    public function getSpec()
    {
        return $this->spec;
    }

    /**
     * Set Spec.
     *
     * @param ShopSpec $spec
     *
     * @return ShopSpec
     */
    public function setSpec($spec)
    {
        $this->spec = $spec;

        return $this;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
        );
    }
}
