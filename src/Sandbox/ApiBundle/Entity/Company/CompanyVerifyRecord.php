<?php

namespace Sandbox\ApiBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * CompanyVerifyRecord.
 *
 * @ORM\Table(name="CompanyVerifyRecord")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Company\CompanyVerifyRecordRepository")
 */
class CompanyVerifyRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_UPDATED = 'updated';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ACCEPTED = 'accepted';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "company_info", "company_limit"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="companyId", type="integer", nullable=false)
     * @Serializer\Groups({"main", "company_info"})
     */
    private $companyId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Company\Company
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Company\Company")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="companyId", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Serializer\Groups({"main", "company_info"})
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=true)
     * @Serializer\Groups({"main", "company_info", "company_limit"})
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var string
     *
     * @ORM\Column(name="companyInfo", type="text", nullable=false)
     * @Serializer\Groups({"main", "company_info"})
     */
    private $companyInfo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main", "company_info", "company_limit"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main", "company_info", "company_limit"})
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
     * Set companyId.
     *
     * @param int $companyId
     *
     * @return CompanyVerifyRecord
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get companyId.
     *
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * Set company.
     *
     * @param Company $company
     *
     * @return CompanyVerifyRecord
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company.
     *
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return CompanyVerifyRecord
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set companyInfo.
     *
     * @param string $companyInfo
     *
     * @return CompanyVerifyRecord
     */
    public function setCompanyInfo($companyInfo)
    {
        $this->companyInfo = $companyInfo;

        return $this;
    }

    /**
     * Get companyInfo.
     *
     * @return string
     */
    public function getCompanyInfo()
    {
        return $this->companyInfo;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return CompanyVerifyRecord
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
     * @return CompanyVerifyRecord
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
}
