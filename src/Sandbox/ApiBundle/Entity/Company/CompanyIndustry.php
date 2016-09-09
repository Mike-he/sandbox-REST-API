<?php

namespace Sandbox\ApiBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * CompanyIndustry.
 *
 * @ORM\Table(name="company_industry")
 * @ORM\Entity
 */
class CompanyIndustry
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "company_info", "company_industry", "verify"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "company_info", "company_industry", "verify"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $key;

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
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return CompanyIndustry
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
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
     * @return CompanyIndustry
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
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

    /**
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return CompanyIndustry
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }
}
