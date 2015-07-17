<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * AdminType.
 *
 * @ORM\Table(
 *      name="AdminType",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="key_UNIQUE", columns={"key"})}
 * )
 * @ORM\Entity
 */
class AdminType
{
    const KEY_SUPER = 'super';
    const KEY_PLATFORM = 'platform';
    const KEY_BUILDING = 'building';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "login", "admin"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=32, nullable=true)
     * @Serializer\Groups({"main", "login", "admin"})
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "login", "admin"})
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", length=15, nullable=false)
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
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="Admin",
     *      mappedBy="type"
     * )
     */
    private $admins;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="AdminPermission",
     *      mappedBy="type"
     * )
     */
    private $permissions;

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
     * @return AdminType
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
     * @return AdminType
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
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return AdminType
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
     * @return AdminType
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
     * Get admins.
     *
     * @return array
     */
    public function getAdmins()
    {
        return $this->admins;
    }

    /**
     * Get permissions.
     *
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
