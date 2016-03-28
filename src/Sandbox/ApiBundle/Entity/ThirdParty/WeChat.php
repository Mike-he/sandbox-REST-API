<?php

namespace Sandbox\ApiBundle\Entity\ThirdParty;

use Doctrine\ORM\Mapping as ORM;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserClient;

/**
 * WeChat.
 *
 * @ORM\Table(
 *      name = "WeChat",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="openId_UNIQUE", columns={"openId"}),
 *          @ORM\UniqueConstraint(name="authCode_UNIQUE", columns={"authCode"})
 *      }
 * )
 * @ORM\Entity()
 */
class WeChat
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
     * @var User
     *
     * @ORM\OneToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User"))
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $user;

    /**
     * @var UserClient
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\UserClient"))
     * @ORM\JoinColumn(name="userClientId", referencedColumnName="id")
     **/
    private $userClient;

    /**
     * @var string
     *
     * @ORM\Column(name="openId", type="string", length=128, nullable=false)
     */
    private $openid;

    /**
     * @var string
     *
     * @ORM\Column(name="accessToken", type="string", length=256, nullable=true)
     */
    private $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(name="refreshToken", type="string", length=256, nullable=true)
     */
    private $refreshToken;

    /**
     * @var string
     *
     * @ORM\Column(name="expiresIn", type="string", length=16, nullable=true)
     */
    private $expiresIn;

    /**
     * @var string
     *
     * @ORM\Column(name="scope", type="string", length=512, nullable=true)
     */
    private $scope;

    /**
     * @var string
     *
     * @ORM\Column(name="unionId", type="string", length=512, nullable=true)
     */
    private $unionid;

    /**
     * @var string
     *
     * @ORM\Column(name="authCode", type="string", length=128, nullable=true)
     */
    private $authCode;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     */
    private $modificationDate;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return UserClient
     */
    public function getUserClient()
    {
        return $this->userClient;
    }

    /**
     * @param UserClient $userClient
     */
    public function setUserClient($userClient)
    {
        $this->userClient = $userClient;
    }

    /**
     * @return string
     */
    public function getOpenId()
    {
        return $this->openid;
    }

    /**
     * @param string $openid
     */
    public function setOpenId($openid)
    {
        $this->openid = $openid;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
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
     * @return string
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * @param string $expiresIn
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return string
     */
    public function getUnionId()
    {
        return $this->unionid;
    }

    /**
     * @param string $unionid
     */
    public function setUnionId($unionid)
    {
        $this->unionid = $unionid;
    }

    /**
     * @return string
     */
    public function getAuthCode()
    {
        return $this->authCode;
    }

    /**
     * @param string $authCode
     */
    public function setAuthCode($authCode)
    {
        $this->authCode = $authCode;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
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
}
