<?php

namespace Sandbox\ClientApiBundle\Controller\Order;

use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Order\InvitedPeople;
use Sandbox\ApiBundle\Entity\Room\Room;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Door\DoorAccess;
use Sandbox\ApiBundle\Form\Order\OrderType;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Rest controller for Client Orders.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientOrderController extends PaymentController
{
    const PAYMENT_SUBJECT = 'ROOM';
    const PAYMENT_BODY = 'ROOM ORDER';
    const PRODUCT_ORDER_LETTER_HEAD = 'P';

    /**
     * Get all orders for current user.
     *
     * @Get("/orders/my")
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    default=null,
     *    nullable=true,
     *    description="
     *        maximum allowed people
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Offset of page"
     * )
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getUserOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $status = $paramFetcher->get('status');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        if (!is_null($status)) {
            $orders = $this->getRepo('Order\ProductOrder')->findBy(
                [
                    'userId' => $userId,
                    'status' => $status,
                ],
                ['modificationDate' => 'DESC'],
                $limit,
                $offset
            );
        } else {
            $orders = $this->getRepo('Order\ProductOrder')->findBy(
                ['userId' => $userId],
                ['modificationDate' => 'DESC'],
                $limit,
                $offset
            );
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($orders);

        return $view;
    }

    /**
     * Create orders.
     *
     * @Post("/orders")
     *
     * @param Request $request
     *
     * @return View
     */
    public function createOrdersAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $order = new ProductOrder();

        $form = $this->createForm(new OrderType(), $order);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return $this->customErrorView(
                400,
                self::INVALID_FORM_CODE,
                self::INVALID_FORM_MESSAGE
            );
        }

        //check if product exists
        $productId = $order->getProductId();
        $product = $this->getRepo('Product\Product')->find($productId);
        if (is_null($product)) {
            return $this->customErrorView(
                400,
                self::PRODUCT_NOT_FOUND_CODE,
                self::PRODUCT_NOT_FOUND_MESSAGE
            );
        }
        $productStart = $product->getStartDate();
        $productEnd = $product->getEndDate();
        $now = new \DateTime();
        $type = $product->getRoom()->getType();
        $startDate = new \DateTime($order->getStartDate());
        if ($startDate < $productStart || $startDate > $productEnd || $product->getVisible() == false) {
            return $this->customErrorView(
                400,
                self::PRODUCT_NOT_AVAILABLE_CODE,
                self::PRODUCT_NOT_AVAILABLE_MESSAGE
            );
        }

        $period = $form['rent_period']->getData();
        $timeUnit = $form['time_unit']->getData();
        $basePrice = $product->getBasePrice();
        $calculatedPrice = $basePrice * $period;

        if ($order->getPrice() != $calculatedPrice) {
            return $this->customErrorView(
                400,
                self::PRICE_MISMATCH_CODE,
                self::PRICE_MISMATCH_MESSAGE
            );
        }

        if ($type === Room::TYPE_OFFICE && $order->getIsRenew()) {
            $myEnd = $now->modify('+ 7 days');
            $myOrder = $this->getRepo('Order\ProductOrder')->getRenewOrder(
                $userId,
                $productId,
                $myEnd
            );
            if (empty($myOrder)) {
                return $this->customErrorView(
                    400,
                    self::CAN_NOT_RENEW_CODE,
                    self::CAN_NOT_RENEW_MESSAGE
                );
            }
            $startDate = $myOrder[0]->getEndDate();
            $endDate = clone $startDate;
            $endDate->modify('+ 30 days');
        } else {
            $diff = $startDate->diff($now)->days;
            if ($diff > 7) {
                return $this->customErrorView(
                    400,
                    self::NOT_WITHIN_DATE_RANGE_CODE,
                    self::NOT_WITHIN_DATE_RANGE_MESSAGE
                );
            }
            $datePeriod = $period;
            if ($timeUnit === 'hour') {
                $datePeriod = $period * 60;
                $timeUnit = 'min';
            } elseif ($timeUnit === 'month') {
                $datePeriod = $period * 30;
                $timeUnit = 'days';
            }
            $endDate = clone $startDate;
            $endDate->modify('+'.$datePeriod.$timeUnit);
        }

        if ($type == Room::TYPE_OFFICE || $type == Room::TYPE_FIXED || $type == Room::TYPE_FLEXIBLE) {
            $nowDate = $now->format('Y-m-d');
            $startPeriod = $startDate->format('Y-m-d');
            if ($nowDate > $startPeriod) {
                return $this->customErrorView(
                    400,
                    self::WRONG_BOOKING_DATE_CODE,
                    self::WRONG_BOOKING_DATE_MESSAGE
                );
            }
            $endDate->modify('- 1 day');
            $endDate->setTime(23, 59, 59);
        } else {
            if ($now > $startDate) {
                return $this->customErrorView(
                    400,
                    self::WRONG_BOOKING_DATE_CODE,
                    self::WRONG_BOOKING_DATE_MESSAGE
                );
            }
            $startHour = $startDate->format('H:i:s');
            $endHour = $endDate->format('H:i:s');
            $roomId = $product->getRoomId();
            $meeting = $this->getRepo('Room\RoomMeeting')->findOneBy(['room' => $roomId]);
            if (!is_null($meeting)) {
                $allowedStart = $meeting->getStartHour();
                $allowedStart = $allowedStart->format('H:i:s');
                $allowedEnd = $meeting->getEndHour();
                $allowedEnd = $allowedEnd->format('H:i:s');
                if ($startHour < $allowedStart || $endHour > $allowedEnd) {
                    return $this->customErrorView(
                        400,
                        self::ROOM_NOT_OPEN_CODE,
                        self::ROOM_NOT_OPEN_MESSAGE
                    );
                }
            }
        }

        //check if flexible room is full
        if ($type == Room::TYPE_FLEXIBLE) {
            $allowedPeople = $product->getRoom()->getAllowedPeople();
            $orderCount = $this->getRepo('Order\ProductOrder')->checkFlexibleForClient(
                $productId,
                $startDate,
                $endDate
            );

            if ($allowedPeople <= (int) $orderCount) {
                return $this->customErrorView(
                    400,
                    self::FLEXIBLE_ROOM_FULL_CODE,
                    self::FLEXIBLE_ROOM_FULL_MESSAGE
                );
            }
        } else {
            $checkOrder = $this->getRepo('Order\ProductOrder')->checkProductForClient(
                $productId,
                $startDate,
                $endDate
            );
            if (!empty($checkOrder) && !is_null($checkOrder)) {
                return $this->customErrorView(
                    400,
                    self::ORDER_CONFLICT_CODE,
                    self::ORDER_CONFLICT_MESSAGE
                );
            }
        }

        $ruleId = $form['rule_id']->getData();
        if (!is_null($ruleId) && !empty($ruleId)) {
            $order->setRuleId($ruleId);
            $discountPrice = $order->getDiscountPrice();
            $isRenew = $order->getIsRenew();
            $result = $this->getDiscountPriceForOrder(
                $ruleId,
                $productId,
                $period,
                $startDate,
                $endDate,
                $isRenew
            );

            if (array_key_exists('bind_product_id', $result['rule'])) {
                $order->setMembershipBindId($result['rule']['bind_product_id']);
            }

            if ($discountPrice != $result['discount_price']) {
                return $this->customErrorView(
                    400,
                    self::DISCOUNT_PRICE_MISMATCH_CODE,
                    self::DISCOUNT_PRICE_MISMATCH_MESSAGE
                );
            }

            if (array_key_exists('rule_name', $result['rule'])) {
                $order->setRuleName($result['rule']['rule_name']);
            }

            if (array_key_exists('rule_description', $result['rule'])) {
                $order->setRuleDescription($result['rule']['rule_description']);
            }
        }

        $orderNumber = $this->getOrderNumber(self::PRODUCT_ORDER_LETTER_HEAD);

        $order->setOrderNumber($orderNumber);
        $order->setProduct($product);
        $order->setStartDate($startDate);
        $order->setEndDate($endDate);
        $order->setUserId($userId);
        $order->setLocation('location');
        $order->setStatus('unpaid');
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        $view = new View();
        $view->setData(
            ['order_id' => $order->getId()]
        );

        return $view;
    }

    /**
     * @param $order
     *
     * @return View
     */
    private function payByAccount($order)
    {
        $price = $order->getDiscountPrice();
        $orderNumber = $order->getOrderNumber();
        $balance = $this->postBalanceChange(
            $order->getUserId(),
            (-1) * $price,
            $orderNumber,
            self::PAYMENT_CHANNEL_ACCOUNT
        );
        if (is_null($balance)) {
            return $this->customErrorView(
                400,
                self::INSUFFICIENT_FUNDS_CODE,
                self::INSUFFICIENT_FUNDS_MESSAGE
            );
        }

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
        if (empty($roomDoors)) {
            throw new BadRequestHttpException('no doors');
        }

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
                $access->setOrderId($order->getId());
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

        $this->get('door_service')->setTimePeriod(
            $updatedDoors,
            $base,
            $globals
        );

        $cardNo = $this->getCardNoIfUserAuthorized();

        if (is_null($cardNo)) {
            return;
        }

        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($order->getUserId());
        $userName = $userProfile->getName();
        $doors = $this->getRepo('Door\DoorAccess')->getDoorsByBuilding(
            $order->getUserId(),
            $buildingId
        );
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

        $view = new View();

        return $view->setData(
            array(
                'balance' => $balance,
                'channel' => self::PAYMENT_CHANNEL_ACCOUNT,
            )
        );
    }

    /**
     * @Post("/orders/{id}/pay")
     *
     * @param Request $request
     * @param $id
     */
    public function payAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }
        if ($order->getStatus() !== 'unpaid') {
            return $this->customErrorView(
                400,
                self::WRONG_PAYMENT_STATUS_CODE,
                self::WRONG_PAYMENT_STATUS_MESSAGE
            );
        }
        $requestContent = json_decode($request->getContent(), true);
        $channel = $requestContent['channel'];

        if (
            $channel !== self::PAYMENT_CHANNEL_ALIPAY_WAP &&
            $channel !== self::PAYMENT_CHANNEL_UPACP_WAP &&
            $channel !== self::PAYMENT_CHANNEL_ACCOUNT &&
            $channel !== self::PAYMENT_CHANNEL_ALIPAY
        ) {
            return $this->customErrorView(
                400,
                self::WRONG_CHANNEL_CODE,
                self::WRONG_CHANNEL_MESSAGE
            );
        }
        if ($channel === self::PAYMENT_CHANNEL_ACCOUNT) {
            return $this->payByAccount($order);
        }

        $orderNumber = $order->getOrderNumber();
        $charge = $this->payForOrder(
            $orderNumber,
            $order->getDiscountPrice(),
            $channel,
            self::PAYMENT_SUBJECT,
            self::PAYMENT_BODY
        );
        $charge = json_decode($charge, true);
        $chargeId = $charge['id'];

        $this->createOrderMap('product', $order->getId(), $chargeId);

        return new View($charge);
    }

    /**
     * @Post("/orders/{id}/refund")
     *
     * @param Request $request
     * @param $id
     */
    public function refundAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }
        $now = new \DateTime();
        if ($order->getStatus() !== 'paid' || $order->getStartDate() <= $now) {
            return $this->customErrorView(
                400,
                self::WRONG_PAYMENT_STATUS_CODE,
                self::WRONG_PAYMENT_STATUS_MESSAGE
            );
        }
        $price = $order->getDiscountPrice();
        $userId = $order->getUserId();
        $balance = $this->postBalanceChange(
            $userId,
            $price,
            $id,
            self::PAYMENT_CHANNEL_ACCOUNT
        );
        if (!is_null($balance)) {
            $order->setStatus('cancelled');
            $order->setCancelledDate($now);
            $order->setModificationDate($now);
            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();

            $peopleArray = [];
            array_push($peopleArray, $userId);
            $appointedId = $order->getAppointed();
            if (!is_null($appointedId)) {
                array_push($peopleArray, $appointedId);
            }

            $people = $this->getRepo('Order\InvitedPeople')->findBy(
                [
                    'orderId' => $id,
                ]
            );
            if (!empty($people)) {
                foreach ($people as $user) {
                    $userId = $user->getUserId();
                    array_push($peopleArray, $userId);
                }
            }
            $globals = $this->getGlobals();
            $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
            $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
            $base = $building->getServer();
            foreach ($peopleArray as $user) {
                $controls = $this->getRepo('Door\DoorAccess')->findBy(
                    [
                        'userId' => $user,
                        'orderId' => $order->getId(),
                    ]
                );
                foreach ($controls as $control) {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($control);
                    $em->flush();
                }

                $cardNo = $this->getCardNoByUser($user);
                if (!is_null($cardNo)) {
                    $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($user);
                    $userName = $userProfile->getName();
                    $doors = $this->getRepo('Door\DoorAccess')->getDoorsByBuilding(
                        $user,
                        $buildingId
                    );
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
                            $user,
                            $userName,
                            $cardNo,
                            $doorArray,
                            DoorController::METHOD_ADD,
                            $globals
                        );
                    } else {
                        $this->get('door_service')->cardPermission(
                            $base,
                            $user,
                            $userName,
                            $cardNo,
                            [],
                            DoorController::METHOD_DELETE,
                            $globals
                        );
                    }
                }
            }
        }
        $view = new View();

        return $view->setData(
            array(
                'balance' => $balance,
                'channel' => self::PAYMENT_CHANNEL_ACCOUNT,
            )
        );
    }

    /**
     * @Post("/orders/{id}/people")
     *
     * @param Request $request
     * @param $id
     */
    public function addPeopleAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }
        $status = $order->getStatus();
        $endDate = $order->getEndDate();
        $now = new \DateTime();
        if ($status !== 'paid' && $status !== 'completed' || $now >= $endDate) {
            return $this->customErrorView(
                400,
                self::WRONG_ORDER_STATUS_CODE,
                self::WRONG_ORDER_STATUS_MESSAGE
            );
        }
        $people = $this->getRepo('Order\InvitedPeople')->findBy(
            ['orderId' => $id]
        );
        if (!is_null($people) && !empty($people)) {
            $this->deletePeople($order, $people);
        }

        $globals = $this->getGlobals();
        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        $base = $building->getServer();
        $roomId = $order->getProduct()->getRoom()->getId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findBy(['room' => $roomId]);
        if (empty($roomDoors)) {
            return $this->customErrorView(
                400,
                self::NO_DOOR_CODE,
                self::NO_DOOR_MESSAGE
            );
        }
        $users = json_decode($request->getContent(), true);
        foreach ($users as $user) {
            $people = new InvitedPeople();
            $people->setOrderId($order);
            $people->setUserId($user['user_id']);
            $people->setCreationDate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($people);
            $em->flush();

            foreach ($roomDoors as $roomDoor) {
                $doorAccess = $this->getRepo('Door\DoorAccess')->findOneBy(
                    [
                        'userId' => $user['user_id'],
                        'orderId' => $order->getId(),
                        'buildingId' => $buildingId,
                        'doorId' => $roomDoor->getDoorControlId(),
                    ]
                );
                if (is_null($doorAccess)) {
                    $doorOfOrder = $this->getRepo('Door\DoorAccess')->findOneBy(
                        ['orderId' => $order->getId()]
                    );

                    $access = new DoorAccess();
                    $access->setBuildingId($buildingId);
                    $access->setDoorId($roomDoor->getDoorControlId());
                    $access->setUserId($user['user_id']);
                    $access->setTimeId($doorOfOrder->getTimeId());
                    $access->setRoomId($roomId);
                    $access->setOrderId($order->getId());
                    $access->setStartDate($order->getStartDate());
                    $access->setEndDate($order->getEndDate());

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($access);
                    $em->flush();
                }
            }

            $cardNo = $this->getCardNoByUser($user['user_id']);
            if (!is_null($cardNo)) {
                $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($user['user_id']);
                $userName = $userProfile->getName();

                $doors = $this->getRepo('Door\DoorAccess')->getDoorsByBuilding(
                    $user['user_id'],
                    $buildingId
                );

                $doorArray = [];
                foreach ($doors as $door) {
                    $doorId = $door->getDoorId();
                    $timeId = $door->getTimeId();
                    $door = ['doorid' => $doorId, 'timeperiodid' => "$timeId"];

                    array_push($doorArray, $door);
                }

                $this->get('door_service')->cardPermission(
                    $base,
                    $user['user_id'],
                    $userName,
                    $cardNo,
                    $doorArray,
                    DoorController::METHOD_ADD,
                    $globals
                );
            }
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @param ParamFetcherInterface $paramFetcher
     */
    private function deletePeople(
        $order,
        $people
    ) {
        $globals = $this->getGlobals();
        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        $base = $building->getServer();
        foreach ($people as $person) {
            $userId = $person->getUserId();

            $em = $this->getDoctrine()->getManager();
            $em->remove($person);
            $em->flush();

            $controls = $this->getRepo('Door\DoorAccess')->findBy(
                [
                    'userId' => $userId,
                    'orderId' => $order->getId(),
                ]
            );

            foreach ($controls as $control) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($control);
                $em->flush();
            }

            $cardNo = $this->getCardNoByUser($userId);
            if (!is_null($cardNo)) {
                $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
                $userName = $userProfile->getName();
                $doors = $this->getRepo('Door\DoorAccess')->getDoorsByBuilding(
                    $userId,
                    $buildingId
                );

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
                        $userId,
                        $userName,
                        $cardNo,
                        $doorArray,
                        DoorController::METHOD_ADD,
                        $globals
                    );
                } else {
                    $this->get('door_service')->cardPermission(
                        $base,
                        $userId,
                        $userName,
                        $cardNo,
                        [],
                        DoorController::METHOD_DELETE,
                        $globals
                    );
                }
            }
        }
    }

    /**
     * @Get("/orders/{id}/invited")
     *
     * @param Request $request
     * @param $id
     */
    public function getInvitedPeopleAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }
        $people = $this->getRepo('Order\InvitedPeople')->findBy(
            ['orderId' => $id]
        );

        $users = [];
        foreach ($people as $person) {
            $userId = $person->getUserId();
            $user = $this->getRepo('User\UserProfile')->findOneBy(['userId' => $userId]);
            array_push($users, $user);
        }

        return new View($users);
    }

    /**
     * @Post("/orders/{id}/person/appoint")
     *
     * @param Request $request
     * @param $id
     */
    public function appointPersonAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }
        $status = $order->getStatus();
        $endDate = $order->getEndDate();
        $now = new \DateTime();
        if ($status !== 'paid' && $status !== 'completed' || $now >= $endDate) {
            return $this->customErrorView(
                400,
                self::WRONG_ORDER_STATUS_CODE,
                self::WRONG_ORDER_STATUS_MESSAGE
            );
        }
        $requestContent = json_decode($request->getContent(), true);
        $user = $requestContent['user_id'];

        $appointedProfile = $this->getRepo('User\UserProfile')->findOneByUserId($user);
        if (is_null($user) || empty($user) || is_null($appointedProfile)) {
            return $this->customErrorView(
                400,
                self::NO_APPOINTED_PERSON_CODE,
                self::NO_APPOINTED_PERSON_CODE_MESSAGE
            );
        }
        $appointedName = $appointedProfile->getName();
        $order->setAppointed($user);
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
        if (empty($roomDoors)) {
            return $this->customErrorView(
                400,
                self::NO_DOOR_CODE,
                self::NO_DOOR_MESSAGE
            );
        }
        foreach ($roomDoors as $roomDoor) {
            $doorAccess = $this->getRepo('Door\DoorAccess')->findOneBy(
                [
                    'userId' => $user,
                    'orderId' => $order->getId(),
                    'buildingId' => $buildingId,
                    'doorId' => $roomDoor->getDoorControlId(),
                ]
            );
            if (is_null($doorAccess)) {
                $doorOfOrder = $this->getRepo('Door\DoorAccess')->findOneBy(
                    ['orderId' => $order->getId()]
                );

                $access = new DoorAccess();
                $access->setBuildingId($buildingId);
                $access->setDoorId($roomDoor->getDoorControlId());
                $access->setUserId($user);
                $access->setTimeId($doorOfOrder->getTimeId());
                $access->setRoomId($roomId);
                $access->setOrderId($order->getId());
                $access->setStartDate($order->getStartDate());
                $access->setEndDate($order->getEndDate());

                $em = $this->getDoctrine()->getManager();
                $em->persist($access);
                $em->flush();
            }
        }

        $controls = $this->getRepo('Door\DoorAccess')->findBy(
            [
                'userId' => $order->getUserId(),
                'orderId' => $order->getId(),
            ]
        );

        foreach ($controls as $control) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($control);
            $em->flush();
        }

        $appointedCardNo = $this->getCardNoByUser($user);
        if (!is_null($appointedCardNo)) {
            $doors = $this->getRepo('Door\DoorAccess')->getDoorsByBuilding(
                $user,
                $buildingId
            );

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
                    $user,
                    $appointedName,
                    $appointedCardNo,
                    $doorArray,
                    DoorController::METHOD_ADD,
                    $globals
                );
            }
        }

        $userCardNo = $this->getCardNoIfUserAuthorized();

        if (!is_null($userCardNo)) {
            $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($order->getUserId());
            $userName = $userProfile->getName();
            $doors = $this->getRepo('Door\DoorAccess')->getDoorsByBuilding(
                $order->getUserId(),
                $buildingId
            );
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
                    $userCardNo,
                    $doorArray,
                    DoorController::METHOD_ADD,
                    $globals
                );
            } else {
                $this->get('door_service')->cardPermission(
                    $base,
                    $order->getUserId(),
                    $userName,
                    $userCardNo,
                    [],
                    DoorController::METHOD_DELETE,
                    $globals
                );
            }
        }
    }

    /**
     * @Get("/orders/number/{orderNumber}")
     *
     * @param Request $request
     * @param int     $orderNumber
     *
     * @return View
     */
    public function getOrderByOrderNumberAction(
        Request $request,
        $orderNumber
    ) {
        $order = $this->getRepo('Order\ProductOrder')->findOneBy(
            ['orderNumber' => $orderNumber]
        );
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }
        $view = $this->getOrderDetail($order);

        return $view;
    }

    /**
     * @Get("/orders/{id}")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getOneOrderAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }
        $view = $this->getOrderDetail($order);

        return $view;
    }

    /**
     * @param $order
     *
     * @return View
     */
    private function getOrderDetail(
        $order
    ) {
        $appointed = $order->getAppointed();
        $appointedPerson = [];
        if (!is_null($appointed) && !empty($appointed)) {
            $appointedPerson = $this->getRepo('User\UserProfile')->findOneBy(['userId' => $appointed]);
        }

        $now = new \DateTime();
        $userId = $order->getUserId();
        $type = $order->getProduct()->getRoom()->getType();
        $productId = $order->getProductId();
        $status = $order->getStatus();
        $startDate = $order->getStartDate();
        $renewButton = false;
        $minutes = 0;
        $seconds = 0;
        if ($type == Room::TYPE_OFFICE && $status == 'completed') {
            $renewOrder = $this->getRepo('Order\ProductOrder')->getAlreadyRenewedOrder($userId, $productId);
            if (is_null($renewOrder) || empty($renewOrder)) {
                $endDate = $order->getEndDate();
                $days = $endDate->diff($now)->days;
                if ($days > 7 && $now >= $startDate) {
                    $renewButton = true;
                }
            }
        }

        if ($status == 'unpaid') {
            $creationDate = $order->getCreationDate();
            $remainingTime = $now->diff($creationDate);
            $minutes = $remainingTime->i;
            $seconds = $remainingTime->s;
            $minutes = 14 - $minutes;
            $seconds = 60 - $seconds;
            if ($minutes < 0) {
                $minutes = 0;
                $seconds = 0;
                $order->setStatus('cancelled');
                $order->setCancelledDate($now);
                $order->setModificationDate($now);
                $em = $this->getDoctrine()->getManager();
                $em->persist($order);
                $em->flush();
            }
        } elseif ($status == 'paid') {
            if ($now >= $startDate) {
                $order->setStatus('completed');
                $order->setModificationDate($now);
                $em = $this->getDoctrine()->getManager();
                $em->persist($order);
                $em->flush();

                if (!is_null($order->getMembershipBindId())) {
                    $this->postAccountUpgrade(
                        $userId,
                        $order->getMembershipBindId(),
                        $order->getOrderNumber()
                    );
                }

                $amount = $this->postConsumeBalance(
                    $userId,
                    $order->getDiscountPrice(),
                    $order->getOrderNumber()
                );
            }
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData(
            [
                'renewButton' => $renewButton,
                'order' => $order,
                'appointedPerson' => $appointedPerson,
                'remainingMinutes' => $minutes,
                'remainingSeconds' => $seconds,
            ]
        );

        return $view;
    }
}
