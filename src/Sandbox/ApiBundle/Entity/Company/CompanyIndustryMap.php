<?php

namespace Sandbox\ApiBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * CompanyIndustryMap.
 *
 * @ORM\Table(name="CompanyIndustryMap", uniqueConstraints={@ORM\UniqueConstraint(name="companyId_industryId_UNIQUE", columns={"companyId", "industryId"})}, indexes={@ORM\Index(name="fk_CompanyIndustryMap_companyId_idx", columns={"companyId"}), @ORM\Index(name="fk_CompanyIndustryMap_industryId_idx", columns={"industryId"})})
 * @ORM\Entity(
 *      repositoryClass="Sandbox\ApiBundle\Repository\Company\CompanyIndustryMapRepository"
 * )
 */
class CompanyIndustryMap
{
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
     * @var Company
     *
     * @ORM\ManyToOne(targetEntity="Company")
     * @ORM\JoinColumn(name="companyId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main"})
     */
    private $company;

    /**
     * @var int
     *
     * @ORM\Column(name="companyId", type="integer",  nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $companyId;

    /**
     * @var CompanyIndustry
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Company\CompanyIndustry")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="industryId", referencedColumnName="id")
     * })
     * @Serializer\Groups({"main", "company_info", "company_industry"})
     */
    private $industry;

    /**
     * @var int
     *
     * @ORM\Column(name="industryId", type="integer")
     */
    private $industryId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
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
     * get company.
     *
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param $company
     *
     * @return $this
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }
    /**
     * Get companyId.
     *
     * @return Company
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * Set companyId.
     *
     * @param int $companyId
     *
     * @return CompanyIndustryMap
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get industryId.
     *
     * @return CompanyIndustry
     */
    public function getIndustryId()
    {
        return $this->industryId;
    }

    /**
     * Set industryId.
     *
     * @param int $industryId
     *
     * @return CompanyIndustryMap
     */
    public function setIndustryId($industryId)
    {
        $this->industryId = $industryId;

        return $this;
    }

    /**
     * get industry.
     *
     * @return CompanyIndustry
     */
    public function getIndustry()
    {
        return $this->industry;
    }

    /**
     * set industry.
     *
     * @param $industry
     *
     * @return $this
     */
    public function setIndustry($industry)
    {
        $this->industry = $industry;

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
}
