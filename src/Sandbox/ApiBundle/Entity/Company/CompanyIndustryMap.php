<?php

namespace Sandbox\ApiBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * CompanyIndustryMap.
 *
 * @ORM\Table(name="CompanyIndustryMap", uniqueConstraints={@ORM\UniqueConstraint(name="companyId_industryId_UNIQUE", columns={"companyId", "industryId"})}, indexes={@ORM\Index(name="fk_CompanyIndustryMap_companyId_idx", columns={"companyId"}), @ORM\Index(name="fk_CompanyIndustryMap_industryId_idx", columns={"industryId"})})
 * @ORM\Entity
 */
class CompanyIndustryMap
{
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
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main"})
     */
    private $id;

    /**
     * @var \Sandbox\ApiBundle\Entity\Company\CompanyIndustry
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Company\CompanyIndustry")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="industryId", referencedColumnName="id")
     * })
     * @Serializer\Groups({"main", "info"})
     */
    private $industry;

    /**
     * @var \Sandbox\ApiBundle\Entity\Company\CompanyIndustry
     *
     *
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="industryId", referencedColumnName="id")
     * })
     * @Serializer\Groups({"main", "info"})
     */
    private $industryId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Company\Company
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Company\Company")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="companyId", referencedColumnName="id")
     * })
     * @Serializer\Groups({"main", "info"})
     */
    private $companyId;

    /**
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="industries")
     * @ORM\JoinColumn(name="companyId", referencedColumnName="id")
     * @Serializer\Groups({"main"})
     */
    private $company;

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return CompanyIndustryMap
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
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
     * @return CompanyIndustryMap
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set industryId.
     *
     * @param \Sandbox\ApiBundle\Entity\Company\CompanyIndustry $industryId
     *
     * @return CompanyIndustryMap
     */
    public function setIndustryId(\Sandbox\ApiBundle\Entity\Company\CompanyIndustry $industryId = null)
    {
        $this->industryId = $industryId;

        return $this;
    }

    /**
     * Get industryId.
     *
     * @return \Sandbox\ApiBundle\Entity\Company\CompanyIndustry
     */
    public function getIndustryId()
    {
        return $this->industryId;
    }

    /**
     * Set companyId.
     *
     * @param \Sandbox\ApiBundle\Entity\Company\Company $companyId
     *
     * @return CompanyIndustryMap
     */
    public function setCompanyId(\Sandbox\ApiBundle\Entity\Company\Company $companyId = null)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get companyId.
     *
     * @return \Sandbox\ApiBundle\Entity\Company\Company
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function getIndustry()
    {
        return $this->industry;
    }
}
