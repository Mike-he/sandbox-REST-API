<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminPositionUserBinding.
 *
 * @ORM\Table(
 *     name="admin_position_user_binding",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="userId_positionId_UNIQUE", columns={"userId", "positionId"})
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_AdminPositionUserBinding_userId_idx", columns={"userId"}),
 *          @ORM\Index(name="fk_AdminPositionUserBinding_positionId_idx", columns={"positionId"})
 *      }
 * )
 * @ORM\Entity
 */
class AdminPositionUserBinding
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
     * @ORM\Column(name="userId", type="integer")
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="positionId", type="integer")
     */
    private $positionId;

    /**
     * @ORM\ManyToOne(targetEntity="AdminPosition")
     * @ORM\JoinColumn(name="positionId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $position;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
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
     * @return AdminPositionUserBinding
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
     * Set positionId.
     *
     * @param int $positionId
     *
     * @return AdminPositionUserBinding
     */
    public function setPositionId($positionId)
    {
        $this->positionId = $positionId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get positionId.
     *
     * @return int
     */
    public function getPositionId()
    {
        return $this->positionId;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return AdminPositionUserBinding
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
     * AdminPositionUserBinding constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
    }
}
