<?php

namespace Sandbox\ApiBundle\Controller\Payment;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Constants\DoorAccessConstants;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Entity\Order\TopUpOrder;
use Sandbox\ApiBundle\Entity\Order\MembershipOrder;
use Sandbox\ApiBundle\Entity\Order\OrderCount;
use Sandbox\ApiBundle\Entity\Door\DoorAccess;
use Sandbox\ApiBundle\Entity\Order\OrderMap;
use Sandbox\ApiBundle\Entity\Food\FoodOrder;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Pingpp\Pingpp;
use Pingpp\Charge;
use Pingpp\Error\Base;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sandbox\ApiBundle\Traits\StringUtil;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;

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
    const NO_DOOR_CODE = 400019;
    const NO_DOOR_MESSAGE = 'Room Has No Doors';
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
    const PAYMENT_CHANNEL_ALIPAY_WAP = 'alipay_wap';
    const PAYMENT_CHANNEL_UPACP_WAP = 'upacp_wap';
    const PAYMENT_CHANNEL_ACCOUNT = 'account';
    const PAYMENT_CHANNEL_ALIPAY = 'alipay';
    const PAYMENT_CHANNEL_UPACP = 'upacp';
    const PAYMENT_CHANNEL_WECHAT = 'wx';
    const ORDER_REFUND = 'refund';

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
     * @param $chargeId
     * @param $channel
     *
     * @return ProductOrder
     */
    public function setProductOrder(
        $chargeId,
        $channel
    ) {
        $map = $this->getRepo('Order\OrderMap')->findOneBy(['chargeId' => $chargeId]);
        $this->throwNotFoundIfNull($map, self::NOT_FOUND_MESSAGE);

        $orderId = $map->getOrderId();

        $order = $this->getRepo('Order\ProductOrder')->find($orderId);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $order->setStatus(self::STATUS_PAID);
        $order->setPaymentDate(new \DateTime());
        $order->setModificationDate(new \DateTime());

        // store payment channel
        $this->storePayChannel(
            $order,
            $channel
        );

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // set door access
        $this->setDoorAccessForSingleOrder($order);

        return $order;
    }

    /**
     * @param ProductOrder $order
     */
    public function setDoorAccessForSingleOrder(
        $order
    ) {
        // send order email
        $this->sendOrderEmail($order);

        $userId = $order->getUserId();
        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        $base = $building->getServer();
        $roomId = $order->getProduct()->getRoom()->getId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findBy(['room' => $roomId]);

        if (empty($roomDoors)) {
            throw new BadRequestHttpException('no doors');
        }

        $em = $this->getDoctrine()->getManager();
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
        $order = new TopUpOrder();
        $order->setUserId($userId);
        $order->setOrderNumber($orderNumber);
        $order->setPrice($price);

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
     * @param $type
     * @param $id
     * @param $chargeId
     *
     * @return OrderMap
     */
    public function createOrderMap(
        $type,
        $id,
        $chargeId
    ) {
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
            $email = $order->getProduct()->getRoom()->getBuilding()->getEmail();
            if (is_null($email)) {
                return;
            }

            $payChannel = $this->get('translator')->trans('product_order.channel.'.$order->getPayChannel());
            if (is_null($payChannel)) {
                return;
            }

            $orderStatus = $order->getStatus();
            if ($orderStatus == ProductOrder::STATUS_PAID) {
                $title = '新的订单';
            } elseif ($orderStatus == ProductOrder::STATUS_CANCELLED) {
                $title = '订单取消';
            } else {
                return;
            }

            $productInfo = json_decode($order->getProductInfo(), true);

            $status = $this->get('translator')->trans('product_order.status.'.$orderStatus);
            $roomType = $this->get('translator')->trans('room.type.'.$order->getProduct()->getRoom()->getType());
            $unitPrice = $this->get('translator')->trans('room.unit.'.$productInfo['unit_price']);

            $user = $this->getRepo('User\UserProfile')->find($order->getUserId());

            // send email
            $subject = '【展想创合】'.$title;
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
        } catch (\Exception $e) {
            error_log('Send order email went wrong!');
        }
    }

    /**
     * @param $userId
     * @param $userArray
     *
     * @return mixed
     */
    public function getUserArrayIfAuthed(
        $base,
        $userId,
        $userArray
    ) {
        $userEntity = $this->getRepo('User\User')->find($userId);
        $result = $this->getCardNoByUser($userId);
        if (
            !is_null($result) &&
            $result['status'] === DoorController::STATUS_AUTHED &&
            !$userEntity->isBanned()
        ) {
            $this->setEmployeeCardForOneBuilding(
                $base,
                $userId,
                $result['card_no']
            );

            $empUser = ['empid' => $userId];
            array_push($userArray, $empUser);
        }

        return $userArray;
    }

    /**
     * @param $order
     */
    public function syncAccessByOrder(
        $base,
        $order
    ) {
        $orderId = $order->getId();
        $roomId = $order->getProduct()->getRoom()->getId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findByRoomId($roomId);
        if (empty($roomDoors)) {
            throw new NotFoundHttpException(self::NO_DOOR_MESSAGE);
        }

        // check if order cancelled
        if ($order->getStatus() == ProductOrder::STATUS_CANCELLED) {
            // cancel order
            $this->callRepealRoomOrderCommand(
                $base,
                $orderId
            );
        } else {
            // get add action controls
            $addControls = $this->getRepo('Door\DoorAccess')->getAllWithoutAccess(
                DoorAccessConstants::METHOD_ADD,
                $orderId
            );

            // get delete action controls
            $deleteControls = $this->getRepo('Door\DoorAccess')->getAllWithoutAccess(
                DoorAccessConstants::METHOD_DELETE,
                $orderId
            );

            if (!empty($addControls)) {
                $userArray = [];
                foreach ($addControls as $addControl) {
                    $userArray = $this->getUserArrayIfAuthed(
                        $base,
                        $addControl->getUserId(),
                        $userArray
                    );
                }

                // set room access
                if (!empty($userArray)) {
                    $this->callSetRoomOrderCommand(
                        $base,
                        $userArray,
                        $roomDoors,
                        $order
                    );
                }
            }

            if (!empty($deleteControls)) {
                $removeUserArray = [];
                foreach ($deleteControls as $deleteControl) {
                    $userId = $deleteControl->getUserId();
                    $result = $this->getCardNoByUser($userId);
                    if ($result['status'] !== DoorController::STATUS_UNAUTHED) {
                        $empUser = ['empid' => $userId];
                        array_push($removeUserArray, $empUser);
                    }
                }

                // remove room access
                if (!empty($removeUserArray)) {
                    $this->callRemoveFromOrderCommand(
                        $base,
                        $orderId,
                        $removeUserArray
                    );
                }
            }
        }
    }
}
