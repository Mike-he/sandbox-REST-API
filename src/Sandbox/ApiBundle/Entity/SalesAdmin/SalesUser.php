<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;

/**
 * SalesUser.
 *
 * @ORM\Table(name="sales_user")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\SalesAdmin\SalesUserRepository")
 */
class SalesUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer")
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="companyId", type="integer")
     */
    private $companyId;

    /**
     * @var int
     *
     * @ORM\Column(name="shopId", type="integer", nullable=true)
     */
    private $shopId;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer")
     */
    private $buildingId;

    /**
     * @var bool
     *
     * @ORM\Column(name="isOrdered", type="boolean")
     */
    private $isOrdered = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="isShopOrdered", type="boolean")
     */
    private $isShopOrdered = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="isAuthorized", type="boolean")
     */
    private $isAuthorized = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return SalesUser
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set companyId.
     *
     * @param int $companyId
     *
     * @return SalesUser
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
     * Set shopId.
     *
     * @param int $shopId
     *
     * @return SalesUser
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * Get shopId.
     *
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * Set buildingId.
     *
     * @param int $buildingId
     *
     * @return SalesUser
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;

        return $this;
    }

    /**
     * Get buildingId.
     *
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @return bool
     */
    public function isOrdered()
    {
        return $this->isOrdered;
    }

    /**
     * @param bool $isOrdered
     */
    public function setIsOrdered($isOrdered)
    {
        $this->isOrdered = $isOrdered;
    }

    /**
     * @return bool
     */
    public function isShopOrdered()
    {
        return $this->isShopOrdered;
    }

    /**
     * @param bool $isShopOrdered
     */
    public function setIsShopOrdered($isShopOrdered)
    {
        $this->isShopOrdered = $isShopOrdered;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->isAuthorized;
    }

    /**
     * @param bool $isAuthorized
     */
    public function setIsAuthorized($isAuthorized)
    {
        $this->isAuthorized = $isAuthorized;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return SalesUser
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
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param \DateTime $modificationDate
     *
     * @return SalesUser
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * SalesUser constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
        $this->modificationDate = new \DateTime('now');
    }
}
