<?php

namespace Sandbox\ClientApiBundle\Data\User;

/**
 * Phone Binding Submit Incoming Data
 */
class PhoneBindingSubmit
{
    /**
     * @var string
     */
    private $phone;

    /**
     * Set phone
     *
     * @param  string             $phone
     * @return PhoneBindingSubmit
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }
}
