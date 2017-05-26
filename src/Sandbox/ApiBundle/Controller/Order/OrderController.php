<?php

namespace Sandbox\ApiBundle\Controller\Order;

use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Order\ProductOrderCheck;
use Sandbox\ApiBundle\Entity\Order\ProductOrderInfo;
use Sandbox\ApiBundle\Entity\Order\ProductOrderRecord;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesUser;
use Sandbox\ApiBundle\Traits\ProductOrderNotification;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
     * @param array  $orders
     * @param string $language
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws \PHPExcel_Exception
     */
    protected function getProductOrderExport(
        $orders,
        $language
    ) {
        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Sandbox Orders');
        $excelBody = array();

        // set excel body
        foreach ($orders as $order) {
            $productInfo = json_decode($order->getProductInfo(), true);

            // set product name
            $productName = $productInfo['room']['city']['name'].
                $productInfo['room']['building']['name'].
                $productInfo['room']['name'];

            // set product type
            $productTypeKey = $productInfo['room']['type'];
            $productType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$productTypeKey,
                array(),
                null,
                $language
            );

            // set unit price
            $unitPriceKey = $productInfo['unit_price'];
            $unitPrice = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_UNIT.$unitPriceKey,
                array(),
                null,
                $language
            );

            // set status
            $statusKey = $order->getStatus();
            $status = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$statusKey,
                array(),
                null,
                $language
            );

            // set leasing name
            $leasingTime = $order->getStartDate()->format('Y-m-d H:i:s')
                .' - '
                .$order->getEndDate()->format('Y-m-d H:i:s');

            $userId = $order->getUserId();
            $user = $this->getRepo('User\User')->find($userId);

            $paymentChannel = $order->getPayChannel();
            $refundChannel = $order->getRefundTo();
            if (!is_null($paymentChannel) && !empty($paymentChannel)) {
                $paymentChannel = $this->get('translator')->trans(
                    ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$paymentChannel,
                    array(),
                    null,
                    $language
                );

                if ($statusKey == ProductOrder::STATUS_CANCELLED) {
                    if (is_null($refundChannel)) {
                        $refundChannel = ProductOrder::REFUND_TO_ORIGIN;
                    } else {
                        $refundChannel = ProductOrder::REFUND_TO_ACCOUNT;
                    }

                    $refundChannel = $this->get('translator')->trans(
                        ProductOrderExport::TRANS_PRODUCT_ORDER_REFUND_TO.$refundChannel,
                        array(),
                        null,
                        $language
                    );
                }
            }

            $orderType = $order->getType();
            if (is_null($orderType) || empty($orderType)) {
                $orderType = 'user';
            }

            $orderType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.$orderType,
                array(),
                null,
                $language
            );

            $companyName = null;
            $buildingName = null;
            $productId = $order->getProductId();
            if (!is_null($productId)) {
                $product = $this->getDoctrine()->getRepository('SandboxApiBundle:Product\Product')->find($productId);
                $building = $product->getRoom()->getBuilding();
                $buildingName = $building->getName();
                $companyName = $building->getCompany()->getName();
            }

            $price = $order->getDiscountPrice();
            $refund = $order->getActualRefundAmount();
            if (is_null($refund) || empty($refund)) {
                $refund = 0;
            }

            $actualAmount = $price - $refund;

            // set excel body
            $body = array(
                ProductOrderExport::COMPANY_NAME => $companyName,
                ProductOrderExport::BUILDING_NAME => $buildingName,
                ProductOrderExport::ORDER_NUMBER => $order->getOrderNumber(),
                ProductOrderExport::PRODUCT_NAME => $productName,
                ProductOrderExport::ROOM_TYPE => $productType,
                ProductOrderExport::USER_ID => $userId,
                ProductOrderExport::BASE_PRICE => $productInfo['base_price'],
                ProductOrderExport::UNIT_PRICE => $unitPrice,
                ProductOrderExport::AMOUNT => $order->getPrice(),
                ProductOrderExport::DISCOUNT_PRICE => $price,
                ProductOrderExport::REFUND_AMOUNT => $refund,
                ProductOrderExport::ACTUAL_AMOUNT => $actualAmount,
                ProductOrderExport::LEASING_TIME => $leasingTime,
                ProductOrderExport::ORDER_TIME => $order->getCreationDate()->format('Y-m-d H:i:s'),
                ProductOrderExport::PAYMENT_TIME => $order->getPaymentDate()->format('Y-m-d H:i:s'),
                ProductOrderExport::ORDER_STATUS => $status,
                ProductOrderExport::REFUND_TO => $refundChannel,
                ProductOrderExport::USER_PHONE => $user->getPhone(),
                ProductOrderExport::USER_EMAIL => $user->getEmail(),
                ProductOrderExport::PAYMENT_CHANNEL => $paymentChannel,
                ProductOrderExport::ORDER_TYPE => $orderType,
            );

            $excelBody[] = $body;
        }

        $headers = [
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_COMPANY_NAME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_BUILDING_NAME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_NO, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_PRODUCT_NAME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ROOM_TYPE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_USER_ID, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_BASE_PRICE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_UNIT_PRICE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_AMOUNT, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_DISCOUNT_PRICE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_REFUND_AMOUNT, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ACTUAL_AMOUNT, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_LEASING_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_PAYMENT_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_STATUS, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_REFUND_TO, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_USER_PHONE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_USER_EMAIL, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_PAYMENT_CHANNEL, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_TYPE, array(), null, $language),
        ];

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($excelBody, ' ', 'A2');

        $phpExcelObject->getActiveSheet()->getStyle('A1:L1')->getFont()->setBold(true);

        //set column dimension
        for ($col = ord('a'); $col <= ord('o'); ++$col) {
            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension(chr($col))->setAutoSize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('Orders');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        $date = new \DateTime('now');
        $stringDate = $date->format('Y-m-d H:i:s');

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'orders_'.$stringDate.'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

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
     * @param $userId
     *
     * @return View|ProductOrderCheck
     */
    protected function orderDuplicationCheck(
        $em,
        $type,
        $allowedPeople,
        $productId,
        $startDate,
        $endDate,
        $userId,
        $seatId
    ) {
        $orderCheck = null;
        try {
            if ($type == Room::TYPE_DESK) {
                //check if flexible room is full before order creation
                $orderCount = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Order\ProductOrder')
                    ->checkFlexibleForClient(
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
                    $endDate,
                    $seatId
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
                    $endDate,
                    $seatId
                );

                if ($orderCheckCount > 1) {
                    $em->remove($orderCheck);
                    $em->flush();

                    throw new ConflictHttpException(self::ORDER_CONFLICT_MESSAGE);
                }

                if ($type == Room::TYPE_OFFICE) {
                    $orders = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Order\ProductOrder')
                        ->getOfficeRejected(
                            $productId,
                            $startDate,
                            $endDate,
                            $userId
                        );

                    if (!empty($orders)) {
                        if (!is_null($orderCheck)) {
                            $em->remove($orderCheck);
                            $em->flush();
                        }

                        throw new ConflictHttpException(self::ORDER_CONFLICT_MESSAGE);
                    }
                }
            }

            return $orderCheck;
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
    protected function storeRoomInfo(
        $product,
        $seatId = null
    ) {
        $room = $this->getRepo('Room\Room')->find($product->getRoomId());
        $city = $this->getRepo('Room\RoomCity')->find($room->getCityId());
        $building = $this->getRepo('Room\RoomBuilding')->find($room->getBuildingId());
        $floor = $this->getRepo('Room\RoomFloor')->find($room->getFloorId());
        $supplies = $this->getRepo('Room\RoomSupplies')->findBy(['room' => $room->getId()]);
        $meeting = $this->getRepo('Room\RoomMeeting')->findOneBy(['room' => $room->getId()]);
        $productLeasingSets = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
            ->findBy(array('product' => $product));

        $seatArray = [];
        if (!is_null($seatId)) {
            $fixedSeat = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomFixed')
                ->findOneBy([
                    'roomId' => $room->getId(),
                    'id' => $seatId,
            ]);

            if (!is_null($fixedSeat)) {
                $seatArray = [
                    'id' => $fixedSeat->getId(),
                    'seat_number' => $fixedSeat->getSeatNumber(),
                    'base_price' => $fixedSeat->getBasePrice(),
                ];
            }
        }

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

        $leasingSetArray = array();
        foreach ($productLeasingSets as $productLeasingSet) {
            $leasingSetArray[] = array(
                'base_price' => $productLeasingSet->getBasePrice(),
                'unit_price' => $productLeasingSet->getUnitPrice(),
                'amount' => $productLeasingSet->getAmount(),
            );
        }

        $productInfo = [
            'id' => $product->getId(),
            'description' => $product->getDescription(),
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
                'type_tag' => $room->getTypeTag(),
                'allowed_people' => $room->getAllowedPeople(),
                'office_supplies' => $supplyArray,
                'meeting' => $meetingArray,
                'attachment' => $attachmentArray,
                'seat' => $seatArray,
                'leasing_set' => $leasingSetArray,
            ],
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

        $this->storeProductOrderInfo(
            $em,
            $product,
            $order
        );
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

        $order->setOrderNumber($orderNumber);
        $order->setProduct($product);
        $order->setStartDate($startDate);
        $order->setEndDate($endDate);
        $order->setUser($user);
        $order->setLocation('location');
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
        $endDate = clone $startDate;
        $firstDay = '01-30';
        $secondDay = '01-31';

        $day = $startDate->format('m-d');
        $year = $startDate->format('Y');

        if ($timeUnit === Product::UNIT_HOUR) {
            $datePeriod = $period * 60;
            $timeUnit = Product::UNIT_MIN;
        }

        $endDate->modify('+'.$datePeriod.' '.$timeUnit);

        if ($timeUnit === Product::UNIT_MONTH &&
            ($day == $firstDay || $day == $secondDay)
        ) {
            $endDate->setDate($year, 3, 1);
        }

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

        if ($type == Room::TYPE_OFFICE || $type == Room::TYPE_DESK) {
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
     * @param $basePrice
     *
     * @return array
     */
    protected function checkIfPriceMatch(
        $order,
        $productId,
        $product,
        $period,
        $startDate,
        $endDate,
        $basePrice
    ) {
        $error = [];
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
        $controls = $this->getRepo('Door\DoorAccess')->findByAccessNo($orderId);
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
     * @param $type
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
            $endDate,
            $user->getId(),
            $order->getSeatId()
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
        $em->flush();
    }

    /**
     * @param $em
     * @param $product
     * @param $order
     */
    protected function storeProductOrderInfo(
        $em,
        $product,
        $order
    ) {
        $productInfo = $this->storeRoomInfo(
            $product,
            $order->getSeatId()
        );

        $productOrderInfo = new ProductOrderInfo();
        $productOrderInfo->setOrder($order);
        $productOrderInfo->setProductInfo($productInfo);

        $em->persist($productOrderInfo);
    }

    /**
     * @param $em
     * @param $salesUserId
     * @param $product
     */
    protected function setSalesUser(
        $em,
        $salesUserId,
        $product
    ) {
        // check sales user record
        $companyId = $product->getRoom()->getBuilding()->getCompanyId();
        $buildingId = $product->getRoom()->getBuildingId();

        $salesUser = $this->getRepo('SalesAdmin\SalesUser')->findOneBy(array(
            'userId' => $salesUserId,
            'buildingId' => $buildingId,
        ));

        if (is_null($salesUser)) {
            $salesUser = new SalesUser();

            $salesUser->setUserId($salesUserId);
            $salesUser->setCompanyId($companyId);
            $salesUser->setBuildingId($buildingId);
        }

        $salesUser->setIsOrdered(true);
        $salesUser->setModificationDate(new \DateTime('now'));

        $em->persist($salesUser);
    }

    /**
     * @param Product      $product
     * @param ProductOrder $order
     */
    protected function setOrderDrawer(
        $product,
        $order
    ) {
        $type = $product->getRoom()->getType();
        $salesCompany = $product->getRoom()->getBuilding()->getCompany();
        $salesCompanyInfo = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->findOneBy(array(
                'tradeTypes' => $type,
                'company' => $salesCompany,
            ));

        if (is_null($salesCompanyInfo)) {
            return;
        }

        $drawer = $salesCompanyInfo->getDrawer();
        if ($drawer == SalesCompanyServiceInfos::DRAWER_SANDBOX) {
            return;
        }

        $order->setSalesInvoice(true);

        return;
    }

    /**
     * @param $number
     *
     * @return array
     */
    protected function getProductOrderResponse(
        $number
    ) {
        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->findOneBy(array(
                'orderNumber' => $number,
            ));

        return array(
            'trade_id' => $order->getId(),
            'company_id' => $order->getProduct()->getRoom()->getBuilding()->getCompany()->getId(),
            'company_name' => $order->getProduct()->getRoom()->getBuilding()->getCompany()->getName(),
            'trade_type' => 'product_order',
            'trade_number' => $number,
            'trade_name' => '',
            'room_name' => $order->getProduct()->getRoom()->getName(),
            'payment_date' => $order->getPaymentDate(),
            'category' => $this->getOrderInvoiceCategory($order),
            'amount' => (float) $order->getDiscountPrice(),
            'sales_invoice' => $order->isSalesInvoice(),
        );
    }

    /**
     * @param $number
     *
     * @return array
     */
    protected function getLeaseBillResponse(
        $number
    ) {
        $bill = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findOneBy(array(
                'serialNumber' => $number,
            ));

        return array(
            'trade_id' => $bill->getId(),
            'company_id' => $bill->getLease()->getProduct()->getRoom()->getBuilding()->getCompany()->getId(),
            'company_name' => $bill->getLease()->getProduct()->getRoom()->getBuilding()->getCompany()->getName(),
            'trade_type' => 'lease_bill',
            'trade_number' => $number,
            'trade_name' => $bill->getName(),
            'room_name' => $bill->getLease()->getProduct()->getRoom()->getName(),
            'payment_date' => $bill->getPaymentDate(),
            'category' => $this->getBillInvoiceCategory($bill),
            'amount' => (float) $bill->getRevisedAmount(),
            'sales_invoice' => $bill->isSalesInvoice(),
        );
    }

    /**
     * @param ProductOrder $order
     *
     * @return mixed
     */
    private function getOrderInvoiceCategory(
        $order
    ) {
        $roomType = $order->getProduct()->getRoom()->getType();
        $salesCompany = $order->getProduct()->getRoom()->getBuilding()->getCompany();

        $info = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->findOneBy(array(
                'company' => $salesCompany,
                'tradeTypes' => $roomType,
            ));

        if (is_null($info)) {
            return '';
        }

        return $info->getInvoicingSubjects();
    }

    /**
     * @param LeaseBill $bill
     *
     * @return mixed
     */
    private function getBillInvoiceCategory(
        $bill
    ) {
        $roomType = $bill->getLease()->getProduct()->getRoom()->getType();
        $salesCompany = $bill->getLease()->getProduct()->getRoom()->getBuilding()->getCompany();

        $info = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->findOneBy(array(
                'company' => $salesCompany,
                'tradeTypes' => $roomType,
            ));

        if (is_null($info)) {
            return '';
        }

        return $info->getInvoicingSubjects();
    }
}
