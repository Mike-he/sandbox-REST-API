<?php

namespace Sandbox\ClientApiBundle\Data\ThirdParty;

use Sandbox\ClientApiBundle\Data\User\UserLoginData;

/**
 * Third Party OAuth Login Incoming Data.
 */
class ThirdPartyOAuthLoginData extends UserLoginData
{
    /**
     * @var ThirdPartyOAuthWeChatData
     */
    private $weChat;

    /**
     * @return ThirdPartyOAuthWeChatData
     */
    public function getWeChat()
    {
        return $this->weChat;
    }

    /**
     * @param ThirdPartyOAuthWeChatData $weChat
     */
    public function setWeChat($weChat)
    {
        $this->weChat = $weChat;
    }
}
