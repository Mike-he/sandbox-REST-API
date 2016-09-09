<?php

namespace Sandbox\AdminShopApiBundle\Entity\Auth;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use Sandbox\ApiBundle\Entity\Shop\ShopAdmin;

/**
 * AdminApiAuth.
 *
 * @ORM\Table(
 *      name="shop_admin_api_auth_view",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="token_UNIQUE", columns={"token"})}
 * )
 * @ORM\Entity
 */
class ShopAdminApiAuth implements UserInterface
{
    const ROLE_SHOP_ADMIN_API = 'ROLE_SHOP_ADMIN_API';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=64, nullable=true)
     */
    private $token;

    /**
     * @var int
     *
     * @ORM\Column(name="clientId", type="integer", nullable=true)
     */
    private $clientId;

    /**
     * @var int
     *
     * @ORM\Column(name="adminId", type="integer", nullable=true)
     */
    private $adminId;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=64, nullable=true)
     */
    private $username;

    /**
     * @var ShopAdmin
     *
     * @ORM\OneToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\ShopAdmin"))
     * @ORM\JoinColumn(name="adminId", referencedColumnName="id")
     **/
    private $myAdmin;

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
     * Get token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get clientId.
     *
     * @return int
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Get adminId.
     *
     * @return int
     */
    public function getAdminId()
    {
        return $this->adminId;
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
     * Get myAdmin.
     *
     * @return ShopAdmin
     */
    public function getMyAdmin()
    {
        return $this->myAdmin;
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
        return array('ROLE_SHOP_ADMIN_API');
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

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        // token as the username
        // clientId as the password
        return $this->clientId;
    }
}
