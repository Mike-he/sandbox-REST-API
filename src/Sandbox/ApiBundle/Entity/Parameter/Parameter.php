<?php

namespace Sandbox\ApiBundle\Entity\Parameter;

use Doctrine\ORM\Mapping as ORM;

/**
 * Parameter.
 *
 * @ORM\Table(
 *      name="parameter",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="key_UNIQUE", columns={"key"})}
 * )
 * @ORM\Entity
 */
class Parameter
{
    const KEY_LEASE_CONFIRM_EXPIRE_IN = 'lease_confirm_expire_in';
    const KEY_BEAN_USER_REGISTER = 'bean_user_register';
    const KEY_BEAN_USER_LOGIN = 'bean_user_login';
    const KEY_BEAN_USER_SHARE = 'bean_user_share';
    const KEY_BEAN_ORDER_EVALUATION = 'bean_order_evaluation';
    const KEY_BEAN_BUILDING_EVALUATION = 'bean_building_evaluation';
    const KEY_BEAN_SUCCESS_INVITATION = 'bean_success_invitation';
    const KEY_BEAN_INVITEE_PRODUCT_ORDER = 'bean_invitee_product_order';
    const KEY_BEAN_INVITEE_PAY_BILL = 'bean_invitee_pay_bill';
    const KEY_BEAN_PRODUCT_ORDER = 'bean_product_order';
    const KEY_BEAN_PAY_BILL = 'bean_pay_bill';
    const KEY_BEAN_SHOP_ORDER = 'bean_shop_order';
    const KEY_BEAN_EVENT_ORDER = 'bean_event_order';
    const KEY_BEAN_MEMBERSHIP_ORDER = 'bean_membership_order';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="`key`", type="string", length=64)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=128)
     */
    private $value;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
