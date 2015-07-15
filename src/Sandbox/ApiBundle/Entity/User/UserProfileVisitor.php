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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="profileVisitorUser"))
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
     **/
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="visitorId", type="integer",  nullable=false)
     */
    private $visitorId;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="profileVisitor"))
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
     **/
    private $visitor;

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

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return UserProfileVisitor
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * Set user.
     *
     * @param User $visitor
     *
     * @return UserProfileVisitor
     */
    public function setVisitor($visitor)
    {
        $this->user = $visitor;
    }

    public function __construct()
    {
        $this->setCreationDate(new \DateTime('now'));
    }
}
