<?php

namespace Sandbox\ClientApiBundle\Controller\Order;

use Elastica\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Order\InvitedPeople;
use Sandbox\ApiBundle\Entity\Order\ProductOrderCheck;
use Sandbox\ApiBundle\Entity\Order\ProductOrderRecord;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Form\Order\OrderType;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Traits\ProductOrderNotification;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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
    use ProductOrderNotification;
    const PAYMENT_SUBJECT = 'SANDBOX3-预定房间';
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

        if (!is_null($status) && !empty($status)) {
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
     * Get user's current available rooms.
     *
     * @Get("/orders/current")
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
     * @Annotations\QueryParam(
     *    name="search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getUserCurrentRoomsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $search = $paramFetcher->get('search');

        $orders = $this->getRepo('Order\ProductOrder')->getUserCurrentOrders(
            $userId,
            $limit,
            $offset,
            $search
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['current_order']));
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
        $em = $this->getDoctrine()->getManager();
        $orderCheck = null;

        try {
            $userId = $this->getUserId();
            $user = $this->getRepo('User\User')->find($userId);
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

            if (
                $now < $productStart ||
                $now > $productEnd ||
                $startDate < $productStart ||
                $startDate > $productEnd ||
                $product->getVisible() == false
            ) {
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
                $timeModify = $this->getGlobal('time_for_half_hour_early');
                $halfHour = clone $now;
                $halfHour->modify($timeModify);

                // check to allow ordering half an hour early
                if ($halfHour > $startDate) {
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

            // check if it's same order from the same user
            // return orderId if so
            if ($type !== Room::TYPE_FLEXIBLE) {
                $sameOrder = $this->getRepo('Order\ProductOrder')->getOrderFromSameUser(
                    $productId,
                    $userId,
                    $startDate,
                    $endDate
                );

                if (!is_null($sameOrder)) {
                    return new View(
                        ['order_id' => $sameOrder->getId()]
                    );
                }
            }

            // check for duplicate orders
            $allowedPeople = $product->getRoom()->getAllowedPeople();
            $orderCheck = $this->orderDuplicationCheck(
                $em,
                $type,
                $allowedPeople,
                $productId,
                $startDate,
                $endDate
            );

            // check for discount rule and price
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

            $orderNumber = $this->getOrderNumberForProductOrder(
                self::PRODUCT_ORDER_LETTER_HEAD,
                $orderCheck
            );
            $productInfo = $this->storeRoomInfo($product);

            // set product order
            $order->setOrderNumber($orderNumber);
            $order->setProduct($product);
            $order->setStartDate($startDate);
            $order->setEndDate($endDate);
            $order->setUser($user);
            $order->setLocation('location');
            $order->setStatus('unpaid');
            $order->setProductInfo($productInfo);
            $em->persist($order);

            // store order record
            $room = $this->getRepo('Room\Room')->find($product->getRoomId());
            $roomRecord = new ProductOrderRecord();
            $roomRecord->setOrder($order);
            $roomRecord->setCityId($room->getCityId());
            $roomRecord->setBuildingId($room->getBuildingId());
            $roomRecord->setRoomType($room->getType());
            $em->remove($orderCheck);
            $em->persist($roomRecord);
            $em->flush();

            $view = new View();
            $view->setData(
                ['order_id' => $order->getId()]
            );

            return $view;
        } catch (\Exception $exception) {
            if (!is_null($orderCheck)) {
                $em->remove($orderCheck);
                $em->flush();
            }

            throw $exception;
        }
    }

    /**
     * @param $product
     *
     * @return string
     */
    public function storeRoomInfo(
        $product
    ) {
        $room = $this->getRepo('Room\Room')->find($product->getRoomId());
        $city = $this->getRepo('Room\RoomCity')->find($room->getCityId());
        $building = $this->getRepo('Room\RoomBuilding')->find($room->getBuildingId());
        $floor = $this->getRepo('Room\RoomFloor')->find($room->getFloorId());
        $supplies = $this->getRepo('Room\RoomSupplies')->findBy(['room' => $room->getId()]);
        $meeting = $this->getRepo('Room\RoomMeeting')->findOneBy(['room' => $room->getId()]);
        $bindings = $this->getRepo('Room\RoomAttachmentBinding')->findBy(
            ['room' => $room->getId()],
            ['id' => 'ASC']
        );

        $supplyArray = [];
        $meetingArray = [];
        $attachmentArray = [];
        if (!is_null($supplies) && !empty($supplies)) {
            foreach ($supplies as $supply) {
                $eachSupply = [
                    'supply' => [
                        'id' => $supply->getSupply()->getId(),
                        'name' => $supply->getSupply()->getName(),
                    ],
                    'quantity' => $supply->getQuantity(),
                ];
                array_push($supplyArray, $eachSupply);
            }
        }
        if (!is_null($meeting)) {
            $meetingArray = [
                'id' => $meeting->getId(),
                'start_hour' => $meeting->getStartHour(),
                'end_hour' => $meeting->getEndHour(),
            ];
        }
        if (!is_null($bindings) && !empty($bindings)) {
            foreach ($bindings as $binding) {
                $attachment = $binding->getAttachmentId();
                $eachAttachment = [
                    'attachment_id' => [
                        'id' => $attachment->getId(),
                        'content' => $attachment->getContent(),
                        'attachment_type' => $attachment->getAttachmentType(),
                        'filename' => $attachment->getFilename(),
                        'preview' => $attachment->getPreview(),
                        'size' => $attachment->getSize(),
                    ],
                ];
                array_push($attachmentArray, $eachAttachment);
            }
        }

        $productInfo = [
            'id' => $product->getId(),
            'description' => $product->getDescription(),
            'base_price' => $product->getBasePrice(),
            'unit_price' => $product->getUnitPrice(),
            'renewable' => $product->getRenewable(),
            'start_date' => $product->getStartDate(),
            'end_date' => $product->getEndDate(),
            'room' => [
                'id' => $room->getId(),
                'name' => $room->getName(),
                'city' => [
                    'id' => $city->getId(),
                    'name' => $city->getName(),
                ],
                'building' => [
                    'id' => $building->getId(),
                    'name' => $building->getName(),
                    'address' => $building->getAddress(),
                    'lat' => $building->getLat(),
                    'lng' => $building->getLng(),
                ],
                'floor' => [
                    'id' => $floor->getId(),
                    'floor_number' => $floor->getFloorNumber(),
                ],
                'number' => $room->getNumber(),
                'area' => $room->getArea(),
                'type' => $room->getType(),
                'allowed_people' => $room->getAllowedPeople(),
                'office_supplies' => $supplyArray,
                'meeting' => $meetingArray,
                'attachment' => $attachmentArray,
            ],
            'seat_number' => $product->getSeatNumber(),
        ];

        return json_encode($productInfo);
    }

    /**
     * @param $order
     *
     * @return View
     */
    private function payByAccount(
        $order,
        $channel
    ) {
        $price = $order->getDiscountPrice();
        $orderNumber = $order->getOrderNumber();
        $balance = $this->postBalanceChange(
            $order->getUserId(),
            (-1) * $price,
            $orderNumber,
            self::PAYMENT_CHANNEL_ACCOUNT,
            $price
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

        // store payment channel
        $this->storePayChannel(
            $order,
            $channel
        );

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // set door access
        $this->setDoorAccessForSingleOrder($order);

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

        // check if request user is the same as order user
        $this->throwAccessDeniedIfNotSameUser($order->getUserId());

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
            $channel !== self::PAYMENT_CHANNEL_UPACP &&
            $channel !== self::PAYMENT_CHANNEL_UPACP_WAP &&
            $channel !== self::PAYMENT_CHANNEL_ACCOUNT &&
            $channel !== self::PAYMENT_CHANNEL_WECHAT &&
            $channel !== self::PAYMENT_CHANNEL_ALIPAY
        ) {
            return $this->customErrorView(
                400,
                self::WRONG_CHANNEL_CODE,
                self::WRONG_CHANNEL_MESSAGE
            );
        }

        if ($channel === self::PAYMENT_CHANNEL_ACCOUNT) {
            return $this->payByAccount(
                $order,
                $channel
            );
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

        // check if request user is the same as order user
        $this->throwAccessDeniedIfNotSameUser($order->getUserId());

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
            $order->getOrderNumber(),
            self::PAYMENT_CHANNEL_ACCOUNT,
            0,
            self::ORDER_REFUND
        );
        if (!is_null($balance)) {
            $this->removeAccessByOrder($order);
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

        // check if request user is the same as order user
        $this->throwAccessDeniedIfNotSameUser($order->getUserId());

        $status = $order->getStatus();
        $endDate = $order->getEndDate();
        $now = new \DateTime();
        if (
            $status !== ProductOrder::STATUS_PAID &&
            $status !== ProductOrder::STATUS_COMPLETED ||
            $now >= $endDate
        ) {
            return $this->customErrorView(
                400,
                self::WRONG_ORDER_STATUS_CODE,
                self::WRONG_ORDER_STATUS_MESSAGE
            );
        }

        $people = json_decode($request->getContent(), true);
        $this->setDoorAccessForInvite(
            $order,
            $people['add'],
            $people['remove']
        );

        return new Response();
    }

    /**
     * @param Request $request
     * @param $id
     * @param ParamFetcherInterface $paramFetcher
     * @param array
     */
    private function deletePeople(
        $removeUsers,
        $orderId,
        $base
    ) {
        $em = $this->getDoctrine()->getManager();
        $userArray = [];
        $recvUsers = [];
        foreach ($removeUsers as $removeUser) {
            $userId = $removeUser['user_id'];
            $person = $this->getRepo('Order\InvitedPeople')->findOneBy(
                [
                    'orderId' => $orderId,
                    'userId' => $userId,
                ]
            );
            if (!is_null($person)) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($person);
                $em->flush();

                // set user array for message
                array_push($recvUsers, $userId);
            }

            // set controller status to delete
            $this->setControlToDelete(
                $orderId,
                $userId
            );

            $result = $this->getCardNoByUser($userId);
            if ($result['status'] !== DoorController::STATUS_UNAUTHED) {
                $empUser = ['empid' => $userId];
                array_push($userArray, $empUser);
            }
        }
        $em->flush();

        // remove room access
        if (!empty($userArray)) {
            $this->callRemoveFromOrderCommand(
                $base,
                $orderId,
                $userArray
            );
        }

        return $recvUsers;
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

        // check if request user is the same as order user
        $this->throwAccessDeniedIfNotSameUser($order->getUserId());

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
        $newUser = $requestContent['user_id'];
        $currentUser = $order->getAppointed();
        $orderUser = $order->getUserId();

        if (!is_null($newUser) && !empty($newUser)) {
            $this->setDoorAccessForAppoint(
                $order,
                $newUser,
                $currentUser,
                $orderUser
            );
        }

        return new Response();
    }

    /**
     * @Delete("/orders/{id}/person/appoint")
     *
     * @param Request $request
     * @param $id
     */
    public function removePersonAction(
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

        // check if request user is the same as order user
        $this->throwAccessDeniedIfNotSameUser($order->getUserId());

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

        $currentUser = $order->getAppointed();
        $orderUser = $order->getUserId();

        if (!is_null($orderUser) && !empty($orderUser)) {
            $this->removeAppointed(
                $order,
                $currentUser,
                $orderUser
            );
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
            $appointedPerson = $this->getRepo('User\UserView')->find($appointed);
        }

        $now = new \DateTime();
        $userId = $order->getUserId();
        $type = $order->getProduct()->getRoom()->getType();
        $productId = $order->getProductId();
        $status = $order->getStatus();
        $startDate = $order->getStartDate();
        $renewButton = false;

        if ($type == Room::TYPE_OFFICE && $status == ProductOrder::STATUS_COMPLETED) {
            $renewOrder = $this->getRepo('Order\ProductOrder')->getAlreadyRenewedOrder($userId, $productId);
            if (is_null($renewOrder) || empty($renewOrder)) {
                $endDate = $order->getEndDate();
                $days = $endDate->diff($now)->days;
                if ($days > 7 && $now >= $startDate) {
                    $renewButton = true;
                }
            }
        }

        if ($status == ProductOrder::STATUS_PAID && $now >= $startDate) {
            $order->setStatus(ProductOrder::STATUS_COMPLETED);
            $order->setModificationDate($now);
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData(
            [
                'renewButton' => $renewButton,
                'order' => $order,
                'appointedPerson' => $appointedPerson,
            ]
        );

        return $view;
    }

    /**
     * @Get("/orders/{id}/remaining")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getOrderRemainingTimeAction(
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
        $now = new \DateTime();
        $minutes = 0;
        $seconds = 0;

        if ($status == 'unpaid') {
            $creationDate = $order->getCreationDate();
            $remainingTime = $now->diff($creationDate);
            $minutes = $remainingTime->i;
            $seconds = $remainingTime->s;
            $minutes = 14 - $minutes;
            $seconds = 59 - $seconds;
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
        }

        $view = new View();
        $view->setData(
            [
                'remainingMinutes' => $minutes,
                'remainingSeconds' => $seconds,
            ]
        );

        return $view;
    }

    /**
     * @param $productId
     * @param $startDate
     * @param $endDate
     *
     * @return ProductOrderCheck
     */
    private function setProductOrderCheck(
        $productId,
        $startDate,
        $endDate
    ) {
        $em = $this->getDoctrine()->getManager();
        $orderCheck = new ProductOrderCheck();
        $orderCheck->setProductId($productId);
        $orderCheck->setStartDate($startDate);
        $orderCheck->setEndDate($endDate);
        $em->persist($orderCheck);
        $em->flush();

        return $orderCheck;
    }

    /**
     * @param $em
     * @param $type
     * @param $allowedPeople
     * @param $productId
     * @param $startDate
     * @param $endDate
     *
     * @return View|ProductOrderCheck
     */
    private function orderDuplicationCheck(
        $em,
        $type,
        $allowedPeople,
        $productId,
        $startDate,
        $endDate
    ) {
        if ($type == Room::TYPE_FLEXIBLE) {
            //check if flexible room is full before order creation
            $orderCount = $this->getRepo('Order\ProductOrder')->checkFlexibleForClient(
                $productId,
                $startDate,
                $endDate
            );

            if ($allowedPeople <= $orderCount) {
                throw new ConflictHttpException(self::ORDER_CONFLICT_MESSAGE);
            }

            // check if flexible room is full after order check creation
            // in case of duplicate submits
            $orderCheck = $this->setProductOrderCheck(
                $productId,
                $startDate,
                $endDate
            );

            $orderCheckCount = $this->getRepo('Order\ProductOrderCheck')->checkFlexibleForClient(
                $productId,
                $startDate,
                $endDate
            );

            if ($allowedPeople < ($orderCount + $orderCheckCount)) {
                $em->remove($orderCheck);
                $em->flush();

                throw new ConflictHttpException(self::ORDER_CONFLICT_MESSAGE);
            }
        } else {
            // check for room conflict before order creation
            $checkOrder = $this->getRepo('Order\ProductOrder')->checkProductForClient(
                $productId,
                $startDate,
                $endDate
            );

            if (!empty($checkOrder) && !is_null($checkOrder)) {
                throw new ConflictHttpException(self::ORDER_CONFLICT_MESSAGE);
            }

            // check for room conflict after order check creation
            // in case of duplicate submits
            $orderCheck = $this->setProductOrderCheck(
                $productId,
                $startDate,
                $endDate
            );

            $orderCheckCount = $this->getRepo('Order\ProductOrderCheck')->checkProductForClient(
                $productId,
                $startDate,
                $endDate
            );

            if ($orderCheckCount > 1) {
                $em->remove($orderCheck);
                $em->flush();

                throw new ConflictHttpException(self::ORDER_CONFLICT_MESSAGE);
            }
        }

        return $orderCheck;
    }

    /**
     * @param $order
     * @param $users
     * @param $removeUsers
     */
    private function setDoorAccessForInvite(
        $order,
        $users,
        $removeUsers
    ) {
        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        $base = $building->getServer();
        $roomId = $order->getProduct()->getRoom()->getId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findBy(['room' => $roomId]);
        if (empty($roomDoors)) {
            throw new NotFoundException(self::NO_DOOR_MESSAGE);
        }

        $userArray = [];
        $recvUsers = [];
        $em = $this->getDoctrine()->getManager();
        if (!empty($users) && !is_null($users)) {
            foreach ($users as $user) {
                $userId = $user['user_id'];

                // find user
                $user = $this->getRepo('User\User')->find($userId);
                $this->throwNotFoundIfNull($user, User::ERROR_NOT_FOUND);

                // find user in invitedPeople
                $person = $this->getRepo('Order\InvitedPeople')->findOneBy(
                    [
                        'orderId' => $order->getId(),
                        'userId' => $userId,
                    ]
                );
                if (is_null($person)) {
                    $people = new InvitedPeople();
                    $people->setOrderId($order);
                    $people->setUserId($userId);
                    $people->setCreationDate(new \DateTime());
                    $em->persist($people);

                    // set user array for message
                    array_push($recvUsers, $userId);
                }

                $this->storeDoorAccess(
                    $em,
                    $order,
                    $userId,
                    $buildingId,
                    $roomId
                );
                $userArray = $this->getUserArrayIfAuthed(
                    $base,
                    $userId,
                    $userArray
                );
            }
            $em->flush();
        }

        $removedUserArray = [];
        if (!empty($removeUsers) && !is_null($removeUsers)) {
            // remove user
            $removedUserArray = $this->deletePeople(
                $removeUsers,
                $order->getId(),
                $base
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

        // send notification to invited users
        if (!empty($recvUsers)) {
            $this->sendXmppProductOrderNotification(
                $order,
                $recvUsers,
                ProductOrder::ACTION_INVITE_ADD,
                $order->getUserId(),
                [],
                ProductOrderMessage::APPOINT_MESSAGE_PART1,
                ProductOrderMessage::APPOINT_MESSAGE_PART2
            );
        }

        // send notification to invited users
        if (!empty($removedUserArray)) {
            $this->sendXmppProductOrderNotification(
                $order,
                $removedUserArray,
                ProductOrder::ACTION_INVITE_REMOVE,
                $order->getUserId(),
                [],
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
            );
        }
    }

    /**
     * @param $order
     */
    private function removeAccessByOrder(
        $order
    ) {
        $em = $this->getDoctrine()->getManager();
        $order->setStatus(ProductOrder::STATUS_CANCELLED);
        $order->setCancelledDate(new \DateTime());
        $order->setModificationDate(new \DateTime());

        // set access action to cancelled
        $orderId = $order->getId();
        $controls = $this->getRepo('Door\DoorAccess')->findByOrderId($orderId);
        if (!empty($controls)) {
            foreach ($controls as $control) {
                $control->setAction(ProductOrder::STATUS_CANCELLED);
                $control->setAccess(false);
            }
        }
        $em->flush();

        // send order email
        $this->sendOrderEmail($order);

        // get appointed user
        $userArray = [];
        $type = $order->getProduct()->getRoom()->getType();
        if ($type == Room::TYPE_OFFICE) {
            $action = ProductOrder::ACTION_INVITE_REMOVE;
            // get invited users
            $people = $this->getRepo('Order\InvitedPeople')->findBy(['orderId' => $order->getId()]);
            if (!empty($people)) {
                foreach ($people as $person) {
                    array_push($userArray, $person->getUserId());
                }
            }
        } else {
            $action = ProductOrder::ACTION_APPOINT_REMOVE;
            $appointed = $order->getAppointed();
            if (!is_null($appointed) && !empty($appointed)) {
                array_push($userArray, $appointed);
            }
        }

        // send notification to invited and appointed users
        $orderUser = $order->getUserId();
        if (!empty($userArray)) {
            $this->sendXmppProductOrderNotification(
                $order,
                $userArray,
                $action,
                $orderUser,
                [],
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
            );
        }

        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        $base = $building->getServer();

        $this->callRepealRoomOrderCommand(
            $base,
            $orderId
        );
    }

    /**
     * @param $order
     * @param $newUser
     * @param $currentUser
     * @param $orderUser
     */
    private function setDoorAccessForAppoint(
        $order,
        $newUser,
        $currentUser,
        $orderUser
    ) {
        $orderId = $order->getId();
        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        $base = $building->getServer();
        $roomId = $order->getProduct()->getRoom()->getId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findBy(['room' => $roomId]);
        if (empty($roomDoors)) {
            throw new NotFoundException(self::NO_DOOR_MESSAGE);
        }

        // find user
        $user = $this->getRepo('User\User')->find($newUser);
        $this->throwNotFoundIfNull($user, User::ERROR_NOT_FOUND);

        $userArray = [];
        $order->setAppointed($newUser);
        $order->setModificationDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();

        // set controller status to delete
        $this->setControlToDelete($orderId);

        // add new door access
        $this->storeDoorAccess(
            $em,
            $order,
            $newUser,
            $buildingId,
            $roomId
        );
        $em->flush();

        $userArray = $this->getUserArrayIfAuthed(
            $base,
            $newUser,
            $userArray
        );

        // set room access
        if (!empty($userArray)) {
            $this->callSetRoomOrderCommand(
                $base,
                $userArray,
                $roomDoors,
                $order
            );
        }

        // remove all user access with method delete
        $this->removeUserAccess(
            $orderId,
            $base
        );

        // send notification to new user
        $this->sendXmppProductOrderNotification(
            $order,
            [$newUser],
            ProductOrder::ACTION_APPOINT_ADD,
            $orderUser,
            [],
            ProductOrderMessage::APPOINT_MESSAGE_PART1,
            ProductOrderMessage::APPOINT_MESSAGE_PART2
        );

        if (!is_null($currentUser) && !empty($currentUser) && $currentUser != 0) {
            // send notification to old appointed user
            $this->sendXmppProductOrderNotification(
                $order,
                [$currentUser],
                ProductOrder::ACTION_APPOINT_REMOVE,
                $orderUser,
                [],
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
            );
        }
    }

    /**
     * @param $order
     * @param $currentUser
     * @param $orderUser
     */
    private function removeAppointed(
        $order,
        $currentUser,
        $orderUser
    ) {
        $orderId = $order->getId();
        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        $base = $building->getServer();
        $roomId = $order->getProduct()->getRoom()->getId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findBy(['room' => $roomId]);
        if (empty($roomDoors)) {
            throw new NotFoundException(self::NO_DOOR_MESSAGE);
        }

        $userArray = [];
        $order->setAppointed(null);
        $order->setModificationDate(new \DateTime());

        // set controller status to delete
        $this->setControlToDelete($orderId);

        $em = $this->getDoctrine()->getManager();
        $this->storeDoorAccess(
            $em,
            $order,
            $orderUser,
            $buildingId,
            $roomId
        );
        $em->flush();
        $userArray = $this->getUserArrayIfAuthed(
            $base,
            $orderUser,
            $userArray
        );
        if (!empty($userArray)) {
            $this->setRoomOrderAccessIfUserArray(
                $base,
                $userArray,
                $roomDoors,
                $order
            );
        }

        // remove all user access with method delete
        $this->removeUserAccess(
            $orderId,
            $base
        );

        if (!is_null($currentUser) && !empty($currentUser) && $currentUser != 0) {
            // send notification to appointed user
            $this->sendXmppProductOrderNotification(
                $order,
                [$currentUser],
                ProductOrder::ACTION_APPOINT_REMOVE,
                $orderUser,
                [],
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
            );
        }
    }
}
