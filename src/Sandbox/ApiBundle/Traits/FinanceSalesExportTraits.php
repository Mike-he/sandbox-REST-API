<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Constants\EventOrderExport;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentServiceBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Sandbox\ApiBundle\Entity\User\UserCustomer;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait FinanceSalesExportTraits
{
    /**
     * @param $language
     * @param $membershipOrders
     *
     * @return mixed
     *
     * @throws \PHPExcel_Exception
     */
    public function getMembershipOrderExport(
        $language,
        $membershipOrders
    ) {
        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Finance Summary');

        $headers = $this->getSalesExcelHeaders($language);

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 1;

        if (!is_null($membershipOrders) && !empty($membershipOrders)) {
            $membershipBody = $this->setMembershipArray(
                $membershipOrders,
                $language
            );

            $phpExcelObject->setActiveSheetIndex(0)->fromArray($membershipBody, ' ', "A$row");
        }

        $phpExcelObject->getActiveSheet()->getStyle('A1:R1')->getFont()->setBold(true);

        //set column dimension
        for ($col = ord('a'); $col <= ord('o'); ++$col) {
            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension(chr($col))->setAutoSize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('Membership Order');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        $now = new \DateTime();
        $stringDate = $now->format('Y-m');

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'membership_order_'.$stringDate.'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @param $firstDate
     * @param $language
     * @param $events
     * @param $shortOrders
     * @param $longBills
     *
     * @return mixed
     */
    public function getFinanceSummaryExport(
        $firstDate,
        $language,
        $events,
        $shortOrders,
        $longBills,
        $membershipOrders
    ) {
        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Finance Summary');

        $headers = $this->getSalesExcelHeaders($language);

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 1;

        // set sheet body
        if (!is_null($shortOrders) && !empty($shortOrders)) {
            $shortBody = $this->setShortOrderArray(
                $shortOrders,
                $language
            );

            $phpExcelObject->setActiveSheetIndex(0)->fromArray($shortBody, ' ', "A$row");
            $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 3;
        }

        if (!is_null($longBills) && !empty($longBills)) {
            $longBody = $this->setLongOrderArray(
                $longBills,
                $language
            );

            $phpExcelObject->setActiveSheetIndex(0)->fromArray($longBody, ' ', "A$row");
            $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 3;
        }

        if (!is_null($events) && !empty($events)) {
            $eventBody = $this->setEventArray(
                $events,
                $language
            );

            $phpExcelObject->setActiveSheetIndex(0)->fromArray($eventBody, ' ', "A$row");
            $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 3;
        }

        if (!is_null($membershipOrders) && !empty($membershipOrders)) {
            $membershipBody = $this->setMembershipArray(
                $membershipOrders,
                $language
            );

            $phpExcelObject->setActiveSheetIndex(0)->fromArray($membershipBody, ' ', "A$row");
        }

        $phpExcelObject->getActiveSheet()->getStyle('A1:R1')->getFont()->setBold(true);

        //set column dimension
        for ($col = ord('a'); $col <= ord('o'); ++$col) {
            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension(chr($col))->setAutoSize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('Summary');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        $stringDate = $firstDate->format('Y-m');

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'summary_'.$stringDate.'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @return array
     */
    private function getSalesExcelHeaders(
        $language
    ) {
        return [
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_COLLECTION_METHOD, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_BUILDING_NAME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_CATEGORY, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_NO, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_PRODUCT_NAME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ROOM_TYPE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_USERNAME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_BASE_PRICE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_UNIT_PRICE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_AMOUNT, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_DISCOUNT_PRICE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ACTUAL_AMOUNT, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_COMMISSION, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_START_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_END_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_PAYMENT_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_STATUS, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_TYPE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_PAYMENT_CHANNEL, array(), null, $language),
        ];
    }

    /**
     * @param $events
     * @param $language
     *
     * @return array
     */
    private function setEventArray(
        $events,
        $language
    ) {
        $eventBody = [];

        $collection = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_PROFILE_SANDBOX,
            array(),
            null,
            $language
        );

        $orderCategory = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_PROFILE_EVENT_ORDER,
            array(),
            null,
            $language
        );

        $roomType = null;

        $commission = null;

        $orderType = $orderType = $this->get('translator')->trans(
            ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.'user',
            array(),
            null,
            $language
        );

        foreach ($events as $event) {
            $buildingId = $event->getEvent()->getBuildingId();

            if (is_null($buildingId)) {
                $buildingName = null;
            } else {
                $building = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);
                $buildingName = $building ? $building->getName() : null;
            }

            $orderNumber = $event->getOrderNumber();

            $productName = $event->getEvent()->getName();

            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy(['userId' => $event->getUserId()]);
            $username = $profile->getName();

            $basePrice = $event->getPrice();

            $unit = null;

            $price = $basePrice;

            $actualAmount = $price;

            $income = $actualAmount - $actualAmount * $event->getServiceFee() / 100;

            $leasingTime = $event->getEvent()->getEventStartDate()->format('Y-m-d H:i:s')
                .' - '
                .$event->getEvent()->getEventEndDate()->format('Y-m-d H:i:s');

            $creationDate = $event->getCreationDate()->format('Y-m-d H:i:s');

            $payDate = $event->getPaymentDate()->format('Y-m-d H:i:s');

            $status = $this->get('translator')->trans(
                EventOrderExport::TRANS_EVENT_ORDER_STATUS.$event->getStatus(),
                array(),
                null,
                $language
            );

            $channel = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$event->getPayChannel(),
                array(),
                null,
                $language
            );

            $body = $this->getExportBody(
                $collection,
                $buildingName,
                $orderCategory,
                $orderNumber,
                $productName,
                $roomType,
                $username,
                $basePrice,
                $unit,
                $price,
                $actualAmount,
                $income,
                $commission,
                $leasingTime,
                $creationDate,
                $payDate,
                $status,
                $orderType,
                $channel
            );

            $eventBody[] = $body;
        }

        return $eventBody;
    }

    /**
     * @param $membershipOrders
     * @param $language
     *
     * @return array
     */
    private function setMembershipArray(
        $membershipOrders,
        $language
    ) {
        $membershipBody = [];

        $collection = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_PROFILE_SANDBOX,
            array(),
            null,
            $language
        );

        $orderCategory = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_PROFILE_CARD_ORDER,
            array(),
            null,
            $language
        );

        $roomType = null;

        $commission = null;

        $orderType = $orderType = $this->get('translator')->trans(
            ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.'user',
            array(),
            null,
            $language
        );

        foreach ($membershipOrders as $order) {
            $card = $order->getCard();

            $buildingIds = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserGroupDoors')
                ->getBuildingIdsByGroup(
                    null,
                    $card
                );

            $buildingName = null;
            foreach ($buildingIds as $buildingId) {
                $building = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);
                $name = $building ? $building->getName() : null;

                if (is_null($buildingName)) {
                    $buildingName = $name;
                } elseif (!is_null($name)) {
                    $buildingName = $buildingName.", $name";
                }
            }

            $orderNumber = $order->getOrderNumber();

            $productName = $card->getName();

            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy(['userId' => $order->getUser()]);
            $username = $profile->getName();

            $basePrice = $order->getPrice();

            $period = $order->getValidPeriod();
            $unit = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_UNIT.$order->getUnitPrice(),
                array(),
                null,
                $language
            );
            $unit = $period.$unit;

            $price = $order->getPrice();

            $actualAmount = $price;

            $income = $actualAmount - $actualAmount * $order->getServiceFee() / 100;

            $startTime = $order->getStartDate()->format('Y-m-d H:i:s');

            $endTime = $order->getEndDate()->format('Y-m-d H:i:s');

            $creationDate = $order->getCreationDate()->format('Y-m-d H:i:s');

            $payDate = $order->getPaymentDate()->format('Y-m-d H:i:s');

            $status = $this->get('translator')->trans(
                EventOrderExport::TRANS_EVENT_ORDER_STATUS.'completed',
                array(),
                null,
                $language
            );

            $channel = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$order->getPayChannel(),
                array(),
                null,
                $language
            );

            $body = $this->getExportBody(
                $collection,
                $buildingName,
                $orderCategory,
                $orderNumber,
                $productName,
                $roomType,
                $username,
                $basePrice,
                $unit,
                $price,
                $actualAmount,
                $income,
                $commission,
                $startTime,
                $endTime,
                $creationDate,
                $payDate,
                $status,
                $orderType,
                $channel
            );

            $membershipBody[] = $body;
        }

        return $membershipBody;
    }

    /**
     * @param $companyName
     * @param $shortOrders
     * @param $language
     *
     * @return array
     */
    private function setShortOrderArray(
        $shortOrders,
        $language
    ) {
        $shortBody = [];

        $collection = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_PROFILE_SANDBOX,
            array(),
            null,
            $language
        );

        $orderCategory = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_PROFILE_SHORT_RENT_ORDER,
            array(),
            null,
            $language
        );

        $commission = null;

        foreach ($shortOrders as $order) {
            $productId = $order->getProductId();
            $product = $this->getDoctrine()->getRepository('SandboxApiBundle:Product\Product')->find($productId);
            $building = $product->getRoom()->getBuilding();
            $company = $building->getCompany();
            $companyName = $company->getName();
            $buildingName = $building->getName();

            $orderNumber = $order->getOrderNumber();

            $productInfo = json_decode($order->getProductInfo(), true);
            $productName = $productInfo['room']['name'];

            $roomType = $productInfo['room']['type'];
            $roomType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$roomType,
                array(),
                null,
                $language
            );

            $companyServiceInfo = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
                ->findOneBy([
                    'company' => $company,
                    'tradeTypes' => $roomType,
                ]);
            if (!is_null($companyServiceInfo)) {
                $method = $companyServiceInfo->getCollectionMethod();
                if ($method == SalesCompanyServiceInfos::COLLECTION_METHOD_SALES) {
                    $collection = $companyName;
                }
            }

            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy(['userId' => $order->getUserId()]);
            $username = $profile->getName();

            $basePrice = $productInfo['base_price'];

            $unit = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_UNIT.$productInfo['unit_price'],
                array(),
                null,
                $language
            );

            $price = $order->getPrice();

            $actualAmount = $order->getDiscountPrice();

            $income = $actualAmount - $actualAmount * $order->getServiceFee() / 100;

            $startTime = $order->getStartDate()->format('Y-m-d H:i:s');

            $endTime = $order->getEndDate()->format('Y-m-d H:i:s');

            $creationDate = $order->getCreationDate()->format('Y-m-d H:i:s');

            $payDate = $order->getPaymentDate()->format('Y-m-d H:i:s');

            $status = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$order->getStatus(),
                array(),
                null,
                $language
            );

            $type = $order->getType();
            if (is_null($type) || empty($type)) {
                $type = 'user';
            }

            $orderType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.$type,
                array(),
                null,
                $language
            );

            $channel = '';
            if ($order->getPayChannel()) {
                $channel = $this->get('translator')->trans(
                    ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$order->getPayChannel(),
                    array(),
                    null,
                    $language
                );
            }

            $body = $this->getExportBody(
                $collection,
                $buildingName,
                $orderCategory,
                $orderNumber,
                $productName,
                $roomType,
                $username,
                $basePrice,
                $unit,
                $price,
                $actualAmount,
                $income,
                $commission,
                $startTime,
                $endTime,
                $creationDate,
                $payDate,
                $status,
                $orderType,
                $channel
            );

            $shortBody[] = $body;
        }

        return $shortBody;
    }

    /**
     * @param $companyName
     * @param $longOrders
     * @param $language
     *
     * @return array
     */
    private function setLongOrderArray(
        $longBills,
        $language
    ) {
        $longBody = [];

        $collection = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_PROFILE_SANDBOX,
            array(),
            null,
            $language
        );

        $orderCategory = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_PROFILE_LONG_RENT_ORDER,
            array(),
            null,
            $language
        );

        foreach ($longBills as $longBill) {
            /** @var LeaseBill $longBill */
            $lease = $longBill->getLease();
            $product = $lease->getProduct();
            $room = $product->getRoom();
            $building = $room->getBuilding();
            $company = $building->getCompany();
            $companyName = $company->getName();
            $buildingName = $building->getName();
            $city = $building->getCity();

            $orderNumber = $longBill->getSerialNumber();

            $productName = $room->getName();

            $roomType = $room->getType();
            $roomType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$roomType,
                array(),
                null,
                $language
            );

            $collection = $companyName;

            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy(['userId' => $lease->getSupervisor()->getId()]);
            $username = $profile->getName();

            $basePrice = $product->getBasePrice();

            $unit = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_UNIT.$product->getUnitPrice(),
                array(),
                null,
                $language
            );

            $price = $longBill->getAmount();

            $actualAmount = $longBill->getRevisedAmount();

            $income = $actualAmount;

            $commission = 0;

            $serviceBill = $this->getContainer()
                ->get('doctrine')
                ->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
                ->findOneBy([
                    'orderNumber' => $longBill->getSerialNumber(),
                ]);
            if (!is_null($serviceBill)) {
                $commission = $serviceBill->getAmount();
            }

            $startTime = $longBill->getStartDate()->format('Y-m-d H:i:s');
            $endTime = $longBill->getEndDate()->format('Y-m-d H:i:s');

            $creationDate = $longBill->getCreationDate()->format('Y-m-d H:i:s');

            $payDate = $longBill->getPaymentDate()->format('Y-m-d H:i:s');

            $status = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$longBill->getStatus(),
                array(),
                null,
                $language
            );

            $type = $longBill->getOrderMethod();
            $orderType = $this->get('translator')->trans(
                LeaseConstants::TRANS_LEASE_BILL_ORDER_METHOD.$type,
                array(),
                null,
                $language
            );

            $channel = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$longBill->getPayChannel(),
                array(),
                null,
                $language
            );

            $body = $this->getExportBody(
                $collection,
                $buildingName,
                $orderCategory,
                $orderNumber,
                $productName,
                $roomType,
                $username,
                $basePrice,
                $unit,
                $price,
                $actualAmount,
                $income,
                $commission,
                $startTime,
                $endTime,
                $creationDate,
                $payDate,
                $status,
                $orderType,
                $channel
            );

            $longBody[] = $body;
        }

        return $longBody;
    }

    /**
     * @param $collection
     * @param $buildingName
     * @param $orderCategory
     * @param $orderNumber
     * @param $productName
     * @param $roomType
     * @param $username
     * @param $basePrice
     * @param $unit
     * @param $price
     * @param $actualAmount
     * @param $income
     * @param $commission
     * @param $startTime
     * @param $endTime
     * @param $creationDate
     * @param $payDate
     * @param $status
     * @param $orderType
     * @param $channel
     *
     * @return array
     */
    private function getExportBody(
        $collection,
        $buildingName,
        $orderCategory,
        $orderNumber,
        $productName,
        $roomType,
        $username,
        $basePrice,
        $unit,
        $price,
        $actualAmount,
        $income,
        $commission,
        $startTime,
        $endTime,
        $creationDate,
        $payDate,
        $status,
        $orderType,
        $channel = null
    ) {
        // set excel body
        $body = array(
            ProductOrderExport::COLLECTION_METHOD => $collection,
            ProductOrderExport::BUILDING_NAME => $buildingName,
            ProductOrderExport::ORDER_CATEGORY => $orderCategory,
            ProductOrderExport::ORDER_NUMBER => $orderNumber,
            ProductOrderExport::PRODUCT_NAME => $productName,
            ProductOrderExport::ROOM_TYPE => $roomType,
            ProductOrderExport::USERNAME => $username,
            ProductOrderExport::BASE_PRICE => $basePrice,
            ProductOrderExport::UNIT_PRICE => $unit,
            ProductOrderExport::AMOUNT => $price,
            ProductOrderExport::DISCOUNT_PRICE => $actualAmount,
            ProductOrderExport::ACTUAL_AMOUNT => $income,
            ProductOrderExport::COMMISSION => $commission,
            ProductOrderExport::START_TIME => $startTime,
            ProductOrderExport::END_TIME => $endTime,
            ProductOrderExport::CREATION_DATE => $creationDate,
            ProductOrderExport::PAYMENT_TIME => $payDate,
            ProductOrderExport::ORDER_STATUS => $status,
            ProductOrderExport::ORDER_TYPE => $orderType,
            ProductOrderExport::PAYMENT_CHANNEL => $channel,
        );

        return $body;
    }

    public function getFinanceExportPoundage(
        $serviceBills,
        $startString,
        $language
    ) {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Finance Export');

        $headers = [
            '社区',
            '类型',
            '订单号/账单号',
            '商品',
            '商品类型',
            '客户名',
            '下单方式',
            '支付方式',
            '支付渠道',
            '单价',
            '单位',
            '订单原价',
            '付款金额',
            '退款金额',
            '手续费',
            '结算金额',
            '租赁起始时间',
            '租赁结束时间',
            '创建时间',
            '付款时间',
            '订单状态',
            '退款路径',
            '客户手机',
            '客户邮箱',
        ];

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 1;

        $orderType = array(
            'P' => '秒租订单',
            'B' => '长租账单',
        );

        $payments = $em->getRepository('SandboxApiBundle:Payment\Payment')->findAll();
        $payChannels = array();
        foreach ($payments as $payment) {
            $payChannels[$payment->getChannel()] = $payment->getName();
        }

        // set sheet body
        $excelBody = array();
        foreach ($serviceBills as $serviceBill) {
            /** @var FinanceLongRentServiceBill $serviceBill */
            $orderNumber = $serviceBill->getOrderNumber();
            $firstTag = substr($orderNumber, 0, 1);

            switch ($firstTag) {
                case 'P':
                    /** @var ProductOrder $order */
                    $order = $em->getRepository('SandboxApiBundle:Order\ProductOrder')
                        ->findOneBy(array('orderNumber' => $orderNumber));

                    if (!$order) {
                        continue;
                    }
                    $product = $order->getProduct();
                    $payChannel = $order->getPayChannel();
                    $price = $order->getPrice();
                    $discountPrice = $order->getDiscountPrice();
                    $refundAmount = $order->getActualRefundAmount();
                    $startDate = $order->getStartDate();
                    $endDate = $order->getEndDate();
                    $creationDate = $order->getCreationDate()->format('Y-m-d H:i:s');
                    $paymentDate = $order->getPaymentDate()->format('Y-m-d H:i:s');
                    $refundTo = $order->getRefundTo();
                    $basePrice = $order->getBasePrice();
                    $unitPrice = $order->getUnitPrice();

                    $customerId = $order->getCustomerId();
                    if ($customerId) {
                        /** @var UserCustomer $customer */
                        $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
                            ->find($customerId);
                    } else {
                        $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
                            ->findOneBy(array('userId' => $order->getUserId(), 'companyId' => $serviceBill->getCompanyId()));
                    }

                    break;
                case 'B':
                    $bill = $em->getRepository('SandboxApiBundle:Lease\LeaseBill')
                        ->findOneBy(array('serialNumber' => $orderNumber));

                    /** @var LeaseBill $bill */
                    $lease = $bill->getLease();
                    $product = $lease->getProduct();
                    $payChannel = $bill->getPayChannel();
                    $price = $bill->getAmount();
                    $discountPrice = $bill->getRevisedAmount();
                    $refundAmount = 0;
                    $startDate = $bill->getStartDate();
                    $endDate = $bill->getEndDate();
                    $creationDate = $bill->getSendDate()->format('Y-m-d H:i:s');
                    $paymentDate = $bill->getPaymentDate()->format('Y-m-d H:i:s');
                    $refundTo = '';

                    $productRentSet = $em->getRepository('SandboxApiBundle:Product\ProductRentSet')
                        ->findOneBy(array('product' => $product));

                    $basePrice = $productRentSet->getBasePrice();
                    $unitPrice = $productRentSet->getUnitPrice();

                    $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
                        ->find($lease->getLesseeCustomer());

                    break;
                default:
                    continue;
            }

            $room = $product->getRoom();
            $building = $room->getBuilding();

            $roomType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$room->getType(),
                array(),
                null,
                $language
            );

            $unitPriceDesc = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_UNIT.$unitPrice,
                array(),
                null,
                $language
            );

            if ($payChannel == 'sales_offline') {
                $payMethod = '销售方收款';
                $receivables = $em->getRepository('SandboxApiBundle:Finance\FinanceReceivables')
                        ->findOneBy(array('orderNumber' => $orderNumber));
                $channel = $receivables->getPayChannel();
            } else {
                $payMethod = '创合代收';
                $channel = $payChannels[$payChannel];
            }

            $body = array(
                'building_name' => $building->getName(),
                'order_type' => $orderType[$firstTag],
                'order_number' => $orderNumber,
                'room_name' => $room->getName(),
                'room_type' => $roomType,
                'customer' => $customer ? $customer->getName() : '',
                'order_method' => '销售方推单',
                'payment_method' => $payMethod,
                'pay_channel' => $channel,
                'base_price' => $basePrice,
                'unit_price' => $unitPriceDesc,
                'price' => $price,
                'discount_price' => $discountPrice,
                'refund_amount' => $refundAmount,
                'poundage' => $serviceBill->getAmount(),
                'settlement_amount' => $discountPrice - $refundAmount - $serviceBill->getAmount(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'creation_date' => $creationDate,
                'payment_date' => $paymentDate,
                'status' => '已完成',
                'refundTo' => $refundTo,
                'customer_phone' => $customer ? $customer->getPhone() : '',
                'customer_email' => $customer ? $customer->getEmail() : '',
            );

            $excelBody[] = $body;
        }

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($excelBody, ' ', 'A2');

        $phpExcelObject->getActiveSheet()->getStyle('A1:R1')->getFont()->setBold(true);

        //set column dimension
        for ($col = ord('a'); $col <= ord('o'); ++$col) {
            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension(chr($col))->setAutoSize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('Poundage');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'poundage_'.$startString.'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}
