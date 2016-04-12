<?php

namespace Sandbox\ApiBundle\Entity\Shop;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as Serializer;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;

/**
 * Admin.
 *
 * @ORM\Table(
 *      name="ShopAdmin",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="username_UNIQUE", columns={"username"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Shop\ShopAdminRepository")
 */
class ShopAdmin implements UserInterface
{
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
     * @ORM\Column(name="username", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=256, nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=true)
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="typeId", type="integer", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $typeId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main", "admin"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main", "admin"})
     */
    private $modificationDate;

    /**
     * @ORM\ManyToOne(targetEntity="ShopAdminType")
     * @ORM\JoinColumn(name="typeId", referencedColumnName="id")
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     **/
    private $type;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="ShopAdminPermissionMap",
     *      mappedBy="admin"
     * )
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     */
    private $permissions;

    /**
     * @var array
     */
    private $permissionIds;

    /**
     * @var int
     *
     * @ORM\Column(name="companyId", type="integer", nullable=false)
     * @Serializer\Groups({"main", "admin"})
     */
    private $companyId;

    /**
     * @var SalesCompany
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany")
     * @ORM\JoinColumn(name="companyId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $salesCompany;

    /**
     * @var bool
     *
     * @ORM\Column(name="defaultPasswordChanged", type="boolean", nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     */
    private $defaultPasswordChanged = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="banned", type="boolean", nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     */
    private $banned = false;

    /**
     * @var int
     *
     * @Serializer\Groups({"main", "admin"})
     */
    private $buildingCounts;

    /**
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * @param int $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * @return SalesCompany
     */
    public function getSalesCompany()
    {
        return $this->salesCompany;
    }

    /**
     * @param SalesCompany $salesCompany
     */
    public function setSalesCompany($salesCompany)
    {
        $this->salesCompany = $salesCompany;
    }

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
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username.
     *
     * @param string $username
     *
     * @return ShopAdmin
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return ShopAdmin
     */
    public function setPassword($password)
    {
        $this->password = $password;
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
     * Set name.
     *
     * @param string $name
     *
     * @return ShopAdmin
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * Set typeId.
     *
     * @param int $typeId
     *
     * @return ShopAdmin
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
    }

    /**
     * @return bool
     */
    public function isDefaultPasswordChanged()
    {
        return $this->defaultPasswordChanged;
    }

    /**
     * @param bool $defaultPasswordChanged
     */
    public function setDefaultPasswordChanged($defaultPasswordChanged)
    {
        $this->defaultPasswordChanged = $defaultPasswordChanged;
    }

    /**
     * @return bool
     */
    public function isBanned()
    {
        return $this->banned;
    }

    /**
     * @param bool $banned
     */
    public function setBanned($banned)
    {
        $this->banned = $banned;
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
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return ShopAdmin
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
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
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return ShopAdmin
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * Get type.
     *
     * @return ShopAdmin
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param ShopAdminType $type
     *
     * @return ShopAdmin
     */
    public function setType($type)
    {
        $this->type = $type;
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

    /**
     * Set permissions.
     *
     * @param array $permissions
     *
     * @return ShopAdmin
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @return array
     */
    public function getPermissionIds()
    {
        return $this->permissionIds;
    }

    /**
     * @param array $permissionIds
     *
     * @return ShopAdmin
     */
    public function setPermissionIds($permissionIds)
    {
        $this->permissionIds = $permissionIds;
    }

    /**
     * @return int
     */
    public function getBuildingCounts()
    {
        return $this->buildingCounts;
    }

    /**
     * @param int $buildingCounts
     */
    public function setBuildingCounts($buildingCounts)
    {
        $this->buildingCounts = $buildingCounts;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return array('ROLE_SHOP_ADMIN');
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}