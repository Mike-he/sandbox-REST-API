<?php

namespace Sandbox\ClientApiBundle\Data\User;

/**
 * Phone Binding Verify Incoming Data.
 */
class PhoneBindingVerify
{
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
     * @return PhoneBindingVerify
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
     * @return PhoneBindingVerify
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
