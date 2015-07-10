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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="hobbies")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
     **/
    private $user;

    /**
     * @var UserHobby
     *
     * @ORM\OneToOne(targetEntity="UserHobby"))
     * @ORM\JoinColumn(name="hobbyId", referencedColumnName="id")
     * @Serializer\Groups({"main", "profile", "profile_stranger"})
     **/
    private $hobby;

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
