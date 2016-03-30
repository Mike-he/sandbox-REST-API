<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * AdminType.
 *
 * @ORM\Table(
 *      name="ShopAdminType",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="key_UNIQUE", columns={"key"})}
 * )
 * @ORM\Entity
 */
class ShopAdminType
{
    const KEY_SUPER = 'super';
    const KEY_PLATFORM = 'platform';

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
     * @ORM\Column(name="key", type="string", length=32, nullable=true)
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
     * @return ShopAdminType
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
     * @return ShopAdminType
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
     * @return ShopAdminType
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
     * @return ShopAdminType
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
}
