<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JsonSerializable;

/**
 * ShopMenu.
 *
 * @ORM\Table(name="ShopMenu")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Shop\ShopMenuRepository")
 */
class ShopMenu implements JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="shopId", type="integer")
     * @Serializer\Groups({"main", "admin_shop"})
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
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="count", type="integer")
     * @Serializer\Groups({"main", "admin_shop"})
     */
    private $count = 0;

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
     * @var array
     */
    private $add;

    /**
     * @var array
     */
    private $modify;

    /**
     * @var array
     */
    private $remove;

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
     * Set count.
     *
     * @param int $count
     *
     * @return ShopMenu
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
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
     * Set add.
     *
     * @param array $add
     *
     * @return ShopMenu
     */
    public function setAdd($add)
    {
        $this->add = $add;

        return $this;
    }

    /**
     * Get add.
     *
     * @return array
     */
    public function getAdd()
    {
        return $this->add;
    }

    /**
     * Set modify.
     *
     * @param array $modify
     *
     * @return ShopMenu
     */
    public function setModify($modify)
    {
        $this->modify = $modify;

        return $this;
    }

    /**
     * Get modify.
     *
     * @return array
     */
    public function getModify()
    {
        return $this->modify;
    }

    /**
     * Set remove.
     *
     * @param array $remove
     *
     * @return ShopMenu
     */
    public function setRemove($remove)
    {
        $this->remove = $remove;

        return $this;
    }

    /**
     * Get remove.
     *
     * @return array
     */
    public function getRemove()
    {
        return $this->remove;
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
