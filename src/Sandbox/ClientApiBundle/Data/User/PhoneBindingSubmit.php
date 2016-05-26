<?php

namespace Sandbox\ClientApiBundle\Data\User;

/**
 * Phone Binding Submit Incoming Data.
 */
class PhoneBindingSubmit
{
    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $phoneCode;

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return PhoneBindingSubmit
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
}
