<?php
/**
 * User entity
 *
 * PHP version 5.3
 *
 * @category Sandbox
 * @package  ApiBundle
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 *
 */
namespace Sandbox\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="User")
 * @ORM\Entity
 *
 */
class User
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
     * @ORM\Column(name="countryCode", type="string", length=16, nullable=true)
     */
    private $countrycode;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64, nullable=true)
     */
    private $phone;

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
     * @ORM\Column(name="creationDate", type="string", length=15, nullable=false)
     */
    private $creationdate;

    /**
     * @var string
     *
     * @ORM\Column(name="modificationDate", type="string", length=15, nullable=false)
     */
    private $modificationdate;

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
     * Set password
     *
     * @param  string $password
     * @return JtUser
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
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
     * Set email
     *
     * @param  string $email
     * @return JtUser
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
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
     * Set countrycode
     *
     * @param  string $countrycode
     * @return JtUser
     */
    public function setCountrycode($countrycode)
    {
        $this->countrycode = $countrycode;

        return $this;
    }

    /**
     * Get countrycode
     *
     * @return string
     */
    public function getCountrycode()
    {
        return $this->countrycode;
    }

    /**
     * Set phone
     *
     * @param  string $phone
     * @return JtUser
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set xmppUsername
     *
     * @param  string $xmppUsername
     * @return JtUser
     */
    public function setXmppUsername($xmppUsername)
    {
        $this->xmppUsername = $xmppUsername;

        return $this;
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
     * Set activated
     *
     * @param  boolean $activated
     * @return JtUser
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
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
     * Set creationdate
     *
     * @param  string $creationdate
     * @return JtUser
     */
    public function setCreationdate($creationdate)
    {
        $this->creationdate = $creationdate.'000';

        return $this;
    }

    /**
     * Get creationdate
     *
     * @return string
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * Set modificationdate
     *
     * @param  string $modificationdate
     * @return JtUser
     */
    public function setModificationdate($modificationdate)
    {
        $this->modificationdate = $modificationdate.'000';

        return $this;
    }

    /**
     * Get modificationdate
     *
     * @return string
     */
    public function getModificationdate()
    {
        return $this->modificationdate;
    }
}
