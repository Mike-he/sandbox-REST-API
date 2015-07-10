<?php

namespace Sandbox\ApiBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompanyMember.
 *
 * @ORM\Table(name="CompanyMember", uniqueConstraints={
 * @ORM\UniqueConstraint(name="userId_companyId_UNIQUE", columns={"userId", "companyId"})}, indexes={
 * @ORM\Index(name="fk_CompanyMember_companyId_idx", columns={"companyId"}),
 * @ORM\Index(name="fk_CompanyMember_userId_idx", columns={"userId"})})
 * @ORM\Entity
 */
class CompanyMember
{
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
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var \Sandbox\ApiBundle\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userId", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var \Sandbox\ApiBundle\Entity\Company\Company
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Company\Company")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="companyId", referencedColumnName="id")
     * })
     */
    private $companyId;

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return CompanyMember
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
     * @return CompanyMember
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
     * Set userId.
     *
     * @param \Sandbox\ApiBundle\Entity\User\User $userId
     *
     * @return CompanyMember
     */
    public function setUserId(\Sandbox\ApiBundle\Entity\User\User $userId = null)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return \Sandbox\ApiBundle\Entity\User\User
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set companyId.
     *
     * @param \Sandbox\ApiBundle\Entity\Company\Company $companyId
     *
     * @return CompanyMember
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
}
