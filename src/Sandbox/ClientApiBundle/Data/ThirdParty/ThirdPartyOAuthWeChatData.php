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
}
