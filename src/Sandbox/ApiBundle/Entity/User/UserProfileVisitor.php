<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * User Profile Visitor.
 *
 * @ORM\Table(name="UserProfileVisitor")
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\User\UserProfileVisitorRepository"
 * )
 */
class UserProfileVisitor
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer",  nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer",  nullable=false)
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="visitorId", type="integer",  nullable=false)
     */
    private $visitorId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return UserProfileVisitor
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get visitorId.
     *
     * @return int
     */
    public function getVisitorId()
    {
        return $this->visitorId;
    }

    /**
     * Set visitorId.
     *
     * @param int $visitorId
     *
     * @return UserProfileVisitor
     */
    public function setVisitorId($visitorId)
    {
        $this->visitorId = $visitorId;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return UserProfileVisitor
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
        $this->setCreationDate(new \DateTime('now'));
    }
}
