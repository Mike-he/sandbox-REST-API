<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * UserFavorite.
 *
 * @ORM\Table(name="user_favorites")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\User\UserFavoriteRepository")
 */
class UserFavorite
{
    const OBJECT_PRODUCT = 'product';
    const OBJECT_BUILDING = 'building';
    const OBJECT_SERVICE = 'service';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer")
     * @Serializer\Groups({"main"})
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="object", type="string", length=16)
     * @Serializer\Groups({"main", "user"})
     */
    private $object;

    /**
     * @var int
     *
     * @ORM\Column(name="objectId", type="integer")
     * @Serializer\Groups({"main", "user"})
     */
    private $objectId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     * @Serializer\Groups({"main", "user"})
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
     * @return UserFavorite
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
     * Set object.
     *
     * @param string $object
     *
     * @return UserFavorite
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object.
     *
     * @return string
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set objectId.
     *
     * @param int $objectId
     *
     * @return UserFavorite
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId.
     *
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return UserFavorite
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
        $now = new \DateTime('now');
        $this->setCreationDate($now);
    }
}
