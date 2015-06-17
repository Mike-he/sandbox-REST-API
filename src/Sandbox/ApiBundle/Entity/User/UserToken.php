<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserToken
 *
 * @ORM\Table(
 *      name="UserToken",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="token_UNIQUE", columns={"token"}),
 *          @ORM\UniqueConstraint(name="userId_clientId_UNIQUE", columns={"userId", "clientId"})
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_UserToken_userId_idx", columns={"userId"}),
 *          @ORM\Index(name="fk_UserToken_clientId_idx", columns={"clientId"})
 *      }
 * )
 * @ORM\Entity
 */
class UserToken
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="userId", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="clientId", type="integer", nullable=false)
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=64, nullable=false)
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set userId
     *
     * @param  integer   $userId
     * @return UserToken
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get clientId
     *
     * @return integer
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set clientId
     *
     * @param  integer   $clientId
     * @return UserToken
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token
     *
     * @param  string    $token
     * @return UserToken
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set creationDate
     *
     * @param  \DateTime $creationDate
     * @return UserToken
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }
}
