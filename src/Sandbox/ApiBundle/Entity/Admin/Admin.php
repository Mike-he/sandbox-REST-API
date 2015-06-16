<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;

/**
 * Admin
 *
 * @ORM\Table(
 *      name="Admin",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="username_UNIQUE", columns={"username"})
 *      }
 * )
 * @ORM\Entity
 */
class Admin
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=64, nullable=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=256, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="typeKey", type="string", nullable=false)
     */
    private $typeKey;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     */
    private $modificationDate;

    /**
     * @ORM\OneToOne(targetEntity="AdminType", cascade={"persist"}))
     * @ORM\JoinColumn(name="typeId", referencedColumnName="id")
     **/
    private $type;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param  string $username
     * @return Admin
     */
    public function setUsername($username)
    {
        $this->username = $username;
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
     * Set password
     *
     * @param  string $password
     * @return Admin
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return Admin
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get typeKey
     *
     * @return int
     */
    public function getTypeKey()
    {
        return $this->typeKey;
    }

    /**
     * Set typeKey
     *
     * @param  int   $typeKey
     * @return Admin
     */
    public function setTypeKey($typeKey)
    {
        $this->typeKey = $typeKey;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set creationDate
     *
     * @param  \DateTime $creationDate
     * @return Admin
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * Get modificationDate
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set modificationDate
     *
     * @param  \DateTime $modificationDate
     * @return Admin
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * Get type
     *
     * @return AdminType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param  AdminType $type
     * @return Admin
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
