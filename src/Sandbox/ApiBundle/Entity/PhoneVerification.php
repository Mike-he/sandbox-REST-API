<?php
/**
 * PhoneVerification entity
 *
 * PHP version 5.3
 *
 * @category Sandbox
 * @package  ApiBundle
 * @author   Yimo Zhang
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 *
 */
namespace Sandbox\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Phone verification
 *
 * @ORM\Table(name="ezPhoneVerification")
 * @ORM\Entity
 *
 */
class PhoneVerification
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
     * @var int
     *
     * @ORM\Column(name="userId", type="integer", nullable=false)
     */
    private $userid;

    /**
     * @var string
     *
     * @ORM\Column(name="countryCode", type="string", length=16, nullable=false)
     */
    private $countrycode;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64, nullable=false)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=64, nullable=false)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=16, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="creationDate", type="string", length=15, nullable=false)
     */
    private $creationdate;

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
     * Set userid
     *
     * @param  string            $userid
     * @return PhoneVerification
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Get userid
     *
     * @return string
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Set countrycode
     *
     * @param  string            $countrycode
     * @return PhoneVerification
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
     * @param  string            $phone
     * @return PhoneVerification
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
     * Set token
     *
     * @param  string            $token
     * @return PhoneVerification
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set code
     *
     * @param  string            $code
     * @return PhoneVerification
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set creationdate
     *
     * @param  string            $creationdate
     * @return PhoneVerification
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
}
