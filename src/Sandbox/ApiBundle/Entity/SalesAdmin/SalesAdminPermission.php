<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * AdminPermission.
 *
 * @ORM\Table(
 *      name="SalesAdminPermission",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="key_UNIQUE", columns={"key"})},
 *      indexes={@ORM\Index(name="fk_AdminPermission_typeId_idx", columns={"typeId"})}
 * )
 * @ORM\Entity
 */
class SalesAdminPermission
{
    const KEY_PLATFORM_DASHBOARD = 'sales.platform.dashboard';
    const KEY_PLATFORM_ADMIN = 'sales.platform.admin';
    const KEY_PLATFORM_BUILDING = 'sales.platform.building';
    const KEY_PLATFORM_FINANCE = 'sales.platform.invoice';

    const KEY_BUILDING_PRICE = 'sales.building.price';
    const KEY_BUILDING_ORDER = 'sales.building.order';
    const KEY_BUILDING_ORDER_RESERVE = 'sales.building.order.reserve';
    const KEY_BUILDING_ORDER_PREORDER = 'sales.building.order.preorder';
    const KEY_BUILDING_BUILDING = 'sales.building.building';
    const KEY_BUILDING_USER = 'sales.building.user';
    const KEY_BUILDING_ROOM = 'sales.building.room';
    const KEY_BUILDING_PRODUCT = 'sales.building.product';
    const KEY_BUILDING_ACCESS = 'sales.building.access';

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
     * @var SalesAdminType
     *
     * @ORM\ManyToOne(targetEntity="SalesAdminType")
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
     * @return SalesAdminPermission
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
     * @return SalesAdminPermission
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
     * @return SalesAdminPermission
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
     * @return SalesAdminPermission
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
     * @return SalesAdminPermission
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
     * @return SalesAdminType
     */
    public function getType()
    {
        return $this->type;
    }
}
