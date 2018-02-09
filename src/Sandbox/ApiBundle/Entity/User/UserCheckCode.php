<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * User registration.
 *
 * @ORM\Table(name="user_check_codes")
 * @ORM\Entity
 */
class UserCheckCode
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
     * @ORM\Column(name="phoneCode", type="string", length=64, nullable=true)
     */
    private $phoneCode;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64, nullable=true, options={"default" = ""})
     */
    private $phone = '';

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=128, nullable=true, options={"default" = ""})
     */
    private $email = '';

    /**
     * Type Of code.
     * 0. Check code for admin login.
     * 1. Check code for user payment check api.
     * 2. Check code for client admin register.
     * 3. Check code for client admin forget password.
     *
     * @var int
     *
     * @ORM\Column(name="type", type="smallint", nullable=false, options={"default" = 0})
     */
    private $type = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=6, nullable=false)
     */
    private $code;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    private $userId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
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
     * @return string
     */
    public function getPhoneCode()
    {
        return $this->phoneCode;
    }

    /**
     * @param string $phoneCode
     *
     * @return UserCheckCode
     */
    public function setPhoneCode($phoneCode)
    {
        $this->phoneCode = $phoneCode;

        return $this;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return UserCheckCode
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return UserCheckCode
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get code.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set code.
     *
     * @param int $type
     *
     * @return UserCheckCode
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return UserCheckCode
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
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
     * @return UserCheckCode
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }
}
