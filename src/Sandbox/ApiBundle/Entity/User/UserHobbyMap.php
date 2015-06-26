<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * User Hobby Map
 *
 * @ORM\Table(name="UserHobbyMap")
 * @ORM\Entity
 *
 */
class UserHobbyMap
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer",  nullable=false)
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
     * @ORM\Column(name="hobbyId", type="integer", nullable=false)
     */
    private $hobbyId;

    /**
     * @ORM\ManyToOne(targetEntity="UserProfile", inversedBy="$hobbies")
     * @ORM\JoinColumn(name="userId", referencedColumnName="userId")
     **/
    private $userProfile;

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
}
