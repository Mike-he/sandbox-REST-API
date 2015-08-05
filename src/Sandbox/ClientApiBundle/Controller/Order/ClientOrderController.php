<?php

namespace Sandbox\ClientApiBundle\Controller\Order;

use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Order\InvitedPeople;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Door\DoorAccess;
use Sandbox\ApiBundle\Form\Order\OrderType;
use JMS\Serializer\SerializationContext;

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
                ['creationDate' => 'DESC'],
                $limit,
                $offset
            );
        } else {
            $orders = $this->getRepo('Order\ProductOrder')->findBy(
                ['userId' => $userId],
                ['creationDate' => 'DESC'],
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
        $now = new \DateTime();
        $type = $product->getRoom()->getType();
        $startDate = new \DateTime($order->getStartDate());
        if ($now > $startDate) {
            return $this->customErrorView(
                400,
                self::WRONG_BOOKING_DATE_CODE,
                self::WRONG_BOOKING_DATE_MESSAGE
            );
        }
        if ($type === 'office' && $order->getIsRenew()) {
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
            $endDate->modify('+ 1 month');
        } else {
            $diff = $startDate->diff($now)->days;
            if ($diff > 7) {
                return $this->customErrorView(
                    400,
                    self::NOT_WITHIN_DATE_RANGE_CODE,
                    self::NOT_WITHIN_DATE_RANGE_MESSAGE
                );
            }
            $period = $form['rent_period']->getData();
            $timeUnit = $form['time_unit']->getData();
            $datePeriod = $period;
            if ($timeUnit === 'hour') {
                $datePeriod = $period * 60;
                $timeUnit = 'min';
            }

            $endDate = clone $startDate;
            $endDate->modify('+'.$datePeriod.$timeUnit);
            $basePrice = $product->getBasePrice();

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

            $calculatedPrice = $basePrice * $period;

            if ($order->getPrice() != $calculatedPrice) {
                return $this->customErrorView(
                    400,
                    self::PRICE_MISMATCH_CODE,
                    self::PRICE_MISMATCH_MESSAGE
                );
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

        $channel = $form['channel']->getData();
        if ($channel !== 'alipay_wap' && $channel !== 'upacp_wap' && $channel !== 'account') {
            return $this->customErrorView(
                400,
                self::WRONG_CHANNEL_CODE,
                self::WRONG_CHANNEL_MESSAGE
            );
        }
        if ($channel === 'account') {
            return $this->payByAccount($order);
        }

        $charge = $this->payForOrder(
            $orderNumber,
            $order->getPrice(),
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
     * @param $order
     *
     * @return View
     */
    private function payByAccount($order)
    {
        $price = $order->getPrice();
        $orderNumber = $order->getOrderNumber();
        $balance = $this->postBalanceChange(
            $order->getUserId(),
            (-1) * $price,
            $orderNumber,
            'account'
        );
        if (!is_null($balance)) {
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
        }

        $view = new View();

        return $view->setData(
            array(
                'balance' => $balance,
                'channel' => 'account',
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

        $channel = $request->get('channel');
        if ($channel !== 'alipay_wap' && $channel !== 'upacp_wap' && $channel !== 'account') {
            return $this->customErrorView(
                400,
                self::WRONG_CHANNEL_CODE,
                self::WRONG_CHANNEL_MESSAGE
            );
        }

        if ($channel === 'account') {
            return $this->payByAccount($order);
        }

        $map = $this->getRepo('Order\OrderMap')->findOneBy(
            [
                'type' => 'product',
                'orderId' => $order->getId(),
            ]
        );
        $chargeId = $map->getChargeId();
        $charge = $this->getChargeDetail($chargeId);
        $charge = json_decode($charge, true);

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
        $price = $order->getPrice();
        $userId = $order->getUserId();
        $balance = $this->postBalanceChange(
            $userId,
            $price,
            $id,
            'account'
        );
        if (!is_null($balance)) {
            $order->setStatus('cancelled');
            $order->setCancelledDate(new \DateTime());
            $order->setModificationDate(new \DateTime());
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
                'channel' => 'account',
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
        $globals = $this->getGlobals();
        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        $base = $building->getServer();
        $roomId = $order->getProduct()->getRoom()->getId();
        $roomDoors = $this->getRepo('Room\RoomDoors')->findBy(['room' => $roomId]);
        $users = json_decode($request->getContent(), true);
        foreach ($users as $user) {
            $checkUser = $this->getRepo('Order\InvitedPeople')->findOneBy(
                [
                    'orderId' => $id,
                    'userId' => $user['user_id'],
                ]
            );
            if (!is_null($checkUser)) {
                return $this->customErrorView(
                    400,
                    self::USER_EXIST_CODE,
                    self::USER_EXIST_MESSAGE
                );
            }
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
     * @Delete("/orders/{id}/people")
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    default="",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Offset of page"
     * )
     *
     * @param Request $request
     * @param $id
     * @param ParamFetcherInterface $paramFetcher
     */
    public function deletePeopleAction(
        Request $request,
        $id,
        ParamFetcherInterface $paramFetcher
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
        $userIds = $paramFetcher->get('id');

        if (empty($userIds)) {
            return $this->customErrorView(
                400,
                self::USER_NOT_FOUND_CODE,
                self::USER_NOT_FOUND_MESSAGE
            );
        }
        $globals = $this->getGlobals();
        $buildingId = $order->getProduct()->getRoom()->getBuilding()->getId();
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        $base = $building->getServer();
        foreach ($userIds as $userId) {
            $checkUser = $this->getRepo('Order\InvitedPeople')->findOneBy(
                [
                    'orderId' => $id,
                    'userId' => $userId,
                ]
            );
            if (is_null($checkUser)) {
                return $this->customErrorView(
                    400,
                    self::USER_NOT_FOUND_CODE,
                    self::USER_NOT_FOUND_MESSAGE
                );
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($checkUser);
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
        $user = $request->get('user_id');
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

        $now = new \DateTime();
        $type = $order->getProduct()->getRoom()->getType();
        $startDate = $order->getStartDate();
        $renewButton = false;
        if ($type === 'office') {
            $endDate = $order->getEndDate();
            $days = $endDate->diff($now)->days;
            if ($days > 7 && $now >= $startDate) {
                $renewButton = true;
            }
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData(
            [
                'renewButton' => $renewButton,
                'order' => $order,
            ]
        );

        return $view;
    }
}
