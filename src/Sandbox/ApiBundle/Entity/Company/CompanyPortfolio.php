<?php

namespace Sandbox\ApiBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * CompanyPortfolio.
 *
 * @ORM\Table(name="company_portfolio", indexes={@ORM\Index(name="fk_CompanyPortfolio_companyId_idx", columns={"companyId"})})
 * @ORM\Entity(
 *      repositoryClass="Sandbox\ApiBundle\Repository\Company\CompanyPortfolioRepository"
 * )
 */
class CompanyPortfolio
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "company_info", "company_portfolio", "verify"})
     */
    private $id;

    /**
     * @var \Sandbox\ApiBundle\Entity\Company\Company
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Company\Company")
     * @ORM\JoinColumn(name="companyId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"main"})
     */
    private $company;

    /**
     * @var int
     *
     * @ORM\Column(name="companyId", type="integer", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $companyId;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     *
     * @Serializer\Groups({"main", "company_info", "company_portfolio", "verify"})
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="attachmentType", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "company_info", "company_portfolio", "verify"})
     */
    private $attachmentType;

    /**
     * @var string
     *
     * @ORM\Column(name="fileName", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "company_info", "company_portfolio", "verify"})
     */
    private $fileName;

    /**
     * @var string
     *
     * @ORM\Column(name="preview", type="text", nullable=true)
     * @Serializer\Groups({"main", "company_info", "company_portfolio", "verify"})
     */
    private $preview;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer", nullable=false)
     * @Serializer\Groups({"main", "company_info", "company_portfolio", "verify"})
     */
    private $size;

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
     * Get companyId.
     *
     * @return \Sandbox\ApiBundle\Entity\Company\Company
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * Set companyId.
     *
     * @param \Sandbox\ApiBundle\Entity\Company\Company $companyId
     *
     * @return CompanyPortfolio
     */
    public function setCompanyId(\Sandbox\ApiBundle\Entity\Company\Company $companyId = null)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return CompanyPortfolio
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get attachmentType.
     *
     * @return string
     */
    public function getAttachmentType()
    {
        return $this->attachmentType;
    }

    /**
     * Set attachmentType.
     *
     * @param string $attachmentType
     *
     * @return CompanyPortfolio
     */
    public function setAttachmentType($attachmentType)
    {
        $this->attachmentType = $attachmentType;

        return $this;
    }

    /**
     * Get fileName.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set fileName.
     *
     * @param string $fileName
     *
     * @return CompanyPortfolio
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get preview.
     *
     * @return string
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * Set preview.
     *
     * @param string $preview
     *
     * @return CompanyPortfolio
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;

        return $this;
    }

    /**
     * Get size.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set size.
     *
     * @param int $size
     *
     * @return CompanyPortfolio
     */
    public function setSize($size)
    {
        $this->size = $size;

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
     * @return CompanyPortfolio
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
     * @return CompanyPortfolio
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }
}
