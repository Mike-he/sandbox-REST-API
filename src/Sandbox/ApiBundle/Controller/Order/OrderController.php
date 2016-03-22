<?php

namespace Sandbox\ApiBundle\Controller\Order;

use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Order\ProductOrderCheck;
use Sandbox\ApiBundle\Entity\Order\ProductOrderRecord;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Traits\ProductOrderNotification;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Order Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class OrderController extends PaymentController
{
    use ProductOrderNotification;

    /**
     * @param Request $request
     *
     * @return View
     */
    public function getAllOrders()
    {
        $orders = $this->getRepo('Order\ProductOrder')->findAll();

        return $orders;
    }

    /**
     * @param $id
     *
     * @return View
     */
    public function getOneOrder(
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);

        return $order;
    }

    /**
     * @param $em
     * @param $productId
     * @param $startDate
     * @param $endDate
     *
     * @return ProductOrderCheck
     */
    private function setProductOrderCheck(
        $em,
        $productId,
        $startDate,
        $endDate
    ) {
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
    protected function orderDuplicationCheck(
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
                $em,
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
                $em,
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
     * @param $product
     *
     * @return string
     */
    protected function storeRoomInfo(
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
     * @param $em
     * @param $order
     * @param $product
     */
    protected function storeRoomRecord(
        $em,
        $order,
        $product
    ) {
        $room = $this->getRepo('Room\Room')->find($product->getRoomId());

        $roomRecord = new ProductOrderRecord();

        $roomRecord->setOrder($order);
        $roomRecord->setCityId($room->getCityId());
        $roomRecord->setBuildingId($room->getBuildingId());
        $roomRecord->setRoomType($room->getType());

        $em->persist($roomRecord);
    }

    /**
     * @param $order
     * @param $product
     * @param $startDate
     * @param $endDate
     * @param $user
     * @param $orderCheck
     */
    protected function setOrderFields(
        $order,
        $product,
        $startDate,
        $endDate,
        $user,
        $orderCheck
    ) {
        $orderNumber = $this->getOrderNumberForProductOrder(
            ProductOrder::LETTER_HEAD,
            $orderCheck
        );

        $productInfo = $this->storeRoomInfo($product);

        $order->setOrderNumber($orderNumber);
        $order->setProduct($product);
        $order->setStartDate($startDate);
        $order->setEndDate($endDate);
        $order->setUser($user);
        $order->setLocation('location');
        $order->setProductInfo($productInfo);
    }

    /**
     * @param $period
     * @param $timeUnit
     * @param $startDate
     *
     * @return mixed
     */
    protected function getOrderEndDate(
        $period,
        $timeUnit,
        $startDate
    ) {
        $datePeriod = $period;

        if ($timeUnit === Product::UNIT_HOUR) {
            $datePeriod = $period * 60;
            $timeUnit = Product::UNIT_MIN;
        } elseif ($timeUnit === Product::UNIT_MONTH) {
            $datePeriod = $period * 30;
            $timeUnit = Product::UNIT_DAYS;
        }

        $endDate = clone $startDate;
        $endDate->modify('+'.$datePeriod.$timeUnit);

        return $endDate;
    }

    /**
     * @param $type
     * @param $now
     * @param $startDate
     * @param $endDate
     * @param $product
     *
     * @return array
     */
    protected function checkIfRoomOpen(
        $type,
        $now,
        $startDate,
        $endDate,
        $product
    ) {
        $error = [];

        if ($type == Room::TYPE_OFFICE || $type == Room::TYPE_FIXED || $type == Room::TYPE_FLEXIBLE) {
            $nowDate = $now->format('Y-m-d');
            $startPeriod = $startDate->format('Y-m-d');

            if ($nowDate > $startPeriod) {
                return $this->setErrorArray(
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
                return $this->setErrorArray(
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
                    return $this->setErrorArray(
                        self::ROOM_NOT_OPEN_CODE,
                        self::ROOM_NOT_OPEN_MESSAGE
                    );
                }
            }
        }

        return $error;
    }

    /**
     * @param $product
     * @param $now
     * @param $startDate
     *
     * @return array
     */
    protected function checkIfProductAvailable(
        $product,
        $now,
        $startDate
    ) {
        $error = [];

        if (is_null($product)) {
            return $this->setErrorArray(
                self::PRODUCT_NOT_FOUND_CODE,
                self::PRODUCT_NOT_FOUND_MESSAGE
            );
        }

        $productStart = $product->getStartDate();
        $productEnd = $product->getEndDate();

        if (
            $now < $productStart ||
            $now > $productEnd ||
            $startDate < $productStart ||
            $startDate > $productEnd ||
            $product->getVisible() == false
        ) {
            return $this->setErrorArray(
                self::PRODUCT_NOT_AVAILABLE_CODE,
                self::PRODUCT_NOT_AVAILABLE_MESSAGE
            );
        }

        return $error;
    }

    /**
     * @param $order
     * @param $productId
     * @param $product
     * @param $period
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    protected function checkIfPriceMatch(
        $order,
        $productId,
        $product,
        $period,
        $startDate,
        $endDate
    ) {
        $error = [];
        $basePrice = $product->getBasePrice();
        $calculatedPrice = $basePrice * $period;

        if ($order->getPrice() != $calculatedPrice) {
            return $this->setErrorArray(
                self::PRICE_MISMATCH_CODE,
                self::PRICE_MISMATCH_MESSAGE
            );
        }

        // check for discount rule and price
        $ruleId = $order->getRuleId();

        if (!is_null($ruleId) && !empty($ruleId)) {
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

            if (is_null($result)) {
                return $this->setErrorArray(
                    self::PRICE_RULE_DOES_NOT_EXIST_CODE,
                    self::PRICE_RULE_DOES_NOT_EXIST_MESSAGE
                );
            }

            if (array_key_exists('bind_product_id', $result['rule'])) {
                $order->setMembershipBindId($result['rule']['bind_product_id']);
            }

            if ($discountPrice != $result['discount_price']) {
                return $this->setErrorArray(
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
        } else {
            $order->setDiscountPrice($calculatedPrice);
        }

        return $error;
    }

    /**
     * @param $order
     */
    protected function removeAccessByOrder(
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
        if (is_null($building)) {
            return;
        }
        $base = $building->getServer();
        if (is_null($base) || empty($base)) {
            return;
        }

        $this->callRepealRoomOrderCommand(
            $base,
            $orderId
        );
    }

    /**
     * @param $em
     * @param $order
     * @param $product
     * @param $productId
     * @param $now
     * @param $startDate
     * @param $endDate
     * @param $user
     *
     * @return array
     */
    protected function checkIfOrderAllowed(
        $em,
        $order,
        $product,
        $productId,
        $now,
        $startDate,
        $endDate,
        $user,
        $type
    ) {
        // check booking dates
        $error = $this->checkIfRoomOpen(
            $type,
            $now,
            $startDate,
            $endDate,
            $product
        );

        if (!empty($error)) {
            return $error;
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

        // set product order
        $this->setOrderFields(
            $order,
            $product,
            $startDate,
            $endDate,
            $user,
            $orderCheck
        );

        $em->remove($orderCheck);
    }
}
