<?php

namespace Sandbox\ApiBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventTime.
 *
 * @ORM\Table(name = "EventTime")
 * @ORM\Entity
 */
class EventTime
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
     * @ORM\Column(name="dateId", type="integer", nullable=false)
     */
    private $dateId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Event\EventDate
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Event\EventDate")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dateId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startTime", type="datetime", nullable=false)
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endTime", type="datetime", nullable=false)
     */
    private $endTime;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

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
     * Set dateId.
     *
     * @param int $dateId
     *
     * @return EventTime
     */
    public function setDateId($dateId)
    {
        $this->dateId = $dateId;

        return $this;
    }

    /**
     * Get dateId.
     *
     * @return int
     */
    public function getDateId()
    {
        return $this->dateId;
    }

    /**
     * Set date.
     *
     * @param $date
     *
     * @return EventTime
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return EventTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set startTime.
     *
     * @param \DateTime $startTime
     *
     * @return EventTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime.
     *
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime.
     *
     * @param \DateTime $endTime
     *
     * @return EventTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime.
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return EventTime
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
