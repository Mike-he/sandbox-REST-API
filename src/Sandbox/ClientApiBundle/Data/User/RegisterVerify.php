<?php

namespace Sandbox\ClientApiBundle\Data\User;

use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;

/**
 * Register Verify Incoming Data.
 */
class RegisterVerify
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $phoneCode;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $password;

    /**
     * @var ThirdPartyOAuthWeChatData
     */
    private $weChat;

    /**
     * @var int
     */
    private $inviterUserId;

    /**
     * @var string
     */
    private $inviterPhone;

    /**
     * @return string
     */
    public function getPhoneCode()
    {
        return $this->phoneCode;
    }

    /**
     * @param string $phoneCode
     */
    public function setPhoneCode($phoneCode)
    {
        $this->phoneCode = $phoneCode;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return RegisterVerify
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return RegisterVerify
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return RegisterVerify
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return RegisterVerify
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

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

    /**
     * @return int
     */
    public function getInviterUserId()
    {
        return $this->inviterUserId;
    }

    /**
     * @param int $inviterUserId
     */
    public function setInviterUserId($inviterUserId)
    {
        $this->inviterUserId = $inviterUserId;
    }

    /**
     * @return string
     */
    public function getInviterPhone()
    {
        return $this->inviterPhone;
    }

    /**
     * @param string $inviterPhone
     */
    public function setInviterPhone($inviterPhone)
    {
        $this->inviterPhone = $inviterPhone;
    }
}
