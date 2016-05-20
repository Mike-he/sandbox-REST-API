<?php

namespace Sandbox\ClientApiBundle\Data\User;

/**
 * Password Forget Verify Incoming Data.
 */
class PasswordForgetVerify
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
     * Set email.
     *
     * @param string $email
     *
     * @return PasswordForgetVerify
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
     * @return PasswordForgetVerify
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
     * Set code.
     *
     * @param string $code
     *
     * @return PasswordForgetVerify
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
}
