<?php

namespace Sandbox\ApiBundle\Entity\ThirdParty;

use Doctrine\ORM\Mapping as ORM;

/**
 * WeChatShares.
 *
 * @ORM\Table(name="WeChatShares")
 * @ORM\Entity
 */
class WeChatShares
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
     * @var string
     *
     * @ORM\Column(name="appId", type="string", length=128)
     */
    private $appId;

    /**
     * @var string
     *
     * @ORM\Column(name="secret", type="string", length=128)
     */
    private $secret;

    /**
     * @var string
     *
     * @ORM\Column(name="accessToken", type="string", length=1024, nullable=true)
     */
    private $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(name="jsapiTicket", type="string", length=1024, nullable=true)
     */
    private $jsapiTicket;

    /**
     * @var string
     *
     * @ORM\Column(name="expiresIn", type="string", length=1024)
     */
    private $expiresIn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
     */
    private $modificationDate;

    /**
     * @var string
     */
    private $url;

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
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param string $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
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
    public function getJsapiTicket()
    {
        return $this->jsapiTicket;
    }

    /**
     * @param string $jsapiTicket
     */
    public function setJsapiTicket($jsapiTicket)
    {
        $this->jsapiTicket = $jsapiTicket;
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
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return WeChatShares
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
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return WeChatShares
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * WeChatShares constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
        $this->modificationDate = new \DateTime('now');
    }
}
