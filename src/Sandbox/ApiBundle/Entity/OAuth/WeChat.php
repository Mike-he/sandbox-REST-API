<?php

namespace Sandbox\ApiBundle\Entity\OAuth;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * WeChat.
 *
 * @ORM\Table(name = "WeChat")
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
     *
     * @Serializer\Groups({"main"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="openId", type="string", length=256, nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $openid;

    /**
     * @var string
     *
     * @ORM\Column(name="accessToken", type="string", length=512, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(name="refreshToken", type="string", length=512, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $refreshToken;

    /**
     * @var string
     *
     * @ORM\Column(name="expiresIn", type="string", length=16, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $expiresIn;

    /**
     * @var string
     *
     * @ORM\Column(name="scope", type="string", length=1024, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $scope;

    /**
     * @var string
     *
     * @ORM\Column(name="unionId", type="string", length=512, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $unionid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main"})
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
