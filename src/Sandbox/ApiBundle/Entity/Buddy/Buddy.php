<?php

namespace Sandbox\ApiBundle\Entity\Buddy;

use Doctrine\ORM\Mapping as ORM;

/**
 * Buddy.
 *
 * @ORM\Table(
 *      name="Buddy",
 *      indexes={
 *          @ORM\Index(name="fk_Buddy_userId_idx", columns={"userId"}),
 *          @ORM\Index(name="fk_Buddy_buddyId_idx", columns={"buddyId"})
 *      }
 * )
 * @ORM\Entity
 */
class Buddy
{
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
     * @ORM\Column(name="userId", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="buddyId", type="integer", nullable=false)
     */
    private $buddyId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
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
     * @return Buddy
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
     * Set buddyId.
     *
     * @param int $buddyId
     *
     * @return Buddy
     */
    public function setBuddyId($buddyId)
    {
        $this->buddyId = $buddyId;

        return $this;
    }

    /**
     * Get buddyId.
     *
     * @return int
     */
    public function getBuddyId()
    {
        return $this->buddyId;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Buddy
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
