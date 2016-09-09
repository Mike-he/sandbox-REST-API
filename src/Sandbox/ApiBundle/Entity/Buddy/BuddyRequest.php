<?php

namespace Sandbox\ApiBundle\Entity\Buddy;

use Doctrine\ORM\Mapping as ORM;
use Sandbox\ApiBundle\Entity\User\User;

/**
 * BuddyRequest.
 *
 * @ORM\Table(
 *      name="buddy_request",
 *      indexes={
 *          @ORM\Index(name="fk_BuddyRequest_askUserId_idx", columns={"askUserId"}),
 *          @ORM\Index(name="fk_BuddyRequest_recvUserId_idx", columns={"recvUserId"})
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="Sandbox\ApiBundle\Repository\Buddy\BuddyRequestRepository"
 * )
 */
class BuddyRequest
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';

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
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumn(name="askUserId", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $askUser;

    /**
     * @var int
     *
     * @ORM\Column(name="recvUserId", type="integer", nullable=false)
     */
    private $recvUserId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumn(name="recvUserId", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $recvUser;

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
    private $status = self::STATUS_PENDING;

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
     * Set askUser.
     *
     * @param User $askUser
     *
     * @return BuddyRequest
     */
    public function setAskUser($askUser)
    {
        $this->askUser = $askUser;
    }

    /**
     * Get askUser.
     *
     * @return User
     */
    public function getAskUser()
    {
        return $this->askUser;
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
     * Set recvUser.
     *
     * @param User $recvUser
     *
     * @return BuddyRequest
     */
    public function setRecvUser($recvUser)
    {
        $this->recvUser = $recvUser;
    }

    /**
     * Get recvUser.
     *
     * @return User
     */
    public function getRecvUser()
    {
        return $this->recvUser;
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
