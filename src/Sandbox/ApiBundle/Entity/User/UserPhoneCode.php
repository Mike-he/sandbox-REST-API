<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserPhoneCode.
 *
 * @ORM\Table(name="UserPhoneCode")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\User\UserPhoneCodeRepository")
 */
class UserPhoneCode
{
    const DEFAULT_PHONE_CODE = '+86';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="cnName", type="string", length=255)
     */
    private $cnName;

    /**
     * @var string
     *
     * @ORM\Column(name="enName", type="string", length=255)
     */
    private $enName;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=122)
     */
    private $code;

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
     * Set cnName.
     *
     * @param string $cnName
     *
     * @return UserPhoneCode
     */
    public function setCnName($cnName)
    {
        $this->cnName = $cnName;

        return $this;
    }

    /**
     * Get cnName.
     *
     * @return string
     */
    public function getCnName()
    {
        return $this->cnName;
    }

    /**
     * Set enName.
     *
     * @param string $enName
     *
     * @return UserPhoneCode
     */
    public function setEnName($enName)
    {
        $this->enName = $enName;

        return $this;
    }

    /**
     * Get enName.
     *
     * @return string
     */
    public function getEnName()
    {
        return $this->enName;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return UserPhoneCode
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
}
