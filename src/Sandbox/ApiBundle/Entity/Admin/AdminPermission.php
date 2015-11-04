<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * AdminPermission.
 *
 * @ORM\Table(
 *      name="AdminPermission",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="key_UNIQUE", columns={"key"})},
 *      indexes={@ORM\Index(name="fk_AdminPermission_typeId_idx", columns={"typeId"})}
 * )
 * @ORM\Entity
 */
class AdminPermission
{
    const KEY_PLATFORM_ORDER = 'platform.order';
    const KEY_PLATFORM_USER = 'platform.user';
    const KEY_PLATFORM_ROOM = 'platform.room';
    const KEY_PLATFORM_PRODUCT = 'platform.product';
    const KEY_PLATFORM_PRICE = 'platform.price';
    const KEY_PLATFORM_ACCESS = 'platform.access';
    const KEY_PLATFORM_ADMIN = 'platform.admin';
    const KEY_PLATFORM_ANNOUNCEMENT = 'platform.announcement';
    const KEY_PLATFORM_DASHBOARD = 'platform.dashboard';
    const KEY_PLATFORM_EVENT = 'platform.event';
    const KEY_PLATFORM_BANNER = 'platform.banner';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=32, nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth"})
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
     * @var AdminType
     *
     * @ORM\ManyToOne(targetEntity="AdminType")
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
     * @return AdminPermission
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
     * @return AdminPermission
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
     * @return AdminPermission
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
     * @return AdminPermission
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
     * @return AdminPermission
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
     * @return AdminType
     */
    public function getType()
    {
        return $this->type;
    }
}
