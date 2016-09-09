<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShopCart.
 *
 * @ORM\Table(name="shop_cart")
 * @ORM\Entity
 */
class ShopCart
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
     * @ORM\Column(name="shopId", type="integer")
     */
    private $shopId;

    /**
     * @var string
     *
     * @ORM\Column(name="cartInfo", type="text")
     */
    private $cartInfo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

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
     * Set shopId.
     *
     * @param int $shopId
     *
     * @return ShopCart
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * Get shopId.
     *
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * Set cartInfo.
     *
     * @param string $cartInfo
     *
     * @return ShopCart
     */
    public function setCartInfo($cartInfo)
    {
        $this->cartInfo = $cartInfo;

        return $this;
    }

    /**
     * Get cartInfo.
     *
     * @return string
     */
    public function getCartInfo()
    {
        return $this->cartInfo;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return ShopCart
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
    }
}
