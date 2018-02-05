<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AdminRemark.
 *
 * @ORM\Table(name="admin_remarks")
 * @ORM\Entity
 */
class AdminRemark
{
    const OBJECT_LEASE_BILL = 'lease_bill';
    const OBJECT_PRODUCT_ORDER = 'product_order';
    const OBJECT_TOP_UP_ORDER = 'top_up_order';
    const OBJECT_LEASE_CLUE = 'lease_clue';
    const OBJECT_LEASE_OFFER = 'lease_offer';
    const OBJECT_LEASE = 'lease';
    const OBJECT_EXPERT = 'expert';

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
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=16)
     */
    private $username;

    /**
     * @var int
     *
     * @ORM\Column(name="companyId", type="integer", nullable=true)
     */
    private $companyId;

    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=16)
     */
    private $platform;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="text")
     */
    private $remarks;

    /**
     * @var string
     *
     * @ORM\Column(name="object", type="string", length=16)
     */
    private $object;

    /**
     * @var int
     *
     * @ORM\Column(name="objectId", type="string", length=64)
     */
    private $objectId;

    /**
     * @var int
     *
     * @ORM\Column(name="is_auto", type="boolean")
     */
    private $isAuto = false;

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
     * @return AdminRemark
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
     * Set username.
     *
     * @param string $username
     *
     * @return AdminRemark
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set companyId.
     *
     * @param int $companyId
     *
     * @return AdminRemark
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
     * Set platform.
     *
     * @param string $platform
     *
     * @return AdminRemark
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get platform.
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return AdminRemark
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
     * Set remarks.
     *
     * @param string $remarks
     *
     * @return AdminRemark
     */
    public function setRemarks($remarks)
    {
        $this->remarks = $remarks;

        return $this;
    }

    /**
     * Get remarks.
     *
     * @return string
     */
    public function getRemarks()
    {
        return $this->remarks;
    }

    /**
     * Set object.
     *
     * @param string $object
     *
     * @return AdminRemark
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object.
     *
     * @return string
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set objectId.
     *
     * @param int $objectId
     *
     * @return AdminRemark
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId.
     *
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @return int
     */
    public function getisAuto()
    {
        return $this->isAuto;
    }

    /**
     * @param int $isAuto
     */
    public function setIsAuto($isAuto)
    {
        $this->isAuto = $isAuto;
    }
}
