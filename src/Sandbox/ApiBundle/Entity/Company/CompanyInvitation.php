<?php

namespace Sandbox\ApiBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sandbox\ApiBundle\Entity\User\User;

/**
 * CompanyInvitation.
 *
 * @ORM\Table(
 *      name="company_invitation",
 *      indexes={
 *          @ORM\Index(name="fk_CompanyInvitation_companyId_idx", columns={"companyId"}),
 *          @ORM\Index(name="fk_CompanyInvitation_askUserId_idx", columns={"askUserId"}),
 *          @ORM\Index(name="fk_CompanyInvitation_recvUserId_idx", columns={"recvUserId"})
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="Sandbox\ApiBundle\Repository\Company\CompanyInvitationRepository"
 * )
 */
class CompanyInvitation
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Groups({"main", "company_invitation"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="companyId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $companyId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Company\Company")
     * @ORM\JoinColumn(name="companyId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"main", "company_invitation"})
     **/
    private $company;

    /**
     * @var int
     *
     * @ORM\Column(name="askUserId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $askUserId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumn(name="askUserId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"main"})
     **/
    private $askUser;

    /**
     * @var int
     *
     * @ORM\Column(name="recvUserId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $recvUserId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumn(name="recvUserId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"main"})
     **/
    private $recvUser;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=false)
     *
     * @Serializer\Groups({"main", "company_invitation"})
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     *
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
     * Set companyId.
     *
     * @param int $companyId
     *
     * @return CompanyInvitation
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
     * @return CompanyInvitation
     */
    public function setCompany($company)
    {
        $this->company = $company;
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
     * Set askUserId.
     *
     * @param int $askUserId
     *
     * @return CompanyInvitation
     */
    public function setAskUserId($askUserId)
    {
        $this->askUserId = $askUserId;

        return $this;
    }

    /**
     * Get askUserId.
     *
     * @return int
     */
    public function getAskUserId()
    {
        return $this->askUserId;
    }

    /**
     * Set askUser.
     *
     * @param User $askUser
     *
     * @return CompanyInvitation
     */
    public function setAskUser($askUser)
    {
        $this->askUser = $askUser;
    }

    /**
     * Get askUser.
     *
     * @return User
     */
    public function getAskUser()
    {
        return $this->askUser;
    }

    /**
     * Set recvUserId.
     *
     * @param int $recvUserId
     *
     * @return CompanyInvitation
     */
    public function setRecvUserId($recvUserId)
    {
        $this->recvUserId = $recvUserId;

        return $this;
    }

    /**
     * Get recvUserId.
     *
     * @return int
     */
    public function getRecvUserId()
    {
        return $this->recvUserId;
    }

    /**
     * Set recvUser.
     *
     * @param User $recvUser
     *
     * @return CompanyInvitation
     */
    public function setRecvUser($recvUser)
    {
        $this->recvUser = $recvUser;
    }

    /**
     * Get recvUser.
     *
     * @return User
     */
    public function getRecvUser()
    {
        return $this->recvUser;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return CompanyInvitation
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
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return CompanyInvitation
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
     * @return CompanyInvitation
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

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }
}
