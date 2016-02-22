<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * Admin.
 *
 * @ORM\Table(
 *      name="SalesAdmin",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="username_UNIQUE", columns={"username"})
 *      }
 * )
 * @ORM\Entity
 */
class SalesAdmin implements UserInterface
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
     * @Serializer\Groups({"main", "admin"})
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
     * @ORM\ManyToOne(targetEntity="SalesAdminType")
     * @ORM\JoinColumn(name="typeId", referencedColumnName="id")
     * @Serializer\Groups({"main", "login", "admin", "auth"})
     **/
    private $type;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="SalesAdminPermissionMap",
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
     * @return SalesAdmin
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
     * @return SalesAdmin
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
     * @return SalesAdmin
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
     * @return SalesAdmin
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
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
     * @return SalesAdmin
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
     * @return SalesAdmin
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
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

    /**
     * Set type.
     *
     * @param SalesAdminType $type
     *
     * @return SalesAdmin
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
     * @return SalesAdmin
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
     * @return SalesAdmin
     */
    public function setPermissionIds($permissionIds)
    {
        $this->permissionIds = $permissionIds;
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
        return array('ROLE_SALES_ADMIN');
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
