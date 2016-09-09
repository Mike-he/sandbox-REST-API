<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * SalesAdminClient.
 *
 * @ORM\Table(name="sales_admin_client")
 * @ORM\Entity
 */
class SalesAdminClient
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "login"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=true)
     * @Serializer\Groups({"main", "login"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="os", type="string", length=256, nullable=true)
     * @Serializer\Groups({"main", "login"})
     */
    private $os;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", length=16, nullable=true)
     * @Serializer\Groups({"main", "login"})
     */
    private $version;

    /**
     * @var string
     *
     * @ORM\Column(name="ipAddress", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main", "login"})
     */
    private $ipAddress;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $modificationDate;

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
     * Set name.
     *
     * @param string $name
     *
     * @return SalesAdminClient
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Set os.
     *
     * @param string $os
     *
     * @return SalesAdminClient
     */
    public function setOs($os)
    {
        $this->os = $os;

        return $this;
    }

    /**
     * Get os.
     *
     * @return string
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * Set version.
     *
     * @param string $version
     *
     * @return SalesAdminClient
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set ipAddress.
     *
     * @param string $ipAddress
     *
     * @return SalesAdminClient
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress.
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return SalesAdminClient
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
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
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return SalesAdminClient
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
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
}
