<?php

namespace Sandbox\ApiBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;

/**
 * Company.
 *
 * @ORM\Table(name="jtCompany")
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\Company\CompanyRepository"
 * )
 */
class Company
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer",  nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=1024, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=256, nullable=true)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=32, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", length=32, nullable=true)
     */
    private $fax;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="creatorId", type="string", length=255, nullable=false)
     */
    private $creatorid;

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
     * Get id.
     *
     * @return string
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
     * @return Company
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
     * Set address.
     *
     * @param string $address
     *
     * @return Company
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set website.
     *
     * @param string $website
     *
     * @return Company
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website.
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return Company
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
     * Set fax.
     *
     * @param string $fax
     *
     * @return Company
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax.
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return VCard
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set owner.
     *
     * @param string $creatorid
     *
     * @return Company
     */
    public function setCreatorid($creatorid)
    {
        $this->creatorid = $creatorid;

        return $this;
    }

    /**
     * Get creatorId.
     *
     * @return string
     */
    public function getCreatorid()
    {
        return $this->creatorid;
    }

    /**
     * Set creationdate.
     *
     * @param string $creationdate
     *
     * @return Company
     */
    public function setCreationdate($creationdate)
    {
        $this->creationdate = $creationdate.'000';

        return $this;
    }

    /**
     * Get creationdate.
     *
     * @return string
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * Set modificationdate.
     *
     * @param string $modificationdate
     *
     * @return Company
     */
    public function setModificationdate($modificationdate)
    {
        $this->modificationdate = $modificationdate.'000';

        return $this;
    }

    /**
     * Get modificationdate.
     *
     * @return string
     */
    public function getModificationdate()
    {
        return $this->modificationdate;
    }
}
