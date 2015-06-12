<?php

namespace Sandbox\ClientApiBundle\Data;

/**
 * Register Submit Incoming Data
 */
class RegisterSubmit
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $countrycode;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $name;

    /**
     * Set email
     *
     * @param  string         $email
     * @return RegisterSubmit
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set countrycode
     *
     * @param  string         $countrycode
     * @return RegisterSubmit
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
     * @param  string         $phone
     * @return RegisterSubmit
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

    /**
     * Set password
     *
     * @param  string         $password
     * @return RegisterSubmit
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set name
     *
     * @param  string         $name
     * @return RegisterSubmit
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
