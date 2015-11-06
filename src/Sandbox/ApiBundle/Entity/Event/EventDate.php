<?php

namespace Sandbox\ApiBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * EventDate.
 *
 * @ORM\Table(name = "EventDate")
 * @ORM\Entity
 */
class EventDate
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="eventId", type="integer", nullable=false)
     *
     * @Serializer\Groups({
     *      "main"
     * })
     */
    private $eventId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Event\Event
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Event\Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eventId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     *
     * @Serializer\Groups({
     *      "main"
     * })
     */
    private $event;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $date;

    /**
     * @var EventTime
     *
     * @ORM\OneToMany(targetEntity="Sandbox\ApiBundle\Entity\Event\EventTime",
     *      mappedBy="date",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="dateId")
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $times;

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
     * Set eventId.
     *
     * @param int $eventId
     *
     * @return EventDate
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId.
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set event.
     *
     * @param $event
     *
     * @return EventDate
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event.
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return EventDate
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set times.
     *
     * @param EventTime $times
     *
     * @return EventDate
     */
    public function setTimes($times)
    {
        $this->times = $times;

        return $this;
    }

    /**
     * Get times.
     *
     * @return EventTime
     */
    public function getTimes()
    {
        return $this->times;
    }
}
