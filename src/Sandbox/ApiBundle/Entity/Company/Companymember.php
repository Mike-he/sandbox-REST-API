<?php

namespace Sandbox\ApiBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;

/**
 * Company Member
 *
 * @ORM\Table(name="jtCompanyMember")
 * @ORM\MappedSuperclass
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\Company\CompanymemberRepository"
 * )
 */
class Companymember
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
     * @ORM\Column(name="userId", type="string", length=64, nullable=false)
     */
    private $userid;

    /**
     * @var int
     *
     * @ORM\Column(name="companyId", type="integer", nullable=false)
     */
    private $companyid;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isDelete", type="boolean",  nullable=false)
     */
    private $isdelete;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userid
     *
     * @param  string        $userid
     * @return CompanyMember
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Get userid
     *
     * @return string
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Set companyid
     *
     * @param  int           $companyid
     * @return CompanyMember
     */
    public function setCompanyid($companyid)
    {
        $this->companyid = $companyid;

        return $this;
    }

    /**
     * Get companyid
     *
     * @return int
     */
    public function getCompanyid()
    {
        return $this->companyid;
    }

    /**
     * Set isdelete
     *
     * @param  boolean       $isdelete
     * @return CompanyMember
     */
    public function setIsdelete($isdelete)
    {
        $this->isdelete = $isdelete;

        return $this;
    }

    /**
     * Get isdelete
     *
     * @return boolean
     */
    public function getIsdelete()
    {
        return $this->isdelete;
    }
}
