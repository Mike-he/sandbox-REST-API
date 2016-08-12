<?php

namespace Sandbox\ApiBundle\Controller\Payment;

use FOS\RestBundle\View\View;
use Proxies\__CG__\Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Constants\BundleConstants;
use Sandbox\ApiBundle\Constants\DoorAccessConstants;
use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Entity\Order\TopUpOrder;
use Sandbox\ApiBundle\Entity\Order\MembershipOrder;
use Sandbox\ApiBundle\Entity\Order\OrderCount;
use Sandbox\ApiBundle\Entity\Door\DoorAccess;
use Sandbox\ApiBundle\Entity\Order\OrderMap;
use Sandbox\ApiBundle\Entity\Food\FoodOrder;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Pingpp\Pingpp;
use Pingpp\Charge;
use Pingpp\Customer;
use Pingpp\Error\Base;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Traits\YunPianSms;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sandbox\ApiBundle\Traits\StringUtil;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Sandbox\ApiBundle\Traits\ProductOrderNotification;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Request;

/**
 * Payment Controller.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class PaymentController extends DoorController
{
    use StringUtil;
    use DoorAccessTrait;
    use ProductOrderNotification;
    use YunPianSms;

    const STATUS_PAID = 'paid';
    const ORDER_CONFLICT_MESSAGE = 'Order Conflict';
    const INSUFFICIENT_FUNDS_CODE = 400001;
    const INSUFFICIENT_FUNDS_MESSAGE = 'Insufficient funds in account balance - 余额不足';
    const SYSTEM_ERROR_CODE = 500001;
    const SYSTEM_ERROR_MESSAGE = 'System error - 系统出错';
    const INVALID_FORM_CODE = 400002;
    const INVALID_FORM_MESSAGE = 'Invalid Form';
    const PRODUCT_NOT_FOUND_CODE = 400003;
    const PRODUCT_NOT_FOUND_MESSAGE = 'Product Does Not Exist';
    const PRICE_MISMATCH_CODE = 400005;
    const PRICE_MISMATCH_MESSAGE = 'PRICE DOES NOT MATCH';
    const WRONG_PAYMENT_STATUS_CODE = 400006;
    const WRONG_PAYMENT_STATUS_MESSAGE = 'WRONG STATUS';
    const ORDER_NOT_FOUND_CODE = 400007;
    const ORDER_NOT_FOUND_MESSAGE = 'Can not find order';
    const USER_NOT_FOUND_CODE = 400008;
    const USER_NOT_FOUND_MESSAGE = 'Can not find user in current order';
    const USER_EXIST_CODE = 400009;
    const USER_EXIST_MESSAGE = 'This user already exist';
    const WRONG_CHANNEL_CODE = 400010;
    const WRONG_CHANNEL_MESSAGE = 'THIS CHANNEL IS NOT SUPPORTED';
    const NO_PRICE_CODE = 400011;
    const NO_PRICE_MESSAGE = 'Price can not be empty';
    const NO_APPOINTED_PERSON_CODE = 400012;
    const NO_APPOINTED_PERSON_CODE_MESSAGE = 'Need an appoint person ID';
    const NOT_WITHIN_DATE_RANGE_CODE = 400013;
    const NOT_WITHIN_DATE_RANGE_MESSAGE = 'Not Within 7 Days For Booking';
    const CAN_NOT_RENEW_CODE = 400014;
    const CAN_NOT_RENEW_MESSAGE = 'Have to renew 7 days before current order end date';
    const WRONG_ORDER_STATUS_CODE = 400015;
    const WRONG_ORDER_STATUS_MESSAGE = 'Wrong Order Status';
    const WRONG_CHARGE_ID_CODE = 400016;
    const WRONG_CHARGE_ID__MESSAGE = 'Wrong Charge ID';
    const WRONG_BOOKING_DATE_CODE = 400017;
    const WRONG_BOOKING_DATE_MESSAGE = 'Wrong Booking Date';
    const NO_VIP_PRODUCT_ID_CODE = 400018;
    const NO_VIP_PRODUCT_ID_CODE_MESSAGE = 'No VIP Product ID';
    const DISCOUNT_PRICE_MISMATCH_CODE = 400020;
    const DISCOUNT_PRICE_MISMATCH_MESSAGE = 'Discount Price Does Not Match';
    const ROOM_NOT_OPEN_CODE = 400021;
    const ROOM_NOT_OPEN_MESSAGE = 'Meeting Room Is Not Opening During This Hour';
    const PRODUCT_NOT_AVAILABLE_CODE = 400022;
    const PRODUCT_NOT_AVAILABLE_MESSAGE = 'Product Is Not Available';
    const FOOD_SOLD_OUT_CODE = 400024;
    const FOOD_SOLD_OUT_MESSAGE = 'This Item Is Sold Out';
    const FOOD_DOES_NOT_EXIST_CODE = 400025;
    const FOOD_DOES_NOT_EXIST_MESSAGE = 'This Item Does Not Exist';
    const FOOD_OPTION_DOES_NOT_EXIST_CODE = 400026;
    const FOOD_OPTION_DOES_NOT_EXIST_MESSAGE = 'This Option Does Not Exist';
    const PRICE_RULE_DOES_NOT_EXIST_CODE = 400027;
    const PRICE_RULE_DOES_NOT_EXIST_MESSAGE = 'This price rule doees not exist';
    const WRONG_REFUND_AMOUNT_CODE = 400028;
    const WRONG_REFUND_AMOUNT_MESSAGE = 'Refund Amount Can Not Exceed Paid Amount';
    const REFUND_AMOUNT_NOT_FOUND_CODE = 400029;
    const REFUND_AMOUNT_NOT_FOUND_MESSAGE = 'Refund Amount Does Not Exist';
    const REFUND_SSN_NOT_FOUND_CODE = 400030;
    const REFUND_SSN_NOT_FOUND_MESSAGE = 'Refund SSN Does Not Exist';
    const PAYMENT_CHANNEL_ALIPAY_WAP = 'alipay_wap';
    const PAYMENT_CHANNEL_UPACP_WAP = 'upacp_wap';
    const PAYMENT_CHANNEL_ACCOUNT = 'account';
    const PAYMENT_CHANNEL_ALIPAY = 'alipay';
    const PAYMENT_CHANNEL_UPACP = 'upacp';
    const PAYMENT_CHANNEL_WECHAT = 'wx';
    const ORDER_REFUND = 'refund';

    /**
     * @param $customerId
     * @param $cardId
     *
     * @return mixed
     */
    protected function getSingleCustomerCard(
        $customerId,
        $cardId
    ) {
        $global = $this->get('twig')->getGlobals();
        $key = $global['pingpp_key'];

        $ch = curl_init(BundleConstants::PING_CREATE_CUSTOMER.'/'.$customerId.'/sources/'.$cardId);

        $response = $this->callAPI(
            $ch,
            'GET',
            array('Authorization: Bearer '.$key)
        );

        return $response;
    }

    /**
     * @param $customerId
     *
     * @return mixed
     */
    protected function getCustomerCards(
        $customerId
    ) {
        $global = $this->get('twig')->getGlobals();
        $key = $global['pingpp_key'];

        $ch = curl_init(BundleConstants::PING_CREATE_CUSTOMER.'/'.$customerId.'/sources');

        $response = $this->callAPI(
            $ch,
            'GET',
            array('Authorization: Bearer '.$key)
        );

        return $response;
    }

    /**
     * @param $customerId
     * @param $cardId
     *
     * @return mixed
     */
    protected function deleteCustomerCard(
        $customerId,
        $cardId
    ) {
        $global = $this->get('twig')->getGlobals();
        $key = $global['pingpp_key'];

        $ch = curl_init(BundleConstants::PING_CREATE_CUSTOMER.'/'.$customerId.'/sources/'.$cardId);

        $response = $this->callAPI(
            $ch,
            'DELETE',
            array('Authorization: Bearer '.$key)
        );

        return $response;
    }

    /**
     * @param $customerId
     * @param $token
     * @param $smsId
     * @param $smsCode
     *
     * @return mixed
     */
    protected function createCustomerCard(
        $customerId,
        $token,
        $smsId,
        $smsCode
    ) {
        $global = $this->get('twig')->getGlobals();
        $key = $global['pingpp_key'];

        $data = array(
            'source' => $token,
            'sms_code' => [
                'code' => $smsCode,
                'id' => $smsId,
            ],
        );

        $ch = curl_init(BundleConstants::PING_CREATE_CUSTOMER.'/'.$customerId.'/sources');

        $response = $this->callAPI(
            $ch,
            'POST',
            array('Authorization: Bearer '.$key),
            json_encode($data)
        );

        return $response;
    }

    /**
     * @param $customerId
     * @param $cardId
     *
     * @return mixed
     */
    protected function putCustomer(
        $customerId,
        $cardId
    ) {
        $global = $this->get('twig')->getGlobals();
        $key = $global['pingpp_key'];

        $data = array(
            'default_source' => $cardId,
        );

        $ch = curl_init(BundleConstants::PING_CREATE_CUSTOMER.'/'.$customerId);

        $response = $this->callAPI(
            $ch,
            'PUT',
            array('Authorization: Bearer '.$key),
            json_encode($data)
        );

        return $response;
    }

    /**
     * @param $customerId
     *
     * @return mixed
     */
    protected function deleteCustomer(
        $customerId
    ) {
        $global = $this->get('twig')->getGlobals();
        $key = $global['pingpp_key'];

        $ch = curl_init(BundleConstants::PING_CREATE_CUSTOMER.'/'.$customerId);

        $response = $this->callAPI(
            $ch,
            'DELETE',
            array('Authorization: Bearer '.$key)
        );

        return $response;
    }

    /**
     * @param $customerId
     *
     * @return Customer
     */
    protected function retrieveCustomer(
        $customerId
    ) {
        $global = $this->get('twig')->getGlobals();
        $key = $global['pingpp_key'];

        Pingpp::setApiKey($key);
        try {
            $customer = Customer::retrieve($customerId);

            return $customer;
        } catch (Base $e) {
            header('Status: '.$e->getHttpStatus());
            echo $e->getHttpBody();
        }
    }

    /**
     * @param $token
     * @param $smsId
     * @param $smsCode
     *
     * @return Customer
     */
    protected function createCustomer(
        $token,
        $smsId,
        $smsCode
    ) {
        $global = $this->get('twig')->getGlobals();
        $key = $global['pingpp_key'];
        $appId = $global['pingpp_app_id'];

        Pingpp::setApiKey($key);
        try {
            $customer = Customer::create(
                [
                    'app' => $appId,
                    'source' => $token,
                    'sms_code' => [
                        'code' => $smsCode,
                        'id' => $smsId,
                    ],
                ]
            );

            return $customer;
        } catch (Base $e) {
            header('Status: '.$e->getHttpStatus());
            echo $e->getHttpBody();
        }
    }

//    protected function createCustomer(
//        $token,
//        $smsId,
//        $smsCode
//    ) {
//        $global = $this->get('twig')->getGlobals();
//        $key = $global['pingpp_key'];
//        $appId = $global['pingpp_app_id'];
//
//        $data = array(
//            'app' => $appId,
//            'source' => $token,
//            'sms_code' => [
//                'code' => $smsCode,
//                'id' => $smsId
//            ]
//        );
//
//        $ch = curl_init(BundleConstants::PING_CREATE_CUSTOMER);
//
//        $response = $this->callAPI(
//            $ch,
//            'POST',
//            array('Authorization: Bearer '.$key),
//            json_encode($data)
//        );
//
//        return $response;
//    }

    /**
     * @Post("/payment/token")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getCreditTokenAction(
        Request $request
    ) {
        $content = json_decode($request->getContent(), true);

        if (array_key_exists('order_no', $content) &&
            array_key_exists('token_id', $content) &&
            array_key_exists('channel', $content)
        ) {
            $orderNumber = $content['order_no'];
            $token = $content['token_id'];
            $channel = $content['channel'];

            $letter = substr($orderNumber, 0, 1);

            switch ($letter) {
                case 'P':
                    $order = $this->getDoctrine()
                        ->getRepository(BundleConstants::PRODUCT_ORDER_ENTITY)
                        ->findOneByOrderNumber($orderNumber);

                    if (is_null($order)) {
                        return new View();
                    }

                    $price = $order->getDiscountPrice();
                    $subject = ProductOrder::PAYMENT_SUBJECT;
                    $body = ProductOrder::PAYMENT_BODY;

                    break;
                case 'E':
                    $order = $this->getDoctrine()
                        ->getRepository(BundleConstants::EVENT_ORDER_ENTITY)
                        ->findOneByOrderNumber($orderNumber);

                    if (is_null($order)) {
                        return new View();
                    }

                    $price = $order->getPrice();
                    $subject = EventOrder::PAYMENT_SUBJECT;
                    $body = EventOrder::PAYMENT_BODY;

                    break;
                case 'S':
                    $order = $this->getDoctrine()
                        ->getRepository(BundleConstants::SHOP_ORDER_ENTITY)
                        ->findOneByOrderNumber($orderNumber);

                    if (is_null($order)) {
                        return new View();
                    }

                    $price = $order->getPrice();
                    $subject = ShopOrder::PAYMENT_SUBJECT;
                    $body = ShopOrder::PAYMENT_BODY;

                    break;
                default:

                    return new View();
            }

            if (ProductOrder::STATUS_UNPAID == $order->getStatus()) {
                $charge = $this->payForOrder(
                    $token,
                    null,
                    null,
                    $orderNumber,
                    $price,
                    $channel,
                    $subject,
                    $body
                );

                $charge = json_decode($charge, true);

                return new View($charge);
            }
        }

        return new View();
    }

    /**
     * @param $channel
     *
     * @return int
     */
    protected function getRefundFeeMultiplier(
        $channel
    ) {
        $globals = $this->getGlobals();
        $multiplier = 0;

        switch ($channel) {
            case ProductOrder::CHANNEL_ACCOUNT:
                $multiplier = $globals['account_refund_fee_multiplier'];
                break;
            case ProductOrder::CHANNEL_ALIPAY:
                $multiplier = $globals['alipay_refund_fee_multiplier'];
                break;
            case ProductOrder::CHANNEL_UNIONPAY:
                $multiplier = $globals['union_refund_fee_multiplier'];
                break;
            case ProductOrder::CHANNEL_WECHAT:
                $multiplier = $globals['wechat_refund_fee_multiplier'];
                break;
            case ProductOrder::CHANNEL_FOREIGN_CREDIT:
                $multiplier = $globals['foreign_credit_refund_fee_multiplier'];
                break;
        }

        return $multiplier;
    }

    /**
     * @param object $order
     * @param float  $amount
     * @param string $type
     * 
     * @return Charge
     */
    public function refundToPayChannel(
        $order,
        $amount,
        $type
    ) {
        $map = $this->getRepo('Order\OrderMap')->findOneBy(
            [
                'orderNumber' => $order->getOrderNumber(),
                'type' => $type,
            ]
        );
        $this->throwNotFoundIfNull($map, self::NOT_FOUND_MESSAGE);

        $chargeId = $map->getChargeId();
        if (is_null($chargeId) || empty($chargeId)) {
            return array();
        }

        $globals = $this->get('twig')->getGlobals();
        $key = $globals['pingpp_key'];

        Pingpp::setApiKey($key);
        try {
            $ch = \Pingpp\Charge::retrieve("$chargeId");

            $refund = $ch->refunds->create(
                array(
                    'amount' => $amount * 100,
                    'description' => 'Your Descripton',
                )
            );

            $this->checkRefund($refund, $order);

            return $refund;
        } catch (Base $e) {
            header('Status: '.$e->getHttpStatus());
            echo $e->getHttpBody();
        }
    }

    /**
     * @param $refund
     * @param $order
     */
    private function checkRefund(
        $refund,
        $order
    ) {
        $refund = json_decode($refund, true);

        if (!array_key_exists('failure_msg', $refund)
            || !array_key_exists('id', $refund)
            || !array_key_exists('status', $refund)
        ) {
            return;
        }

        $order->setRefundProcessed(true);
        $order->setRefundProcessedDate(new \DateTime());
        $order->setModificationDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @param json $refund
     *
     * @return string
     */
    protected function getRefundLink(
        $refund
    ) {
        if (empty($refund['failure_msg'])) {
            return;
        }

        $link = $refund['failure_msg'];

        $linkArray = explode('https://', $link);
        $link = 'https://'.$linkArray[1];

        return $link;
    }

    /**
     * @param object $order
     * @param float  $price
     *
     * @return string
     */
    protected function checkForRefund(
        $order,
        $price,
        $map
    ) {
        $em = $this->getDoctrine()->getManager();
        $channel = $order->getPayChannel();
        $link = '';

        if (ProductOrder::CHANNEL_ACCOUNT == $channel) {
            $balance = $this->postBalanceChange(
                $order->getUserId(),
                $price,
                $order->getOrderNumber(),
                self::PAYMENT_CHANNEL_ACCOUNT,
                0,
                self::ORDER_REFUND
            );

            if (!is_null($balance)) {
                $order->setRefunded(true);
            }
        } elseif (ProductOrder::CHANNEL_ALIPAY == $channel) {
            $link = $order->getRefundUrl();

            if (is_null($link) || empty($link)) {
                $refund = $this->refundToPayChannel(
                    $order,
                    $price,
                    $map
                );

                $link = $this->getRefundLink($refund);
                $order->setRefundUrl($link);
            }
        } elseif (ProductOrder::CHANNEL_UNIONPAY == $channel) {
            $order->setRefundProcessed(true);
            $order->setRefundProcessedDate(new \DateTime());
            $order->setModificationDate(new \DateTime());
        } else {
            if (!$order->isRefundProcessed()) {
                $this->refundToPayChannel(
                    $order,
                    $price,
                    $map
                );
            }
        }

        $em->flush();

        return $link;
    }

    /**
     * @param string $orderNo
     * @param string $channel
     * @param string $chargeId
     *
     * @return string
     */
    public function getJsonData(
        $orderNo,
        $channel,
        $chargeId,
        $status
    ) {
        $dataArray = [
            'order_no' => $orderNo,
            'paid' => $status,
            'channel' => $channel,
            'transaction_id' => $chargeId,
        ];

        return json_encode($dataArray);
    }

    /**
     * @param $userId
     * @param $orderNo
     * @param $amount
     *
     * @return View
     */
    public function accountPayment(
        $userId,
        $orderNo,
        $amount
    ) {
        $balance = $this->postBalanceChange(
            $userId,
            (-1) * $amount,
            $orderNo,
            self::PAYMENT_CHANNEL_ACCOUNT,
            $amount
        );

        if (is_null($balance)) {
            return $this->customErrorView(
                400,
                self::INSUFFICIENT_FUNDS_CODE,
                self::INSUFFICIENT_FUNDS_MESSAGE
            );
        }

        $view = new View();
        $view->setData(
            [
                'order_no' => $orderNo,
                'paid' => true,
                'channel' => self::PAYMENT_CHANNEL_ACCOUNT,
            ]
        );

        return $view;
    }

    /**
     * @param object $order
     * @param string $channel
     */
    public function storePayChannel(
        $order,
        $channel
    ) {
        $order->setPayChannel($channel);
    }

    /**
     * @param $token
     * @param $smsId
     * @param $smsCode
     * @param $orderNumber
     * @param $price
     * @param $channel
     * @param $subject
     * @param $body
     *
     * @return Charge
     */
    public function payForOrder(
        $token,
        $smsId,
        $smsCode,
        $orderNumber,
        $price,
        $channel,
        $subject,
        $body
    ) {
        $extra = [];
        switch ($channel) {
            case self::PAYMENT_CHANNEL_ALIPAY_WAP:
                $extra = array(
                    'success_url' => 'http://www.yourdomain.com/success',
                    'cancel_url' => 'http://www.yourdomain.com/cancel',
                );
                break;
            case self::PAYMENT_CHANNEL_UPACP_WAP:
                $extra = array(
                    'result_url' => 'http://www.yourdomain.com/result?code=',
                );
                break;
            case ProductOrder::CHANNEL_FOREIGN_CREDIT:
                $extra = array(
                    'source' => $token,
                );

                break;
            case ProductOrder::CHANNEL_UNION_CREDIT:
                $extra = array(
                    'source' => $token,
                    'sms_code[id]' => $smsId,
                    'sms_code[code]' => $smsCode,
                );

                break;
        }

        $keyGlobal = $this->get('twig')->getGlobals();
        $key = $keyGlobal['pingpp_key'];
        $appGlobal = $this->get('twig')->getGlobals();
        $appId = $appGlobal['pingpp_app_id'];

        Pingpp::setApiKey($key);
        try {
            $ch = Charge::create(
                array(
                    'order_no' => $orderNumber,
                    'amount' => $price * 100,
                    'app' => array('id' => $appId),
                    'channel' => $channel,
                    'currency' => 'cny',
                    'extra' => $extra,
                    'client_ip' => $_SERVER['REMOTE_ADDR'],
                    'subject' => $subject,
                    'body' => $body,
                )
            );

            return $ch;
        } catch (Base $e) {
            header('Status: '.$e->getHttpStatus());
            echo $e->getHttpBody();
        }
    }

    /**
     * @param $chargeId
     *
     * @return Charge
     */
    public function getChargeDetail(
        $chargeId
    ) {
        $keyGlobal = $this->get('twig')->getGlobals();
        $key = $keyGlobal['pingpp_key'];
        Pingpp::setApiKey($key);
        try {
            $ch = Charge::retrieve($chargeId);

            return $ch;
        } catch (Base $e) {
            header('Status: '.$e->getHttpStatus());
            echo $e->getHttpBody();
        }
    }

    /**
     * @param string $orderNumber
     * @param string $channel
     *
     * @return ProductOrder
     */
    public function setProductOrder(
        $orderNumber,
        $channel
    ) {
        $order = $this->getRepo('Order\ProductOrder')->findOneBy(
            [
                'orderNumber' => $orderNumber,
            ]
        );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $status = $order->getStatus();

        if ($status != ProductOrder::STATUS_CANCELLED &&
            $status != ProductOrder::STATUS_UNPAID
        ) {
            throw new NotFoundHttpException();
        }

        if ($status == ProductOrder::STATUS_CANCELLED) {
            // check if order conflict
            $orders = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Order\ProductOrder')
                ->checkProductForClient(
                    $order->getProductId(),
                    $order->getStartDate(),
                    $order->getEndDate()
                );

            if (!empty($orders)) {
                $order->setNeedToRefund(true);
            } else {
                $order->setStatus(self::STATUS_PAID);
            }
        } else {
            $order->setStatus(self::STATUS_PAID);
        }

        $order->setPaymentDate(new \DateTime());
        $order->setModificationDate(new \DateTime());

        // store payment channel
        $this->storePayChannel(
            $order,
            $channel
        );

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        //send message
        $type = $order->getProduct()->getRoom()->getType();

        if (Room::TYPE_OFFICE == $type && is_null($order->getType())) {
            $this->sendXmppProductOrderNotification(
                null,
                null,
                ProductOrder::ACTION_OFFICE_ORDER,
                null,
                [$order],
                ProductOrderMessage::OFFICE_ORDER_MESSAGE
            );
        }

        // set door access
        if (!$order->isRejected() && $status == ProductOrder::STATUS_PAID) {
            $this->setDoorAccessForSingleOrder($order);
        }

        return $order;
    }

    /**
     * @param string $orderNumber
     * @param string $channel
     *
     * @return ShopOrder
     */
    public function setShopOrderStatus(
        $orderNumber,
        $channel
    ) {
        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Shop\ShopOrder')
            ->findOneBy(
                [
                    'orderNumber' => $orderNumber,
                ]
            );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $status = $order->getStatus();

        if ($status != ShopOrder::STATUS_CANCELLED &&
            $status != ShopOrder::STATUS_UNPAID
        ) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime();

        if ($status == ProductOrder::STATUS_CANCELLED) {
            // check if shop still open
            $shopId = $order->getShopId();

            $shop = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Shop\Shop')
                ->findOneBy(
                    [
                        'id' => $shopId,
                        'isDeleted' => false,
                        'active' => true,
                        'online' => true,
                        'close' => false,
                    ]
                );
            $this->throwNotFoundIfNull($shop, self::NOT_FOUND_MESSAGE);

            $orderProducts = $order->getShopOrderProducts();

            foreach ($orderProducts as $orderProduct) {
                $orderProductSpecs = $orderProduct->getShopOrderProductSpecs();

                foreach ($orderProductSpecs as $orderProductSpec) {
                    $specInfo = $orderProductSpec->getshopProductSpecInfo();
                    $specInfo = json_decode($specInfo, true);

                    if ($specInfo['spec']['has_inventory']) {
                        $orderProductSpecItems = $orderProductSpec->getShopOrderProductSpecItems();

                        foreach ($orderProductSpecItems as $orderProductSpecItem) {
                            $amount = $orderProductSpecItem->getAmount();
                            $productSpecItem = $orderProductSpecItem->getItem();
                            $inventory = $productSpecItem->getInventory();

                            if ($amount > $inventory) {
                                $order->setRefundAmount($order->getPrice());
                                $order->setPayChannel($channel);
                                $order->setPaymentDate($now);
                                $order->setModificationDate($now);
                                $order->setStatus(ShopOrder::STATUS_TO_BE_REFUNDED);

                                $em->flush();

                                return $order;
                            }

                            $productSpecItem->setInventory($inventory - $amount);
                        }
                    }
                }
            }
        }

        $order->setStatus(ShopOrder::STATUS_PAID);
        $order->setPayChannel($channel);
        $order->setPaymentDate($now);
        $order->setModificationDate($now);

        $em->flush();

        return $order;
    }

    /**
     * @param $orderNumber
     * @param $channel
     *
     * @return EventOrder
     */
    public function setEventOrderStatus(
        $orderNumber,
        $channel
    ) {
        $order = $this->getRepo('Event\EventOrder')->findOneBy(
            [
                'orderNumber' => $orderNumber,
                'status' => ShopOrder::STATUS_UNPAID,
            ]
        );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $now = new \DateTime();
        $order->setStatus(ShopOrder::STATUS_PAID);
        $order->setPaymentDate($now);
        $order->setPayChannel($channel);
        $order->setModificationDate($now);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $order;
    }

    /**
     * @param ProductOrder $order
     */
    public function setDoorAccessForSingleOrder(
        $order
    ) {
        if ($order->isRejected()) {
            return;
        }

        // send order email
        $this->sendOrderEmail($order);

        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        if (is_null($building)) {
            return;
        }

        $base = $building->getServer();
        if (is_null($base) || empty($base)) {
            return;
        }

        $roomId = $order->getProduct()->getRoom()->getId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findBy(['room' => $roomId]);
        if (empty($roomDoors)) {
            return;
        }

        $em = $this->getDoctrine()->getManager();

        $userId = $order->getUserId();
        $this->storeDoorAccess(
            $em,
            $order,
            $userId,
            $buildingId,
            $roomId
        );
        $em->flush();
        $result = $this->getCardNoByUser($userId);
        if (
            !is_null($result) &&
            $result['status'] === DoorController::STATUS_AUTHED
        ) {
            $this->callSetCardAndRoomCommand(
                $base,
                $userId,
                $result['card_no'],
                $roomDoors,
                $order
            );
        }
    }

    /**
     * @param $order
     * @param $roomDoors
     */
    public function storeDoorAccess(
        $em,
        $order,
        $userId,
        $buildingId,
        $roomId
    ) {
        $doorAccess = $this->getRepo('Door\DoorAccess')->findOneBy(
            [
                'userId' => $userId,
                'orderId' => $order->getId(),
            ]
        );
        if (is_null($doorAccess)) {
            $access = new DoorAccess();
            $access->setBuildingId($buildingId);
            $access->setUserId($userId);
            $access->setRoomId($roomId);
            $access->setOrderId($order->getId());
            $access->setStartDate($order->getStartDate());
            $access->setEndDate($order->getEndDate());

            $em->persist($access);
        } else {
            $doorAccess->setAction(DoorAccessConstants::METHOD_ADD);
            $doorAccess->isAccess() ?
                $doorAccess->setAccess(false) : $doorAccess->setAccess(true);
        }
    }

    /**
     * @param $orderId
     * @param $currentUser
     * @param $base
     * @param $globals
     */
    public function removeUserAccess(
        $orderId,
        $base
    ) {
        $currentUserArray = [];
        $controls = $this->getRepo('Door\DoorAccess')->findBy(
            [
                'orderId' => $orderId,
                'action' => DoorAccessConstants::METHOD_DELETE,
                'access' => false,
            ]
        );
        if (!empty($controls)) {
            foreach ($controls as $control) {
                $userId = $control->getUserId();
                // get user cardNo and remove access from order
                $result = $this->getCardNoByUser($userId);
                if ($result['status'] !== DoorController::STATUS_UNAUTHED) {
                    $empUser = ['empid' => $userId];
                    array_push($currentUserArray, $empUser);
                }
            }
        }

        if (!empty($currentUserArray)) {
            $this->callRemoveFromOrderCommand(
                $base,
                $orderId,
                $currentUserArray
            );
        }
    }

    /**
     * @param $orderId
     * @param $userId
     */
    public function setControlToDelete(
        $orderId,
        $userId = null
    ) {
        $controls = $this->getRepo('Door\DoorAccess')->getAddAccessByOrder(
            $userId,
            $orderId
        );

        if (!empty($controls)) {
            foreach ($controls as $control) {
                $control->setAction(DoorAccessConstants::METHOD_DELETE);
                $control->isAccess() ? $control->setAccess(false) : $control->setAccess(true);
            }
        }
    }

    /**
     * @param $productId
     * @param $price
     * @param $orderNumber
     *
     * @return MembershipOrder
     */
    public function setMembershipOrder(
        $userId,
        $productId,
        $price,
        $orderNumber
    ) {
        $order = new MembershipOrder();
        $order->setUserId($userId);
        $order->setProductId($productId);
        $order->setPrice($price);
        $order->setOrderNumber($orderNumber);
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        return $order;
    }

    /**
     * @param int    $userId
     * @param string $price
     * @param string $orderNumber
     * @param string $channel
     */
    public function setTopUpOrder(
        $userId,
        $price,
        $orderNumber,
        $channel
    ) {
        $order = $this->getRepo('Order\TopUpOrder')->findOneByOrderNumber($orderNumber);

        if (is_null($order)) {
            $order = new TopUpOrder();
            $order->setUserId($userId);
            $order->setOrderNumber($orderNumber);
            $order->setPrice($price);
        }

        $this->storePayChannel(
            $order,
            $channel
        );

        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();
    }

    /**
     * @param $count
     * @param $now
     */
    public function setOrderCount(
        $count,
        $now
    ) {
        $counter = new OrderCount();
        $counter->setCount($count);
        $counter->setOrderDate($now);
        $em = $this->getDoctrine()->getManager();
        $em->persist($counter);
        $em->flush();
    }

    /**
     * @param string $orderNumber
     * @param string $chargeId
     * @param string $type
     *
     * @return OrderMap
     */
    public function createOrderMap(
        $orderNumber,
        $chargeId,
        $type
    ) {
        $maps = $this->getRepo('Order\OrderMap')->findByOrderNumber($orderNumber);

        if (!empty($maps)) {
            return;
        }

        $map = new OrderMap();
        $map->setType($type);
        $map->setOrderNumber($orderNumber);
        $map->setChargeId($chargeId);

        $em = $this->getDoctrine()->getManager();
        $em->persist($map);
        $em->flush();
    }

    /**
     * @param $letter
     *
     * @return string
     */
    public function getOrderNumber(
        $letter
    ) {
        $datetime = new \DateTime();
        $now = clone $datetime;
        $now->setTime(00, 00, 00);
        $date = round(microtime(true) * 1000);
        $counter = $this->getRepo('Order\OrderCount')->findOneBy(['orderDate' => $now]);
        if (is_null($counter)) {
            $count = 1;
            $this->setOrderCount($count, $now);
        } else {
            $count = $counter->getCount() + 1;
            $counter->setCount($count);
            $em = $this->getDoctrine()->getManager();
            $em->persist($counter);
            $em->flush();
        }

        $serverId = $this->getGlobal('server_order_id');
        $orderNumber = $letter."$date"."$count"."$serverId";

        return $orderNumber;
    }

    /**
     * @param $letter
     *
     * @return string
     */
    public function getOrderNumberForProductOrder(
        $letter,
        $orderCheck
    ) {
        $date = round(microtime(true) * 1000);
        $checkId = $orderCheck->getId();
        $serverId = $this->getGlobal('server_order_id');

        $orderNumber = $letter."$date"."$checkId"."$serverId";

        return $orderNumber;
    }

    /**
     * @param $type
     *
     * @return \DateTime
     */
    public function calculateEndDate(
        $type
    ) {
        $orderArray = $this->getRepo('Order\MembershipOrder')->findBy(
            ['userId' => $this->getUserId()],
            ['id' => 'DESC'],
            1
        );
        $order = $orderArray[0];
        if (empty($order)) {
            $startDate = new \DateTime();
        } else {
            $startDate = $order->getEndDate();
        }

        $endDate = clone $startDate;
        switch ($type) {
            case 'month':
                $endDate->modify('+ 1 month');
                break;
            case 'quarter':
                $endDate->modify('+ 3 month');
                break;
            case 'year':
                $endDate->modify('+ 1 year');
                break;
        }

        return $endDate;
    }

    /**
     * @param $order
     *
     * @return int
     */
    public function updateFoodOrderStatus(
        $order
    ) {
        $order->setStatus(FoodOrder::STATUS_PAID);
        $order->setPaymentDate(new \DateTime());
        $foodInfo = $order->getFoodInfo();
        $infoArrays = json_decode($foodInfo, true);
        foreach ($infoArrays as $infoArray) {
            if (array_key_exists('quantity', $infoArray) && array_key_exists('inventory', $infoArray)) {
                $food = $this->getRepo('Food\Food')->find($infoArray['id']);
                $food->setInventory($infoArray['inventory'] - $infoArray['quantity']);
            }
        }
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $order->getId();
    }

    /**
     * @param $orderNumber
     *
     * @return int
     */
    public function findAndSetFoodOrder(
        $orderNumber
    ) {
        $order = $this->getRepo('Food\FoodOrder')->findOneBy(
            ['orderNumber' => $orderNumber]
        );
        if (is_null($order)) {
            throw new NotFoundHttpException(self::ORDER_NOT_FOUND_MESSAGE);
        }

        // update order status
        return $this->updateFoodOrderStatus($order);
    }

    /**
     * @param ProductOrder $order
     */
    public function sendOrderEmail(
        $order
    ) {
        try {
//            $email = $order->getProduct()->getRoom()->getBuilding()->getEmail();
//            if (is_null($email)) {
//                return;
//            }
            $building = $order->getProduct()->getRoom()->getBuilding();

            $payChannel = $this->get('translator')->trans('product_order.channel.'.$order->getPayChannel());
            if (is_null($payChannel)) {
                return;
            }

            $orderStatus = $order->getStatus();
            if ($orderStatus == ProductOrder::STATUS_PAID) {
                $title = '新的订单';
                $txt =  '已付款';
            } elseif ($orderStatus == ProductOrder::STATUS_CANCELLED) {
                $title = '订单取消';
                $txt =  '已取消';
            } else {
                return;
            }

            $productInfo = json_decode($order->getProductInfo(), true);

            $status = $this->get('translator')->trans('product_order.status.'.$orderStatus);
            $roomType = $this->get('translator')->trans('room.type.'.$order->getProduct()->getRoom()->getType());
            $unitPrice = $this->get('translator')->trans('room.unit.'.$productInfo['unit_price']);

            $user = $this->getRepo('User\UserProfile')->find($order->getUserId());

            // send email
            if(!is_null($building->getEmail())) {
                $subject = '【展想创合】'.$title;
                $emails = explode(',',$building->getEmail());
                foreach ($emails as $email) {
                    $this->sendEmail($subject, $email, $this->before('@', $email),
                        'Emails/order_email_notification.html.twig',
                        array(
                            'title' => $title,
                            'order' => $order,
                            'product_info' => $productInfo,
                            'status' => $status,
                            'user' => $user,
                            'pay_channel' => $payChannel,
                            'room_type' => $roomType,
                            'unit_price' => $unitPrice,
                        )
                    );
                }
            }

            // send sms
            if(!is_null($building->getOrderRemindPhones())) {
                $orderRoom = $order->getProduct()->getRoom();
                $phoneInfo = $user->getPhone() ? $user->getPhone() : $user->getEmail();
                $username = $user->getName().'('.$phoneInfo.')';
                $time_action = $order->getCreationDate()->format('Y/m/d H:i');
                $orderNumber = $order->getOrderNumber();
                $product = $orderRoom->getCity()->getName().','.$orderRoom->getBuilding()->getName().','.$orderRoom->getNumber().','.$orderRoom->getName();
                $rent_time = $order->getStartDate()->format('Y/m/d H:i').' - '.$order->getEndDate()->format('Y/m/d H:i');
                $payment = $order->getDiscountPrice();

                $smsText = '【展想创合】您有一笔来自'.$username.'于'.$time_action.$txt.'的新订单:'.$orderNumber.'。订单商品为:'.$product.';租赁时间为:'.$rent_time.';付款金额为：￥'.$payment;

                $phones = explode(',',$building->getOrderRemindPhones());
                foreach ($phones as $phone) {
                    $this->send_sms($phone, $smsText);
                }
            }

        } catch (\Exception $e) {
            error_log('Send order email and sms went wrong!');
        }
    }
}
