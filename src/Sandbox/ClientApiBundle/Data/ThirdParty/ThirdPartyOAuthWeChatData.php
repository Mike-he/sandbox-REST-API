<?php

namespace Sandbox\ClientApiBundle\Data\ThirdParty;

/**
 * Third Party OAuth WeChat Data Incoming Data.
 */
class ThirdPartyOAuthWeChatData
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $openId;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getOpenId()
    {
        return $this->openId;
    }

    /**
     * @param string $openId
     */
    public function setOpenId($openId)
    {
        $this->openId = $openId;
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
}
