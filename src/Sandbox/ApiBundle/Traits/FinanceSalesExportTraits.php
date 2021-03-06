<?php

namespace Sandbox\ApiBundle\Traits;

use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Constants\EventOrderExport;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentServiceBill;
use Sandbox\ApiBundle\Entity\Finance\FinanceSalesWalletFlow;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\MembershipCard\MembershipOrder;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Product\Product;
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
     * @param $filename
     * @param $language
     * @param $events
     * @param $shortOrders
     * @param $membershipOrders
     *
     * @return mixed
     */
    public function getFinanceSummaryExport(
        $filename,
        $language,
        $events,
        $shortOrders,
        $membershipOrders
    ) {
        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Finance Summary');

        $headers = [
            '社区',
            '类型',
            '订单号',
            '商品',
            '房间类型',
            '客户',
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

        $em = $this->getContainer()->get('doctrine')->getManager();
        $payments = $em->getRepository('SandboxApiBundle:Payment\Payment')->findAll();
        $payChannels = array();
        foreach ($payments as $payment) {
            $payChannels[$payment->getChannel()] = $payment->getName();
        }

        // set sheet body
        if (!is_null($shortOrders) && !empty($shortOrders)) {
            $shortBody = $this->getShortOrderBody(
                $shortOrders,
                $language,
                $payChannels
            );

            $phpExcelObject->setActiveSheetIndex(0)->fromArray($shortBody, ' ', "A$row");
            $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 3;
        }

        if (!is_null($events) && !empty($events)) {
            $eventBody = $this->getEventOrderBody(
                $events,
                $language,
                $payChannels
            );

            $phpExcelObject->setActiveSheetIndex(0)->fromArray($eventBody, ' ', "A$row");
            $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 3;
        }

        if (!is_null($membershipOrders) && !empty($membershipOrders)) {
            $membershipBody = $this->getMembershipOrderBody(
                $membershipOrders,
                $language,
                $payChannels
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

        // adding headers
        $filename = $filename.'.xls';
        $filename = urlencode($filename);

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

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
     * @param $payChannels
     *
     * @return array
     */
    private function getEventOrderBody(
        $events,
        $language,
        $payChannels
    ) {
        $eventBody = [];
        foreach ($events as $event) {
            /** @var EventOrder $event */
            $buildingId = $event->getEvent()->getBuildingId();

            if (is_null($buildingId)) {
                $buildingName = null;
            } else {
                $building = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);
                $buildingName = $building ? $building->getName() : null;
            }

            $customer = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(array(
                    'userId' => $event->getUserId(),
                    'companyId' => $event->getEvent()->getSalesCompanyId(),
                ));

            $basePrice = $event->getPrice();
            $price = $event->getPrice();
            $discountPrice = $event->getPrice();

            $body = array(
                'building_name' => $buildingName,
                'order_type' => '活动订单',
                'order_number' => $event->getOrderNumber(),
                'room_name' => $event->getEvent()->getName(),
                'room_type' => '',
                'customer' => $customer ? $customer->getName() : '',
                'order_method' => '用户自主下单',
                'payment_method' => '创合代收',
                'pay_channel' => $payChannels[$event->getPayChannel()],
                'base_price' => $basePrice,
                'unit_price' => '',
                'price' => $price,
                'discount_price' => $discountPrice,
                'refund_amount' => '',
                'poundage' => '',
                'settlement_amount' => $discountPrice,
                'start_date' => $event->getEvent()->getEventStartDate()->format('Y-m-d H:i:s'),
                'end_date' => $event->getEvent()->getEventEndDate()->format('Y-m-d H:i:s'),
                'creation_date' => $event->getCreationDate()->format('Y-m-d H:i:s'),
                'payment_date' => $event->getPaymentDate()->format('Y-m-d H:i:s'),
                'status' => '已完成',
                'refundTo' => '',
                'customer_phone' => $customer ? $customer->getPhone() : '',
                'customer_email' => $customer ? $customer->getEmail() : '',
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
            ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.'own',
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
     * @param $shortOrders
     * @param $language
     * @param $payChannels
     *
     * @return array
     */
    private function getShortOrderBody(
        $shortOrders,
        $language,
        $payChannels
    ) {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $receivableTypes = [
            'sales_wx' => '微信',
            'sales_alipay' => '支付宝支付',
            'sales_cash' => '现金',
            'sales_others' => '其他',
            'sales_pos' => 'POS机',
            'sales_remit' => '线下汇款',
        ];

        $shortBody = [];
        foreach ($shortOrders as $order) {
            /** @var ProductOrder $order */
            $product = $order->getProduct();
            $room = $product->getRoom();
            $building = $room->getBuilding();

            $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(array(
                    'userId' => $order->getUserId(),
                    'companyId' => $building->getCompanyId(),
                ));

            $roomType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$room->getType(),
                array(),
                null,
                $language
            );

            $unit = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_UNIT.$order->getUnitPrice(),
                array(),
                null,
                $language
            );

            $discountPrice = $order->getDiscountPrice();
            $refundAmount = $order->getActualRefundAmount();

            $poundage = '';
            if (ProductOrder::PREORDER_TYPE == $order->getType()) {
                $serviceBill = $em->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
                    ->findOneBy(array('orderNumber' => $order->getOrderNumber()));

                $poundage = $serviceBill ? $serviceBill->getAmount() : '';
            }

            $refundTo = null;
            if ($order->isRefunded()) {
                if ('account' == $order->getRefundTo()) {
                    $refundTo = '退款到余额';
                } else {
                    $refundTo = '原路退回';
                }
            }

            if ($order->getPayChannel()) {
                $paymentMethod = ProductOrder::CHANNEL_SALES_OFFLINE == $order->getPayChannel() ? '销售方收款' : '创合代收';
                $paymentDate = $order->getPaymentDate()->format('Y-m-d H:i:s');

                if (ProductOrder::CHANNEL_SALES_OFFLINE == $order->getPayChannel()) {
                    $receivable = $em->getRepository('SandboxApiBundle:Finance\FinanceReceivables')
                        ->findOneBy([
                            'orderNumber' => $order->getOrderNumber(),
                        ]);
                    $payChannel = $receivableTypes[$receivable->getPayChannel()];
                } else {
                    $payChannel = $payChannels[$order->getPayChannel()];
                }
            } else {
                $paymentMethod = '';
                $payChannel = '';
                $paymentDate = '';
            }

            $orderType = $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.$order->getType());
            $status = $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$order->getStatus());

            $body = array(
                'building_name' => $building->getName(),
                'order_type' => '秒租订单',
                'order_number' => $order->getOrderNumber(),
                'room_name' => $room->getName(),
                'room_type' => $roomType,
                'customer' => $customer ? $customer->getName() : '',
                'order_method' => $orderType,
                'payment_method' => $paymentMethod,
                'pay_channel' => $payChannel,
                'base_price' => $order->getBasePrice(),
                'unit_price' => $unit,
                'price' => $order->getPrice(),
                'discount_price' => $discountPrice,
                'refund_amount' => $order->getActualRefundAmount(),
                'poundage' => $poundage,
                'settlement_amount' => $discountPrice - $refundAmount - $poundage,
                'start_date' => $order->getStartDate()->format('Y-m-d H:i:s'),
                'end_date' => $order->getEndDate()->format('Y-m-d H:i:s'),
                'creation_date' => $order->getCreationDate()->format('Y-m-d H:i:s'),
                'payment_date' => $paymentDate,
                'status' => $status,
                'refundTo' => $refundTo,
                'customer_phone' => $customer ? $customer->getPhone() : '',
                'customer_email' => $customer ? $customer->getEmail() : '',
            );
            $shortBody[] = $body;
        }

        return $shortBody;
    }

    /**
     * @param $membershipOrders
     * @param $language
     *
     * @return array
     */
    private function getMembershipOrderBody(
        $membershipOrders,
        $language,
        $payChannels
    ) {
        $membershipBody = [];
        foreach ($membershipOrders as $order) {
            /** @var MembershipOrder $order */
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

            $period = $order->getValidPeriod();
            $unit = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_UNIT.$order->getUnitPrice(),
                array(),
                null,
                $language
            );
            $unit = $period.$unit;

            $price = $order->getPrice();

            $discountPrice = $price;
            $customer = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(array(
                    'userId' => $order->getUser(),
                    'companyId' => $order->getCard()->getCompanyId(),
                ));

            $body = array(
                'building_name' => $buildingName,
                'order_type' => '会员卡订单',
                'order_number' => $order->getOrderNumber(),
                'room_name' => $card->getName(),
                'room_type' => '',
                'customer' => $customer ? $customer->getName() : '',
                'order_method' => '用户自主下单',
                'payment_method' => '创合代收',
                'pay_channel' => $payChannels[$order->getPayChannel()],
                'base_price' => $order->getPrice(),
                'unit_price' => $unit,
                'price' => $order->getPrice(),
                'discount_price' => $discountPrice,
                'refund_amount' => '',
                'poundage' => '',
                'settlement_amount' => $discountPrice,
                'start_date' => $order->getStartDate()->format('Y-m-d H:i:s'),
                'end_date' => $order->getEndDate()->format('Y-m-d H:i:s'),
                'creation_date' => $order->getCreationDate()->format('Y-m-d H:i:s'),
                'payment_date' => $order->getPaymentDate()->format('Y-m-d H:i:s'),
                'status' => '已完成',
                'refundTo' => '',
                'customer_phone' => $customer ? $customer->getPhone() : '',
                'customer_email' => $customer ? $customer->getEmail() : '',
            );

            $membershipBody[] = $body;
        }

        return $membershipBody;
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
        $language,
        $filename
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

        $receivableTypes = [
            'sales_wx' => '微信',
            'sales_alipay' => '支付宝支付',
            'sales_cash' => '现金',
            'sales_others' => '其他',
            'sales_pos' => 'POS机',
            'sales_remit' => '线下汇款',
        ];

        // set sheet body
        $excelBody = array();
        foreach ($serviceBills as $serviceBill) {
            /** @var FinanceLongRentServiceBill $serviceBill */
            $orderNumber = $serviceBill->getOrderNumber();
            $firstTag = substr($orderNumber, 0, 1);

            $basePrice = null;
            $unitPrice = null;
            $refundTo = null;
            $refundAmount = 0;
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
                    $basePrice = $order->getBasePrice();
                    $unitPrice = $order->getUnitPrice();

                    $unitPriceDesc = $this->get('translator')->trans(
                        ProductOrderExport::TRANS_ROOM_UNIT.$unitPrice,
                        array(),
                        null,
                        $language
                    );

                    $customerId = $order->getCustomerId();

                    if ($order->isRefunded()) {
                        if ('account' == $order->getRefundTo()) {
                            $refundTo = '退款到余额';
                        } else {
                            $refundTo = '原路退回';
                        }
                    }

                    $status = ProductOrder::STATUS_COMPLETED == $order->getStatus() ? '已完成' : '已取消';

                    break;
                case 'B':
                    $bill = $em->getRepository('SandboxApiBundle:Lease\LeaseBill')
                        ->findOneBy(array('serialNumber' => $orderNumber));

                    if (!$bill) {
                        continue;
                    }

                    /** @var LeaseBill $bill */
                    $lease = $bill->getLease();
                    $product = $lease->getProduct();
                    $payChannel = $bill->getPayChannel();
                    $price = $bill->getAmount();
                    $discountPrice = $bill->getRevisedAmount();

                    $startDate = $bill->getStartDate();
                    $endDate = $bill->getEndDate();
                    $creationDate = $bill->getSendDate()->format('Y-m-d H:i:s');
                    $paymentDate = $bill->getPaymentDate()->format('Y-m-d H:i:s');

                    $customerId = $bill->getCustomerId() ? $bill->getCustomerId() : $lease->getLesseeCustomer();

                    $unitPriceDesc = null;

                    $status = '已完成';

                    break;
                default:
                    continue;
            }

            $room = $product->getRoom();
            $building = $room->getBuilding();

            $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($customerId);

            $roomType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$room->getType(),
                array(),
                null,
                $language
            );

            if ('sales_offline' == $payChannel) {
                $payMethod = '销售方收款';
                $receivables = $em->getRepository('SandboxApiBundle:Finance\FinanceReceivables')
                        ->findOneBy(array('orderNumber' => $orderNumber));
                $channel = $receivableTypes[$receivables->getPayChannel()];
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
                'status' => $status,
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
        $filename = $filename.'.xls';
        $filename = urlencode($filename);

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

        return $response;
    }

    /**
     * @param $flows
     * @param $language
     *
     * @return mixed
     */
    public function getFinanceSalesWalletFlowsExport(
       $flows,
       $language,
       $filename
    ) {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Finance Export');

        $headers = [
            '时间',
            '明细',
            '入账金额',
            '出账金额',
            '余额',
        ];

        $excelBody = array();
        foreach ($flows as $flow) {
            $title = $flow->getTitle();
            if (FinanceSalesWalletFlow::WITHDRAW_AMOUNT == $title) {
                $enterAmount = '';
                $outAmount = $flow->getChangeAmount();
            } else {
                $enterAmount = $flow->getChangeAmount();
                $outAmount = '';
            }
            $body = array(
                'date' => $flow->getCreationDate()->format('Y-m-d'),
                'title' => $title,
                'enter_amount' => $enterAmount,
                'out_amount' => $outAmount,
                'total_amount' => $flow->getWalletTotalAmount(),
            );
            $excelBody[] = $body;
        }

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($excelBody, ' ', 'A2');

        $phpExcelObject->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);

        //set column dimension
        for ($col = ord('a'); $col <= ord('s'); ++$col) {
            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension(chr($col))->setAutoSize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('导表');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);

        $filename = $filename.'.xls';
        $filename = urlencode($filename);

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

        return $response;
    }

    /**
     * @param $orderNumbers
     * @param $language
     *
     * @return mixed
     */
    public function getFinanceCashierExport(
        $orderNumbers,
        $language,
        $filename
    ) {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Finance Export');

        $headers = [
            '社区',
            '类型',
            '订单号/账单号',
            '商品',
            '房间类型',
            '客户名',
            '下单方式',
            '支付方式',
            '支付渠道',
            '单价',
            '单位',
            '订单/账单原价',
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

        $receivableTypes = [
            'sales_wx' => '微信',
            'sales_alipay' => '支付宝支付',
            'sales_cash' => '现金',
            'sales_others' => '其他',
            'sales_pos' => 'POS机',
            'sales_remit' => '线下汇款',
        ];

        $payments = $em->getRepository('SandboxApiBundle:Payment\Payment')->findAll();
        $payChannels = array();
        foreach ($payments as $payment) {
            $payChannels[$payment->getChannel()] = $payment->getName();
        }

        $excelBody = array();
        foreach ($orderNumbers as $orderNumber) {
            $orderNumber = $orderNumber['order_number'];
            $refundTo = '';
            $paymentMethod = '';
            $payChannel = '';
            $paymentDate = '';

            $serviceBill = $em->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
                ->findOneBy(array('orderNumber' => $orderNumber));

            $poundage = $serviceBill ? $serviceBill->getAmount() : '';

            switch (substr($orderNumber, 0, 1)) {
                case ProductOrder::LETTER_HEAD:
                    $orderType = '秒租订单';
                    $order = $em->getRepository('SandboxApiBundle:Order\ProductOrder')
                        ->findOneBy(array('orderNumber' => $orderNumber));
                    /** @var Product $product */
                    $product = $order->getProduct();

                    $customerId = $order->getCustomerId();
                    $basePrice = $order->getBasePrice();
                    $unit = $this->get('translator')->trans(
                        ProductOrderExport::TRANS_ROOM_UNIT.$order->getUnitPrice(),
                        array(),
                        null,
                        $language
                    );

                    $amount = $order->getPrice();
                    $revisedAmount = $order->getDiscountPrice();
                    $refundAmount = $order->getActualRefundAmount();
                    $startDate = $order->getStartDate()->format('Y-m-d H:i:s');
                    $endDate = $order->getEndDate()->format('Y-m-d H:i:s');
                    $creationDate = $order->getCreationDate()->format('Y-m-d H:i:s');
                    if ($order->isRefunded()) {
                        if ('account' == $order->getRefundTo()) {
                            $refundTo = '退款到余额';
                        } else {
                            $refundTo = '原路退回';
                        }
                    }

                    $channel = $order->getPayChannel();

                    $paymentDate = $order->getPaymentDate() ? $order->getPaymentDate()->format('Y-m-d H:i:s') : '';

                    $status = $this->get('translator')
                        ->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$order->getStatus());

                    break;
                case LeaseBill::LEASE_BILL_LETTER_HEAD:
                    $orderType = '长租账单';
                    $bill = $em->getRepository('SandboxApiBundle:Lease\LeaseBill')
                        ->findOneBy(array('serialNumber' => $orderNumber));

                    /** @var Lease $lease */
                    $lease = $bill->getLease();
                    $product = $lease->getProduct();

                    $customerId = $bill->getCustomerId() ? $bill->getCustomerId() : $lease->getLesseeCustomer();
                    $basePrice = '';
                    $unit = '';
                    $amount = $bill->getAmount();
                    $revisedAmount = $bill->getRevisedAmount();
                    $refundAmount = '';
                    $startDate = $bill->getStartDate()->format('Y-m-d H:i:s');
                    $endDate = $bill->getEndDate()->format('Y-m-d H:i:s');
                    $creationDate = $bill->getSendDate()->format('Y-m-d H:i:s');

                    $channel = $bill->getPayChannel();

                    $paymentDate = $bill->getPaymentDate() ? $bill->getPaymentDate()->format('Y-m-d H:i:s') : '';

                    $status = $this->get('translator')
                        ->trans(LeaseConstants::TRANS_LEASE_BILL_STATUS.$bill->getStatus());

                    break;
            }

            $room = $product->getRoom();
            $building = $room->getBuilding();

            $roomType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$room->getType(),
                array(),
                null,
                $language
            );

            $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')->find($customerId);

            if ($channel) {
                if (ProductOrder::CHANNEL_SALES_OFFLINE == $channel) {
                    $receivable = $em->getRepository('SandboxApiBundle:Finance\FinanceReceivables')
                        ->findOneBy([
                            'orderNumber' => $orderNumber,
                        ]);
                    $payChannel = $receivableTypes[$receivable->getPayChannel()];
                    $paymentMethod = '销售方收款';
                } else {
                    $paymentMethod = '创合代收';
                    $payChannel = $payChannels[$order->getPayChannel()];
                }
            }

            $body = array(
                'building_name' => $building->getName(),
                'order_type' => $orderType,
                'serial_number' => $orderNumber,
                'room_name' => $room->getName(),
                'room_type' => $roomType,
                'customer' => $customer ? $customer->getName() : '',
                'order_method' => '销售方推单',
                'payment_method' => $paymentMethod,
                'pay_channel' => $payChannel,
                'base_price' => $basePrice,
                'unit_price' => $unit,
                'amount' => $amount,
                'revised_amount' => $revisedAmount,
                'refund_amount' => $refundAmount,
                'poundage' => $poundage,
                'settlement_amount' => $revisedAmount - $refundAmount - $poundage,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'creation_date' => $creationDate,
                'payment_date' => $paymentDate,
                'status' => $status,
                'refundTo' => $refundTo,
                'customer_phone' => $customer ? $customer->getPhone() : '',
                'customer_email' => $customer ? $customer->getEmail() : '',
            );

            $excelBody[] = $body;
        }

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($excelBody, ' ', 'A2');

        $phpExcelObject->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);

        //set column dimension
        for ($col = ord('a'); $col <= ord('s'); ++$col) {
            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension(chr($col))->setAutoSize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('导表');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);

        $filename = $filename.'.xls';
        $filename = urlencode($filename);

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

        return $response;
    }

    /**
     * @param $language
     * @param $bills
     *
     * @return mixed
     */
    public function getFinanceExportBills(
        $language,
        $bills,
        $filename
    ) {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Finance Export');

        $headers = [
            '社区',
            '空间名',
            '类型',
            '账单号',
            '合同号',
            '账单名',
            '客户名',
            '下单方式',
            '支付方式',
            '支付渠道',
            '单价',
            '单位',
            '账单原价',
            '付款金额',
            '退款金额',
            '手续费',
            '结算金额',
            '账单起始时间',
            '账单结束时间',
            '推送时间',
            '付款时间',
            '账单状态',
            '退款路径',
            '客户手机',
            '客户邮箱',
        ];

        $orderMethod = [
            LeaseBill::ORDER_METHOD_BACKEND => '销售方推送',
            LeaseBill::ORDER_METHOD_AUTO => '自动推送',
        ];

        $receivableTypes = [
            'sales_wx' => '微信',
            'sales_alipay' => '支付宝支付',
            'sales_cash' => '现金',
            'sales_others' => '其他',
            'sales_pos' => 'POS机',
            'sales_remit' => '线下汇款',
        ];

        $payments = $em->getRepository('SandboxApiBundle:Payment\Payment')->findAll();
        $payChannels = array();
        foreach ($payments as $payment) {
            $payChannels[$payment->getChannel()] = $payment->getName();
        }

        // set sheet body
        $excelBody = [];
        foreach ($bills as $bill) {
            /** @var LeaseBill $bill */
            $lease = $bill->getLease();
            /** @var Product $product */
            $product = $lease->getProduct();
            $room = $product->getRoom();
            $building = $room->getBuilding();

            $customerId = $bill->getCustomerId() ? $bill->getCustomerId() : $lease->getLesseeCustomer();
            $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')->find($customerId);

            if (LeaseBill::STATUS_UNPAID == $bill->getStatus()) {
                $status = '未付款';
                $paymentMethod = '';
                $payChannel = '';
                $serviceBillAmount = '';
                $paymentDate = '';
                $settlementAmount = '';
            } else {
                $status = '已付款';
                $paymentMethod = ProductOrder::CHANNEL_SALES_OFFLINE == $bill->getPayChannel() ? '销售方收款' : '创合代收';
                if (ProductOrder::CHANNEL_SALES_OFFLINE == $bill->getPayChannel()) {
                    $receivable = $em->getRepository('SandboxApiBundle:Finance\FinanceReceivables')
                        ->findOneBy([
                            'orderNumber' => $bill->getSerialNumber(),
                        ]);
                    $payChannel = !is_null($receivable) ? $receivableTypes[$receivable->getPayChannel()] : '';
                } else {
                    $payChannel = $payChannels[$bill->getPayChannel()];
                }
                $serviceBill = $em->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
                    ->findOneBy(['orderNumber' => $bill->getSerialNumber()]);
                $serviceBillAmount = !is_null($serviceBill) ? $serviceBill->getAmount() : '';
                $paymentDate = $bill->getPaymentDate()->format('Y-m-d H:i:s');
                $settlementAmount = $bill->getRevisedAmount() - $serviceBillAmount;
            }

            $body = array(
                'building_name' => $building->getName(),
                'room_name' => $room->getName(),
                'order_type' => '长租账单',
                'serial_number' => $bill->getSerialNumber(),
                'lease_serial_number' => $lease->getSerialNumber(),
                'bill_name' => $bill->getName(),
                'customer' => $customer ? $customer->getName() : '',
                'order_method' => $orderMethod[$bill->getOrderMethod()],
                'payment_method' => $paymentMethod,
                'pay_channel' => $payChannel,
                'base_price' => '',
                'unit_price' => '',
                'price' => $bill->getAmount(),
                'discount_price' => $bill->getRevisedAmount(),
                'refund_amount' => '',
                'poundage' => $serviceBillAmount,
                'settlement_amount' => $settlementAmount,
                'start_date' => $bill->getStartDate()->format('Y-m-d H:i:s'),
                'end_date' => $bill->getEndDate()->format('Y-m-d H:i:s'),
                'creation_date' => $bill->getSendDate()->format('Y-m-d H:i:s'),
                'payment_date' => $paymentDate,
                'status' => $status,
                'refundTo' => '',
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
        $phpExcelObject->getActiveSheet()->setTitle('1');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        // adding headers
        $filename = $filename.'.xls';
        $filename = urlencode($filename);

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

        return $response;
    }
}
