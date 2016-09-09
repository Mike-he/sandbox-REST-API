<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * UserToken.
 *
 * @ORM\Table(
 *      name="user_token",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="token_UNIQUE", columns={"token"}),
 *          @ORM\UniqueConstraint(name="userId_clientId_UNIQUE", columns={"userId", "clientId"}),
 *          @ORM\UniqueConstraint(name="refresh_token_UNIQUE", columns={"refreshToken"})
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_UserToken_userId_idx", columns={"userId"}),
 *          @ORM\Index(name="fk_UserToken_clientId_idx", columns={"clientId"})
 *      }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\User\UserTokenRepository"
 *)
 */
class UserToken
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "login"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer", nullable=false)
     * @Serializer\Groups({"main", "login"})
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="tokens")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="clientId", type="integer", nullable=false)
     * @Serializer\Groups({"main", "login"})
     */
    private $clientId;

    /**
     * @ORM\ManyToOne(targetEntity="UserClient", inversedBy="tokens")
     * @ORM\JoinColumn(name="clientId", referencedColumnName="id")
     **/
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "login"})
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="refreshToken", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "login"})
     */
    private $refreshToken;

    /**
     * @var bool
     *
     * @ORM\Column(name="online", type="boolean", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $online = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main", "login"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main", "login"})
     */
    private $modificationDate;

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
     * @return UserToken
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get clientId.
     *
     * @return int
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set clientId.
     *
     * @param int $clientId
     *
     * @return UserToken
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Get token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token.
     *
     * @param string $token
     *
     * @return UserToken
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
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
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return UserToken
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
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
     * @return UserToken
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get client.
     *
     * @return UserClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set client.
     *
     * @param UserClient $client
     *
     * @return UserToken
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * Set online.
     *
     * @param bool $online
     *
     * @return UserToken
     */
    public function setOnline($online)
    {
        $this->online = $online;

        return $this;
    }

    /**
     * Get online.
     *
     * @return bool
     */
    public function getOnline()
    {
        return $this->online;
    }

    /**
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param \DateTime $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * UserToken constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
        $this->modificationDate = new \DateTime('now');
    }
}
