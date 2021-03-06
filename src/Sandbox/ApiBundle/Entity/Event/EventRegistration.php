<?php

namespace Sandbox\ApiBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * EventRegistration.
 *
 * @ORM\Table(name="event_registration")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Event\EventRegistrationRepository")
 */
class EventRegistration
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    const ACTION_ACCEPT = 'registration_accept';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="eventId", type="integer", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $eventId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Event\Event
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Event\Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eventId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     * @Serializer\Groups({"main"})
     */
    private $event;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $userId;

    /**
     * @var \Sandbox\ApiBundle\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     * @Serializer\Groups({"main"})
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "client_event"
     * })
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var bool
     *
     * @ORM\Column(name="notInList", type="boolean", nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "client_event"
     * })
     */
    private $notInList = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
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
     * Set eventId.
     *
     * @param int $eventId
     *
     * @return EventRegistration
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return EventRegistration
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
     * Set user.
     *
     * @param \Sandbox\ApiBundle\Entity\User\User $user
     *
     * @return EventRegistration
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return EventRegistration
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return EventRegistration
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isNotInList()
    {
        return $this->notInList;
    }

    /**
     * @param bool $notInList
     */
    public function setNotInList($notInList)
    {
        $this->notInList = $notInList;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return EventRegistration
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
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return EventRegistration
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }
}
