<?php

namespace Sandbox\ApiBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * EventForm.
 *
 * @ORM\Table(name = "EventForm")
 * @ORM\Entity
 */
class EventForm
{
    const TYPE_TEXT = 'text';
    const TYPE_EMAIL = 'email';
    const TYPE_PHONE = 'phone';
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event"
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event"
     * })
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event"
     * })
     */
    private $type;

    /**
     * @var EventFormOption
     *
     * @ORM\OneToMany(targetEntity="Sandbox\ApiBundle\Entity\Event\EventFormOption",
     *      mappedBy="form",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="formId")
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event"
     * })
     */
    private $option;

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
     * @return EventForm
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
     * Set title.
     *
     * @param string $title
     *
     * @return EventForm
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return EventForm
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set option.
     *
     * @param EventFormOption $option
     *
     * @return EventForm
     */
    public function setForm($option)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * Get option.
     *
     * @return EventFormOption
     */
    public function getOption()
    {
        return $this->option;
    }
}
