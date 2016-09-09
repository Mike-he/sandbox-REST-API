<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * User Hobby Map.
 *
 * @ORM\Table(
 *      name="user_hobby_map",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="userId_hobbyId_UNIQUE",
 *              columns={"userId", "hobbyId"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\User\UserHobbyMapRepository"
 * )
 */
class UserHobbyMap
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="hobbyId", type="integer",  nullable=false)
     */
    private $hobbyId;

    /**
     * @var UserHobby
     *
     * @ORM\ManyToOne(targetEntity="UserHobby")
     * @ORM\JoinColumn(name="hobbyId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main", "profile", "profile_stranger"})
     **/
    private $hobby;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
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
     * @return UserHobbyMap
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
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
     * @return UserHobbyMap
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get hobbyId.
     *
     * @return int
     */
    public function getHobbyId()
    {
        return $this->hobbyId;
    }

    /**
     * Set hobbyId.
     *
     * @param int $hobbyId
     *
     * @return UserHobbyMap
     */
    public function setHobbyId($hobbyId)
    {
        $this->hobbyId = $hobbyId;
    }

    /**
     * Get hobby.
     *
     * @return UserHobby
     */
    public function getHobby()
    {
        return $this->hobby;
    }

    /**
     * Set hobby.
     *
     * @param UserHobby $hobby
     *
     * @return UserHobbyMap
     */
    public function setHobby($hobby)
    {
        $this->hobby = $hobby;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return UserHobbyMap
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
}
