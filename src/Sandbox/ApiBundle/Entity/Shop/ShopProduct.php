<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ShopProduct.
 *
 * @ORM\Table(
 *     name="ShopProduct",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="menuId_name_UNIQUE", columns={"menuId", "name"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Shop\ShopProductRepository")
 */
class ShopProduct implements JsonSerializable
{
    const SHOP_PRODUCT_CONFLICT_MESSAGE = 'Shop Product with this name already exist in this shop';

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
     * @ORM\Column(name="menuId", type="integer")
     * @Serializer\Groups({"main"})
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $menuId;

    /**
     * @var ShopMenu
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopMenu")
     * @ORM\JoinColumn(name="menuId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $menu;

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
     * @ORM\Column(name="description", type="text")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="sortTime", type="string", length=15)
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $sortTime;

    /**
     * @var bool
     *
     * @ORM\Column(name="online", type="boolean", options={"default": false})
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $online = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="invisible", type="boolean", options={"default": false})
     * @Serializer\Groups({"main"})
     */
    private $invisible = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="isOfflineByShop", type="boolean", options={"default": false})
     * @Serializer\Groups({"main"})
     */
    private $isOfflineByShop = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $modificationDate;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopProductAttachment",
     *      mappedBy="product",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="productId")
     * @ORM\OrderBy({"id" = "ASC"})
     *
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $productAttachments;

    /**
     * @var array
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $attachments;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopProductSpec",
     *      mappedBy="product",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="productId")
     * @ORM\OrderBy({"id" = "ASC"})
     *
     * @Serializer\Groups({"main", "admin_shop", "product_view"})
     */
    private $productSpecs;

    /**
     * @var array
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $specs;

    /**
     * @var array
     */
    private $shopProductSpecs;

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
     * Set menuId.
     *
     * @param int $menuId
     *
     * @return ShopProduct
     */
    public function setMenuId($menuId)
    {
        $this->menuId = $menuId;

        return $this;
    }

    /**
     * Get menuId.
     *
     * @return int
     */
    public function getMenuId()
    {
        return $this->menuId;
    }

    /**
     * Set menu.
     *
     * @param ShopMenu $menu
     *
     * @return ShopProduct
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * Get menu.
     *
     * @return ShopMenu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return ShopProduct
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
     * Set description.
     *
     * @param string $description
     *
     * @return ShopProduct
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set sortTime.
     *
     * @param string $sortTime
     *
     * @return ShopProduct
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
     * Set online.
     *
     * @param bool $online
     *
     * @return ShopProduct
     */
    public function setOnline($online)
    {
        $this->online = $online;

        return $this;
    }

    /**
     * Get online.
     *
     * @return bool
     */
    public function isOnline()
    {
        return $this->online;
    }

    /**
     * Set invisible.
     *
     * @param bool $invisible
     *
     * @return ShopProduct
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

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return ShopProduct
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
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return ShopProduct
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set product attachments.
     *
     * @param $productAttachments
     *
     * @return ShopProduct
     */
    public function setProductAttachments($productAttachments)
    {
        $this->productAttachments = $productAttachments;

        return $this;
    }

    /**
     * Get product attachments.
     *
     * @return array
     */
    public function getProductAttachments()
    {
        return $this->productAttachments;
    }

    /**
     * Set attachments.
     *
     * @param $attachments
     *
     * @return ShopProduct
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Get attachments.
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set product specs.
     *
     * @param $productSpecs
     *
     * @return ShopProduct
     */
    public function setProductSpecs($productSpecs)
    {
        $this->productSpecs = $productSpecs;

        return $this;
    }

    /**
     * Get product specs.
     *
     * @return array
     */
    public function getProductSpecs()
    {
        return $this->productSpecs;
    }

    /**
     * Set specs.
     *
     * @param $specs
     *
     * @return ShopProduct
     */
    public function setSpecs($specs)
    {
        $this->specs = $specs;

        return $this;
    }

    /**
     * Get specs.
     *
     * @return array
     */
    public function getSpecs()
    {
        return $this->specs;
    }

    /**
     * Set shopProductSpecs.
     *
     * @param $specs
     *
     * @return ShopProduct
     */
    public function setShopProductSpecs($specs)
    {
        $this->shopProductSpecs = $specs;

        return $this;
    }

    /**
     * Get shopProductSpecs.
     *
     * @return array
     */
    public function getShopProductSpecs()
    {
        return $this->shopProductSpecs;
    }

    /**
     * @return bool
     */
    public function isOfflineByShop()
    {
        return $this->isOfflineByShop;
    }

    /**
     * @param bool $isOfflineByShop
     */
    public function setIsOfflineByShop($isOfflineByShop)
    {
        $this->isOfflineByShop = $isOfflineByShop;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
        $this->setSortTime(round(microtime(true) * 1000));
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'menu' => array(
                'menuId' => $this->menuId,
                'name' => $this->menu->getName(),
            ),
            'name' => $this->name,
            'description' => $this->description,
        );
    }
}
