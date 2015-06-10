<?php

namespace Sandbox\ApiBundle\Entity\IncomingData;

/**
 * Phone Binding Submit Incoming Data
 */
class PhoneBindingSubmit
{
    /**
     * @var string
     */
    private $countrycode;

    /**
     * @var string
     */
    private $phone;

    /**
     * Set countrycode
     *
     * @param  string             $countrycode
     * @return PhoneBindingSubmit
     */
    public function setCountrycode($countrycode)
    {
        $this->countrycode = $countrycode;

        return $this;
    }

    /**
     * Get countrycode
     *
     * @return string
     */
    public function getCountrycode()
    {
        return $this->countrycode;
    }

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
