<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * User registration.
 *
 * @ORM\Table(
 *      name="user_check_codes",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="phone_and_phone_code_UNIQUE", columns={"phone", "phoneCode"}),
 *          @ORM\UniqueConstraint(name="email_UNIQUE", columns={"email"})
 *      }
 * )
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
     * 0. CHeck code for admin login.
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
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

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
     */
    public function setPhoneCode($phoneCode)
    {
        $this->phoneCode = $phoneCode;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return UserRegistration
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
     * @return UserRegistration
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
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return UserRegistration
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
     * @return User
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function __construct()
    {
        $this->creationDate = new \DateTime(
            'now'
        );
    }
}
