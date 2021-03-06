<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ShopMenu.
 *
 * @ORM\Table(name="shop_menu")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Shop\ShopMenuRepository")
 */
class ShopMenu implements JsonSerializable
{
    const SHOP_MENU_CONFLICT_MESSAGE = 'Menu with the same name already exist in this shop';

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
     * @ORM\Column(name="shopId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $shopId;

    /**
     * @var Shop
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\Shop")
     * @ORM\JoinColumn(name="shopId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main"})
     */
    private $shop;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="sortTime", type="string", length=15)
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $sortTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $creationDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="invisible", type="boolean", options={"default": false})
     * @Serializer\Groups({"main"})
     */
    private $invisible = false;

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
     * @return ShopMenu
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
     * Set Shop.
     *
     * @param Shop $shop
     *
     * @return ShopMenu
     */
    public function setShop($shop)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * Get Shop.
     *
     * @return Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return ShopMenu
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
     * Set sortTime.
     *
     * @param string $sortTime
     *
     * @return ShopMenu
     */
    public function setSortTime($sortTime)
    {
        $this->sortTime = $sortTime;

        return $this;
    }

    /**
     * Get sortTime.
     *
     * @return string
     */
    public function getSortTime()
    {
        return $this->sortTime;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return ShopMenu
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

    /**
     * Set invisible.
     *
     * @param bool $invisible
     *
     * @return ShopMenu
     */
    public function setInvisible($invisible)
    {
        $this->invisible = $invisible;

        return $this;
    }

    /**
     * Get invisible.
     *
     * @return bool
     */
    public function isInvisible()
    {
        return $this->invisible;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setSortTime(round(microtime(true) * 1000));
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
        );
    }
}
