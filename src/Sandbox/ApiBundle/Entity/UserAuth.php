<?php

namespace Sandbox\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserAuth
 *
 * @ORM\Table(name="UserAuthView")
 * @ORM\Entity
 *
 */
class UserAuth implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="xmppUsername", type="string", length=64, nullable=false)
     */
    private $xmppUsername;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activated", type="boolean", nullable=true)
     */
    private $activated = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=256, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=128, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phoneNum", type="string", length=80, nullable=true)
     */
    private $phonenum;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get xmppUsername
     *
     * @return string
     */
    public function getXmppUsername()
    {
        return $this->xmppUsername;
    }

    /**
     * Get activated
     *
     * @return boolean
     */
    public function getActivated()
    {
        return $this->activated;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * Get phonenum
     *
     * @return string
     */
    public function getPhonenum()
    {
        return $this->phonenum;
    }

    ///////////////////////////
    // Interface UserInterface
    ///////////////////////////

    public function getSalt()
    {
        return;
    }

    public function getUsername()
    {
        $this->id;
    }

    public function getRoles()
    {
        return array('ROLE_API');
    }

    public function eraseCredentials()
    {
    }
}
