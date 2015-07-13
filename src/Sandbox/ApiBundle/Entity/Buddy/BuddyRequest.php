<?php

namespace Sandbox\ApiBundle\Entity\Buddy;

use Doctrine\ORM\Mapping as ORM;

/**
 * BuddyRequest.
 *
 * @ORM\Table(
 *      name="BuddyRequest",
 *      indexes={
 *          @ORM\Index(name="fk_BuddyRequest_askUserId_idx", columns={"askUserId"}),
 *          @ORM\Index(name="fk_BuddyRequest_recvUserId_idx", columns={"recvUserId"})
 *      }
 * )
 * @ORM\Entity
 */
class BuddyRequest
{
    const BUDDY_REQUEST_STATUS_PENDING = 'pending';
    const BUDDY_REQUEST_STATUS_ACCEPTED = 'accepted';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="askUserId", type="integer", nullable=false)
     */
    private $askUserId;

    /**
     * @var int
     *
     * @ORM\Column(name="recvUserId", type="integer", nullable=false)
     */
    private $recvUserId;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=128, nullable=true)
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    private $status = self::BUDDY_REQUEST_STATUS_PENDING;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
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
     * Set askUserId.
     *
     * @param int $askUserId
     *
     * @return BuddyRequest
     */
    public function setAskUserId($askUserId)
    {
        $this->askUserId = $askUserId;

        return $this;
    }

    /**
     * Get askUserId.
     *
     * @return int
     */
    public function getAskUserId()
    {
        return $this->askUserId;
    }

    /**
     * Set recvUserId.
     *
     * @param int $recvUserId
     *
     * @return BuddyRequest
     */
    public function setRecvUserId($recvUserId)
    {
        $this->recvUserId = $recvUserId;

        return $this;
    }

    /**
     * Get recvUserId.
     *
     * @return int
     */
    public function getRecvUserId()
    {
        return $this->recvUserId;
    }

    /**
     * Set message.
     *
     * @param string $message
     *
     * @return BuddyRequest
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return BuddyRequest
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
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return BuddyRequest
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
     * @return BuddyRequest
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

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }
}