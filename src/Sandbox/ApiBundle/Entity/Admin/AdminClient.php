<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminClient
 *
 * @ORM\Table(name="AdminClient")
 * @ORM\Entity
 */
class AdminClient
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
     * @ORM\Column(name="name", type="string", length=128, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="os", type="string", length=256, nullable=true)
     */
    private $os;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", length=16, nullable=true)
     */
    private $version;

    /**
     * @var string
     *
     * @ORM\Column(name="ipAddress", type="string", length=64, nullable=true)
     */
    private $ipAddress;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param  string      $name
     * @return AdminClient
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Set os
     *
     * @param  string      $os
     * @return AdminClient
     */
    public function setOs($os)
    {
        $this->os = $os;

        return $this;
    }

    /**
     * Get os
     *
     * @return string
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * Set version
     *
     * @param  string      $version
     * @return AdminClient
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set ipAddress
     *
     * @param  string      $ipAddress
     * @return AdminClient
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
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
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
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
     * Get modificationDate
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }
}
