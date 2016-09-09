<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * AdminPermission.
 *
 * @ORM\Table(
 *      name="shop_admin_permission",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="key_UNIQUE", columns={"key"})},
 *      indexes={@ORM\Index(name="fk_AdminPermission_typeId_idx", columns={"typeId"})}
 * )
 * @ORM\Entity
 */
class ShopAdminPermission
{
    const KEY_PLATFORM_DASHBOARD = 'shop.platform.dashboard';
    const KEY_PLATFORM_ADMIN = 'shop.platform.admin';
    const KEY_PLATFORM_SHOP = 'shop.platform.shop';
    const KEY_PLATFORM_SPEC = 'shop.platform.spec';

    const KEY_SHOP_SHOP = 'shop.shop.shop';
    const KEY_SHOP_ORDER = 'shop.shop.order';
    const KEY_SHOP_PRODUCT = 'shop.shop.product';
    const KEY_SHOP_KITCHEN = 'shop.shop.kitchen';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "login", "admin", "auth", "admin_basic"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=128, nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth", "admin_basic"})
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth", "admin_basic"})
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="typeId", type="integer", nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     */
    private $typeId;

    /**
     * @var ShopAdminType
     *
     * @ORM\ManyToOne(targetEntity="ShopAdminType")
     * @ORM\JoinColumn(name="typeId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main"})
     **/
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $modificationDate;

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
     * Set key.
     *
     * @param string $key
     *
     * @return ShopAdminPermission
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return ShopAdminPermission
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
     * Set typeId.
     *
     * @param int $typeId
     *
     * @return ShopAdminPermission
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId.
     *
     * @return int
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return ShopAdminPermission
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
     * @return ShopAdminPermission
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
     * Get type.
     *
     * @return ShopAdminType
     */
    public function getType()
    {
        return $this->type;
    }
}
