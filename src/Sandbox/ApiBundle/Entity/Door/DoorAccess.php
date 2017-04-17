<?php

namespace Sandbox\ApiBundle\Entity\Door;

use Doctrine\ORM\Mapping as ORM;
use Sandbox\ApiBundle\Constants\DoorAccessConstants;

/**
 * DoorAccess.
 *
 * @ORM\Table(name="door_access")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Door\DoorAccessRepository")
 */
class DoorAccess
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
     * @ORM\Column(name="buildingId", type="integer")
     */
    private $buildingId;

    /**
     * @var int
     *
     * @ORM\Column(name="roomId", type="integer", nullable=true)
     */
    private $roomId;

    /**
     * @var int
     *
     * @ORM\Column(name="accessNo", type="string", length=30)
     */
    private $accessNo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime")
     */
    private $endDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="access", type="boolean", nullable=false)
     */
    private $access = false;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=64)
     */
    private $action = DoorAccessConstants::METHOD_ADD;

    /**
     * Set access.
     *
     * @param bool $banned
     *
     * @return DoorAccess
     */
    public function setAccess($access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * Is access.
     *
     * @return bool
     */
    public function isAccess()
    {
        return $this->access;
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
     * @param int $userId
     *
     * @return DoorAccess
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
     * Set buildingId.
     *
     * @param int $buildingId
     *
     * @return DoorAccess
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
     * Set roomId.
     *
     * @param int $roomId
     *
     * @return DoorAccess
     */
    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Get roomId.
     *
     * @return int
     */
    public function getRoomId()
    {
        return $this->roomId;
    }

    /**
     * Set $accessNo.
     *
     * @param string $accessNo
     *
     * @return DoorAccess
     */
    public function setAccessNo($accessNo)
    {
        $this->accessNo = $accessNo;

        return $this;
    }

    /**
     * Get $accessNo.
     *
     * @return string
     */
    public function getAccessNo()
    {
        return $this->accessNo;
    }

    /**
     * Set startDate.
     *
     * @param \DateTime $startDate
     *
     * @return DoorAccess
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate.
     *
     * @param \DateTime $endDate
     *
     * @return DoorAccess
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate.
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return DoorAccess
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
     * Set action.
     *
     * @param string $action
     *
     * @return DoorAccess
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
    }
}
