<?php

namespace Sandbox\ClientApiBundle\Data\Payment;

class UserPaymentCheck
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $password;

    /**
     * @var bool
     */
    private $touchID;

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return bool
     */
    public function isTouchID()
    {
        return $this->touchID;
    }

    /**
     * @param bool $touchID
     */
    public function setTouchID($touchID)
    {
        $this->touchID = $touchID;
    }
}