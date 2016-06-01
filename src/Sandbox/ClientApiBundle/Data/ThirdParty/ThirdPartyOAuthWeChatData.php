<?php

namespace Sandbox\ClientApiBundle\Data\ThirdParty;

/**
 * Third Party OAuth WeChat Data Incoming Data.
 */
class ThirdPartyOAuthWeChatData
{
    const DATA_FROM_APPLICATION = 'app';
    const DATA_FROM_WEBSITE = 'web';

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $from = self::DATA_FROM_APPLICATION;

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
    public function isFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }
}
