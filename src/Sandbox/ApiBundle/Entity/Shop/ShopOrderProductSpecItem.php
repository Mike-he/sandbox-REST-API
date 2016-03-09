<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ShopOrderProduct.
 *
 * @ORM\Table(
 *     name="ShopOrderProductSpecItem",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="specId_itemId_UNIQUE", columns={"specId", "itemId"})
 *     }
 * )
 * @ORM\Entity
 */
class ShopOrderProductSpecItem
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main"})
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
     * @var ShopOrderProductSpec
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopOrderProductSpec")
     * @ORM\JoinColumn(name="specId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main"})
     */
    private $spec;

    /**
     * @var int
     *
     * @ORM\Column(name="itemId", type="integer", nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $itemId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopProductSpecItem")
     * @ORM\JoinColumn(name="itemId", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({"main"})
     **/
    private $item;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="integer", nullable=true)
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="shopProductSpecItemInfo", type="text")
     * @Serializer\Groups({"main", "admin_shop", "client_order"})
     */
    private $shopProductSpecItemInfo;

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
     * Set specId.
     *
     * @param int $specId
     *
     * @return ShopOrderProductSpecItem
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
     * Set spec.
     *
     * @param ShopOrderProductSpec $spec
     *
     * @return ShopOrderProductSpecItem
     */
    public function setSpec($spec)
    {
        $this->spec = $spec;

        return $this;
    }

    /**
     * Get spec.
     *
     * @return ShopOrderProductSpec
     */
    public function getSpec()
    {
        return $this->spec;
    }

    /**
     * Set itemId.
     *
     * @param int $itemId
     *
     * @return ShopOrderProductSpecItem
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * Get itemId.
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set item.
     *
     * @param $item
     *
     * @return ShopOrderProductSpecItem
     */
    public function setItem($item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item.
     *
     * @return ShopProductSpecItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set amount.
     *
     * @param int $amount
     *
     * @return ShopOrderProductSpecItem
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set shopProductSpecItemInfo.
     *
     * @param string $info
     *
     * @return ShopOrderProductSpecItem
     */
    public function setShopProductSpecItemInfo($info)
    {
        $this->shopProductSpecItemInfo = $info;

        return $this;
    }

    /**
     * Get shopProductSpecItemInfo.
     *
     * @return string
     */
    public function getShopProductSpecItemInfo()
    {
        return $this->shopProductSpecItemInfo;
    }
}
