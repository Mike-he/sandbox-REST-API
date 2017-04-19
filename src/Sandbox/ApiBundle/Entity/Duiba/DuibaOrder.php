<?php

namespace Sandbox\ApiBundle\Entity\Door;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * DuibaOrder.
 *
 * @ORM\Table(name="duiba_order")
 * @ORM\Entity()
 */
class DuibaOrder
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
     * @ORM\Column(name="app_id", type="integer")
     */
    private $appId;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="credits", type="integer")
     */
    private $credits;

    /**
     * @var int
     *
     * @ORM\Column(name="actual_price", type="float")
     */
    private $actualPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="duiba_order_num", type="string", length=255)
     */
    private $duibaOrderNum;

    /**
     * @var int
     *
     * @ORM\Column(name="order_status", type="integer")
     */
    private $orderStatus;

    /**
     * @var int
     *
     * @ORM\Column(name="credits_status", type="integer")
     */
    private $creditsStatus;

    /**
     * @var bool
     *
     * @ORM\Column(name="type", type="string", length=40)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modificationDate", type="datetime")
     */
    private $modificationDate;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param int $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /**
     * @param int $credits
     */
    public function setCredits($credits)
    {
        $this->credits = $credits;
    }

    /**
     * @return int
     */
    public function getActualPrice()
    {
        return $this->actualPrice;
    }

    /**
     * @param int $actualPrice
     */
    public function setActualPrice($actualPrice)
    {
        $this->actualPrice = $actualPrice;
    }

    /**
     * @return string
     */
    public function getDuibaOrderNum()
    {
        return $this->duibaOrderNum;
    }

    /**
     * @param string $duibaOrderNum
     */
    public function setDuibaOrderNum($duibaOrderNum)
    {
        $this->duibaOrderNum = $duibaOrderNum;
    }

    /**
     * @return int
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * @param int $orderStatus
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    /**
     * @return int
     */
    public function getCreditsStatus()
    {
        return $this->creditsStatus;
    }

    /**
     * @param int $creditsStatus
     */
    public function setCreditsStatus($creditsStatus)
    {
        $this->creditsStatus = $creditsStatus;
    }

    /**
     * @return bool
     */
    public function isType()
    {
        return $this->type;
    }

    /**
     * @param bool $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
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
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }
}
