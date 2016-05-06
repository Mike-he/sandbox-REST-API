<?php

namespace Sandbox\ApiBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * EventComment.
 *
 * @ORM\Table(
 *     name="EventComment",
 *     indexes={
 *      @ORM\Index(name="fk_eventComment_eventId_idx", columns={"eventId"})
 *  }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\Event\EventCommentRepository"
 * )
 */
class EventComment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "client_event", "admin_event"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="eventId", type="integer")
     *
     * @Serializer\Groups({"main", "client_event", "admin_event"})
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
     * @Serializer\Groups({"client_event", "admin_event"})
     */
    private $event;

    /**
     * @var int
     *
     * @ORM\Column(name="authorId", type="integer")
     *
     * @Serializer\Groups({"main"})
     */
    private $authorId;

    /**
     * @var \Sandbox\ApiBundle\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="authorId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     *
     * @Serializer\Groups({"main", "client_event", "admin_event"})
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="payload", type="text")
     *
     * @Serializer\Groups({"main", "client_event", "admin_event"})
     */
    private $payload;

    /**
     * @var int
     *
     * @ORM\Column(name="replyToUserId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $replyToUserId;

    /**
     * @var \Sandbox\ApiBundle\Entity\User\User
     *
     * @Serializer\Groups({"main"})
     */
    private $replyToUser;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     *
     * @Serializer\Groups({"main"})
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
     * Set eventId.
     *
     * @param int $eventId
     *
     * @return EventComment
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
     * Set authorId.
     *
     * @param int $authorId
     *
     * @return EventComment
     */
    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;

        return $this;
    }

    /**
     * Get authorId.
     *
     * @return int
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * Set payload.
     *
     * @param string $payload
     *
     * @return EventComment
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Get payload.
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set replyToUserId.
     *
     * @param int $replyToUserId
     *
     * @return EventComment
     */
    public function setReplyToUserId($replyToUserId)
    {
        $this->replyToUserId = $replyToUserId;

        return $this;
    }

    /**
     * Get replyToUserId.
     *
     * @return int
     */
    public function getReplyToUserId()
    {
        return $this->replyToUserId;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return EventComment
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
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return \Sandbox\ApiBundle\Entity\User\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param \Sandbox\ApiBundle\Entity\User\User $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return \Sandbox\ApiBundle\Entity\User\User
     */
    public function getReplyToUser()
    {
        return $this->replyToUser;
    }

    /**
     * @param \Sandbox\ApiBundle\Entity\User\User $replyToUser
     */
    public function setReplyToUser($replyToUser)
    {
        $this->replyToUser = $replyToUser;
    }

    /**
     * EventComment constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
    }
}
