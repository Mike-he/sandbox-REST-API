<?php

namespace Sandbox\ApiBundle\Controller\Payment;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Entity\Order\TopUpOrder;
use Sandbox\ApiBundle\Entity\Order\MembershipOrder;
use Sandbox\ApiBundle\Entity\Order\OrderCount;
use Sandbox\ApiBundle\Entity\Door\DoorAccess;
use Sandbox\ApiBundle\Entity\Order\OrderMap;
use Pingpp\Pingpp;
use Pingpp\Charge;
use Pingpp\Error\Base;

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
class PaymentController extends SandboxRestController
{
    const STATUS_PAID = 'paid';
    const INSUFFICIENT_FUNDS_CODE = 400001;
    const INSUFFICIENT_FUNDS_MESSAGE = 'Insufficient funds in account balance - 余额不足';
    const SYSTEM_ERROR_CODE = 500001;
    const SYSTEM_ERROR_MESSAGE = 'System error - 系统出错';
    const INVALID_FORM_CODE = 400002;
    const INVALID_FORM_MESSAGE = 'Invalid Form';
    const PRODUCT_NOT_FOUND_CODE = 400003;
    const PRODUCT_NOT_FOUND_MESSAGE = 'Product Does Not Exist';
    const ORDER_CONFLICT_CODE = 400004;
    const ORDER_CONFLICT_MESSAGE = 'Order Conflict';
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

    /**
     * @param $order
     * @param $channel
     *
     * @return Charge
     */
    public function payForOrder(
        $orderNumber,
        $price,
        $channel,
        $subject,
        $body
    ) {
        $extra = [];
        switch ($channel) {
            case 'alipay_wap':
                $extra = array(
                    'success_url' => 'http://www.yourdomain.com/success',
                    'cancel_url' => 'http://www.yourdomain.com/cancel',
                );
                break;
            case 'upacp_wap':
                $extra = array(
                    'result_url' => 'http://www.yourdomain.com/result?code=',
                );
                break;
        }

        $keyGlobal = $this->get('twig')->getGlobals();
        $key = $keyGlobal['pingpp_test_key'];
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
            echo($e->getHttpBody());
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
        $key = $keyGlobal['pingpp_test_key'];
        Pingpp::setApiKey($key);
        try {
            $ch = Charge::retrieve($chargeId);

            return $ch;
        } catch (Base $e) {
            header('Status: '.$e->getHttpStatus());
            echo($e->getHttpBody());
        }
    }

    /**
     * @param $data
     */
    public function setProductOrder(
        $data
    ) {
        $chargeId = $data['data']['object']['id'];
        $map = $this->getRepo('Order\OrderMap')->findOneBy(['chargeId' => $chargeId]);
        $orderId = $map->getOrderId();
        $order = $this->getRepo('Order\ProductOrder')->find($orderId);
        $order->setStatus(self::STATUS_PAID);
        $order->setPaymentDate(new \DateTime());
        $order->setModificationDate(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        $globals = $this->getGlobals();
        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        $base = $building->getServer();
        $roomId = $order->getProduct()->getRoom()->getId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findBy(['room' => $roomId]);
        $myDoors = $this->getRepo('Door\DoorAccess')->getAccessByRoom(
            $order->getUserId(),
            $buildingId,
            $roomId
        );

        foreach ($roomDoors as $roomDoor) {
            $doorAccess = $this->getRepo('Door\DoorAccess')->findOneBy(
                [
                    'userId' => $order->getUserId(),
                    'orderId' => $order->getId(),
                    'buildingId' => $buildingId,
                    'doorId' => $roomDoor->getDoorControlId(),
                ]
            );
            if (is_null($doorAccess)) {
                $access = new DoorAccess();
                $access->setBuildingId($buildingId);
                $access->setDoorId($roomDoor->getDoorControlId());
                $access->setUserId($order->getUserId());
                $access->setRoomId($roomId);
                $access->setOrderId($orderId);
                $access->setStartDate($order->getStartDate());
                $access->setEndDate($order->getEndDate());
                if (empty($myDoors)) {
                    $timeId = $order->getId();
                } else {
                    $timeId = $myDoors[0]->getTimeId();
                }
                $access->setTimeId($timeId);

                $em = $this->getDoctrine()->getManager();
                $em->persist($access);
                $em->flush();
            }
        }

        $updatedDoors = $this->getRepo('Door\DoorAccess')->getAccessByRoom(
            $order->getUserId(),
            $buildingId,
            $roomId
        );

        $this->get('door_service')->setTimePeriod($updatedDoors, $base, $globals);

        $cardNo = $this->getCardNoIfUserAuthorized();
        if (is_null($cardNo)) {
            return;
        }

        $doors = $this->getRepo('Door\DoorAccess')->getDoorsByBuilding(
            $order->getUserId(),
            $buildingId
        );

        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($order->getUserId());
        $userName = $userProfile->getName();

        if (!is_null($doors) && !empty($doors)) {
            $doorArray = [];
            foreach ($doors as $door) {
                $doorId = $door->getDoorId();
                $timeId = $door->getTimeId();
                $door = ['doorid' => $doorId, 'timeperiodid' => "$timeId"];

                array_push($doorArray, $door);
            }

            $this->get('door_service')->cardPermission(
                $base,
                $order->getUserId(),
                $userName,
                $cardNo,
                $doorArray,
                DoorController::METHOD_ADD,
                $globals
            );
        }

        return $order;
    }

    /**
     * @param $productId
     * @param $price
     * @param $orderNumber
     *
     * @return MembershipOrder
     */
    public function setMembershipOrder(
        $productId,
        $price,
        $orderNumber
    ) {
        $userId = $this->getUserid();
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
     * @param $price
     * @param $orderNumber
     *
     * @return TopUpOrder
     */
    public function setTopUpOrder(
        $price,
        $orderNumber
    ) {
        $userId = $this->getUserid();

        $order = new TopUpOrder();
        $order->setUserId($userId);
        $order->setOrderNumber($orderNumber);
        $order->setPrice($price);
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        return $order;
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
     * @param $type
     * @param $productId
     * @param $chargeId
     *
     * @return OrderMap
     */
    public function createOrderMap($type, $id, $chargeId)
    {
        $map = new OrderMap();
        $map->setType($type);
        $map->setOrderId($id);
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
        $date = $datetime->format('Ymdhis');
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
        $orderNumber = $letter.$date.$count;

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
            ['userId' => $this->getUserid()],
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
}
