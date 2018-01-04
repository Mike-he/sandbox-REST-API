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

    // BEAN
    const KEY_BEAN_USER_REGISTER = 'bean_user_register';
    const KEY_BEAN_USER_LOGIN = 'bean_user_login';
    const KEY_BEAN_USER_SHARE = 'bean_user_share';
    const KEY_BEAN_ORDER_EVALUATION = 'bean_order_evaluation';
    const KEY_BEAN_SUCCESS_INVITATION = 'bean_success_invitation';
    const KEY_BEAN_INVITEE_PRODUCT_ORDER = 'bean_invitee_product_order';
    const KEY_BEAN_INVITEE_PAY_BILL = 'bean_invitee_pay_bill';
    const KEY_BEAN_PRODUCT_ORDER = 'bean_product_order';
    const KEY_BEAN_PAY_BILL = 'bean_pay_bill';
    const KEY_BEAN_SHOP_ORDER = 'bean_shop_order';
    const KEY_BEAN_MEMBERSHIP_ORDER = 'bean_membership_order';
    const KEY_BEAN_PRODUCT_ORDER_PREORDER = 'bean_product_order_preorder';

    // Poundage
    const KEY_POUNDAGE = 'poundage_';
    const KEY_POUNDAGE_ACCOUNT = 'poundage_account';
    const KEY_POUNDAGE_WX = 'poundage_wx';
    const KEY_POUNDAGE_ALIPAY = 'poundage_alipay';
    const KEY_POUNDAGE_UPACP = 'poundage_upacp';
    const KEY_POUNDAGE_WX_PUB = 'poundage_wx_pub';
    const KEY_POUNDAGE_OFFLINE = 'poundage_offline';

    // Link
    const KEY_HTML_WAIT_APPLY = 'html_wait_apply';                              //等待抢单列表
    const KEY_HTML_MY_APPLY = 'html_my_apply';                                  //我的近期抢单列表
    const KEY_HTML_ROOM_PARAM_ID = 'html_room_param_id';                        //空间管理
    const KEY_HTML_APPLY = 'html_apply';                                        //预约看房
    const KEY_HTML_ROOM_ORDER_PARAM_ID = 'html_room_order_param_id';            //空间订单
    const KEY_HTML_EVENT_ORDER_PARAM_ID = 'html_event_order_param_id';          //活动订单
    const KEY_HTML_MEMBER_ORDER_PARAM_ID= 'html_member_order_param_id';         //会员卡订单
    const KEY_HTML_OFFER_PARAM_ID = 'html_offer_param_id';                      //报价
    const KEY_HTML_BILL_PARAM_ID = 'html_bill_param_id';                        //账单
    const KEY_HTML_CONTRACT_PARAM_ID = 'html_contract_param_id';                //合同
    const KEY_HTML_CLUE_PARAM_ID = 'html_clue_param_id';                        //线索
    const KEY_HTML_CUSTOMER = 'html_customer_relationship';                     //客户关系
    const KEY_HTML_CUSTOMER_CREATE_PERSONAL = 'html_customer_create_personal';  //创建个人客户
    const KEY_HTML_CUSTOMER_CREATE_COMPANY = 'html_customer_create_company';    //创建企业客户
    const KEY_HTML_CLUE_CREATE = 'html_clue_create';                            //创建线索
    const KEY_HTML_CUSTOMER_PROFILE_DETAIL = 'html_customer_profile_detail';    //客户详情
    const KEY_HTML_CUSTOMER_ENTERPRISE_DETAIL = 'html_customer_enterprise_detail'; //客户详情

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
