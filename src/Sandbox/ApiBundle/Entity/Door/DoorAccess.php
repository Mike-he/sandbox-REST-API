<?php

namespace Sandbox\ApiBundle\Entity\Door;

use Doctrine\ORM\Mapping as ORM;

/**
 * DoorAccess.
 *
 * @ORM\Table(name="DoorAccess")
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
     * @var string
     *
     * @ORM\Column(name="doorId", type="string", length=64)
     */
    private $doorId;

    /**
     * @var int
     *
     * @ORM\Column(name="timeId", type="integer")
     */
    private $timeId;

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
     * Set doorId.
     *
     * @param string $doorId
     *
     * @return DoorAccess
     */
    public function setDoorId($doorId)
    {
        $this->doorId = $doorId;

        return $this;
    }

    /**
     * Get doorId.
     *
     * @return string
     */
    public function getDoorId()
    {
        return $this->doorId;
    }

    /**
     * Set timeId.
     *
     * @param int $timeId
     *
     * @return DoorAccess
     */
    public function setTimeId($timeId)
    {
        $this->timeId = $timeId;

        return $this;
    }

    /**
     * Get timeId.
     *
     * @return int
     */
    public function getTimeId()
    {
        return $this->timeId;
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

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
    }
}
