<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * User Hobby Map.
 *
 * @ORM\Table(name="UserHobbyMap")
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
     * @ORM\Column(name="userId", type="integer", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="hobbyId", type="integer", nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $hobbyId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @ORM\OneToOne(targetEntity="UserHobby"))
     * @ORM\JoinColumn(name="hobbyId", referencedColumnName="id")
     * @Serializer\Groups({"main", "profile"})
     **/
    private $hobby;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="hobbies")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
     **/
    private $user;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return UserHobbyMap
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getHobbyId()
    {
        return $this->hobbyId;
    }

    /**
     * @param int $hobbyId
     *
     * @return UserHobbyMap
     */
    public function setHobbyId($hobbyId)
    {
        $this->hobbyId = $hobbyId;
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
     * Get hobby.
     *
     * @return UserHobby
     */
    public function getHobby()
    {
        return $this->hobby;
    }
}
