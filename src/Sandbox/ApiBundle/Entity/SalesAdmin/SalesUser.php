<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;

/**
 * SalesUser
 *
 * @ORM\Table(name="SalesUser")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\SalesAdmin\SalesUserRepository")
 */
class SalesUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="userId", type="integer")
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="companyId", type="integer")
     */
    private $companyId;

    /**
     * @var integer
     *
     * @ORM\Column(name="buildingId", type="integer")
     */
    private $buildingId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return SalesUser
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set companyId
     *
     * @param integer $companyId
     * @return SalesUser
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get companyId
     *
     * @return integer 
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * Set buildingId
     *
     * @param integer $buildingId
     * @return SalesUser
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;

        return $this;
    }

    /**
     * Get buildingId
     *
     * @return integer 
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return SalesUser
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime 
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * SalesUser constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
    }
}
