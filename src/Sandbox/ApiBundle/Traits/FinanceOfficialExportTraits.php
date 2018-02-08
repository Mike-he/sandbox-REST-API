<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Constants\EventOrderExport;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Sandbox\ApiBundle\Entity\Service\Service;
use Sandbox\ApiBundle\Entity\Service\ServiceOrder;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait FinanceOfficialExportTraits
{
    /**
     * @param $firstDate
     * @param $language
     * @param $events
     * @param $shortOrders
     * @param $longBills
     * @param $shopOrders
     * @param $cardOrders
     *
     * @return mixed
     */
    public function getFinanceSummaryExport(
        $firstDate,
        $language,
        $events,
        $shortOrders,
        $longBills,
        $shopOrders,
        $topUpOrders,
        $cardOrders,
        $serviceOrders
    ) {
        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Finance Summary');

        $headers = $this->getOfficialExcelHeaders($language);

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

        if (!is_null($shopOrders) && !empty($shopOrders)) {
            $shopBody = $this->setShopOrderArray(
                $shopOrders,
                $language
            );

            $phpExcelObject->setActiveSheetIndex(0)->fromArray($shopBody, ' ', "A$row");
            $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 3;
        }

        if (!is_null($topUpOrders) && !empty($topUpOrders)) {
            $topUpBody = $this->setTopUpOrderArray(
                $topUpOrders,
                $language
            );

            $phpExcelObject->setActiveSheetIndex(0)->fromArray($topUpBody, ' ', "A$row");
            $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 3;
        }

        if (!is_null($cardOrders) && !empty($cardOrders)) {
            $cardOrderBody = $this->setCardOrderArray(
                $cardOrders,
                $language
            );

            $phpExcelObject->setActiveSheetIndex(0)->fromArray($cardOrderBody, ' ', "A$row");
            $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 3;
        }

        if (!is_null($serviceOrders) && !empty($serviceOrders)) {
            $serviceOrderBody = $this->setServiceOrderArray(
                $serviceOrders,
                $language
            );

            $phpExcelObject->setActiveSheetIndex(0)->fromArray($serviceOrderBody, ' ', "A$row");
        }

        $phpExcelObject->getActiveSheet()->getStyle('A1:V1')->getFont()->setBold(true);
        $phpExcelObject->getActiveSheet()->getStyle('C2:C'.$phpExcelObject->getActiveSheet()->getHighestRow())
            ->getAlignment()->setWrapText(true);
        $phpExcelObject->getActiveSheet()->getStyle('F2:F'.$phpExcelObject->getActiveSheet()->getHighestRow())
            ->getAlignment()->setWrapText(true);
        $phpExcelObject->getActiveSheet()->getStyle('G2:G'.$phpExcelObject->getActiveSheet()->getHighestRow())
            ->getAlignment()->setWrapText(true);

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
            ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.'own',
            array(),
            null,
            $language
        );

        foreach ($events as $event) {
            $companyId = $event->getEvent()->getSalesCompanyId();

            if (is_null($companyId)) {
                $companyName = null;
            } else {
                $company = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->find($companyId);
                $companyName = $company ? $company->getName() : null;
            }

            $buildingId = $event->getEvent()->getBuildingId();

            if (is_null($buildingId)) {
                $buildingName = null;
            } else {
                $building = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);
                $buildingName = $building ? $building->getName() : null;
            }

            $orderNumber = $event->getOrderNumber();

            $productName = $event->getEvent()->getName();

            $userId = $event->getUserId();

            $basePrice = $event->getPrice();

            $unit = null;

            $price = $basePrice;

            $actualAmount = $price;

            $income = $actualAmount - $actualAmount * $event->getServiceFee() / 100;

            $startTime = $event->getEvent()->getEventStartDate()->format('Y-m-d H:i:s');

            $endTime = $event->getEvent()->getEventEndDate()->format('Y-m-d H:i:s');

            $creationDate = $event->getCreationDate()->format('Y-m-d H:i:s');

            $payDate = $event->getPaymentDate()->format('Y-m-d H:i:s');

            $status = $this->get('translator')->trans(
                EventOrderExport::TRANS_EVENT_ORDER_STATUS.$event->getStatus(),
                array(),
                null,
                $language
            );

            $paymentChannel = $event->getPayChannel();

            if (!is_null($paymentChannel)) {
                $paymentChannel = $this->get('translator')->trans(
                    ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$paymentChannel,
                    array(),
                    null,
                    $language
                );
            }

            $body = $this->getExportBody(
                $companyName,
                $collection,
                $buildingName,
                $orderCategory,
                $orderNumber,
                $productName,
                $roomType,
                $userId,
                $basePrice,
                $unit,
                $price,
                $actualAmount,
                null,
                $income,
                $commission,
                $startTime,
                $endTime,
                $creationDate,
                $payDate,
                $status,
                null,
                $paymentChannel,
                $orderType,
                null
            );

            $eventBody[] = $body;
        }

        return $eventBody;
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
            /** @var ProductOrder $order */
            $productId = $order->getProductId();
            $product = $this->getDoctrine()->getRepository('SandboxApiBundle:Product\Product')->find($productId);
            $building = $product->getRoom()->getBuilding();
            $company = $building->getCompany();
            $companyName = $company->getName();
            $buildingName = $building->getName();

            $orderNumber = $order->getOrderNumber();

            $productInfoObject = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Order\ProductOrderInfo')
                ->findOneBy(['order' => $order]);
            $productInfo = json_decode($productInfoObject->getProductInfo(), true);
            $productName = $productInfo['room']['city']['name'].
                $productInfo['room']['building']['name'].
                $productInfo['room']['number'];

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
                if (SalesCompanyServiceInfos::COLLECTION_METHOD_SALES == $method) {
                    $collection = $companyName;
                }
            }

            $userId = $order->getUserId();

            $basePrice = $order->getBasePrice();

            $unit = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_UNIT.$order->getUnitPrice(),
                array(),
                null,
                $language
            );

            $price = $order->getPrice();

            $actualAmount = $order->getDiscountPrice();

            $refundAmount = $order->getActualRefundAmount();

            $income = $actualAmount - $actualAmount * $order->getServiceFee() / 100;

            $startTime = $order->getStartDate()->format('Y-m-d H:i:s');

            $endTime = $order->getEndDate()->format('Y-m-d H:i:s');

            $creationDate = $order->getCreationDate()->format('Y-m-d H:i:s');

            $payDate = $order->getPaymentDate()->format('Y-m-d H:i:s');

            $refundDate = !is_null($order->getRefundProcessedDate()) ? $order->getRefundProcessedDate()->format('Y-m-d H:i:s') : '';

            $status = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$order->getStatus(),
                array(),
                null,
                $language
            );

            $paymentChannel = $order->getPayChannel();
            $refundChannel = $order->getRefundTo();

            if (!is_null($paymentChannel)) {
                $paymentChannel = $this->get('translator')->trans(
                    ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$paymentChannel,
                    array(),
                    null,
                    $language
                );

                if (ProductOrder::STATUS_CANCELLED == $order->getStatus()) {
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

            $orderType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.$order->getType(),
                array(),
                null,
                $language
            );

            $body = $this->getExportBody(
                $companyName,
                $collection,
                $buildingName,
                $orderCategory,
                $orderNumber,
                $productName,
                $roomType,
                $userId,
                $basePrice,
                $unit,
                $price,
                $actualAmount,
                $refundAmount,
                $income,
                $commission,
                $startTime,
                $endTime,
                $creationDate,
                $payDate,
                $status,
                $refundChannel,
                $paymentChannel,
                $orderType,
                $refundDate
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

            $productName = $city->getName().
                $buildingName.
                $room->getNumber();

            $roomType = $room->getType();
            $roomType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$roomType,
                array(),
                null,
                $language
            );

            $collection = $companyName;

            $userId = $lease->getLesseeCustomer();

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

            $paymentChannel = $longBill->getPayChannel();

            if (!is_null($paymentChannel)) {
                $paymentChannel = $this->get('translator')->trans(
                    ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$paymentChannel,
                    array(),
                    null,
                    $language
                );
            }

            $body = $this->getExportBody(
                $companyName,
                $collection,
                $buildingName,
                $orderCategory,
                $orderNumber,
                $productName,
                $roomType,
                $userId,
                $basePrice,
                $unit,
                $price,
                $actualAmount,
                null,
                $income,
                $commission,
                $startTime,
                $endTime,
                $creationDate,
                $payDate,
                $status,
                null,
                $paymentChannel,
                $orderType,
                null
            );

            $longBody[] = $body;
        }

        return $longBody;
    }

    /**
     * @param $shopOrders
     * @param $language
     *
     * @return array
     */
    private function setShopOrderArray(
        $shopOrders,
        $language
    ) {
        $shopBody = array();

        $collection = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_PROFILE_SANDBOX,
            array(),
            null,
            $language
        );

        $orderCategory = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_PROFILE_SHOP_ORDER,
            array(),
            null,
            $language
        );

        foreach ($shopOrders as $shopOrder) {
            /** @var ShopOrder $shopOrder */
            $building = $shopOrder->getShop()->getBuilding();
            $company = $building->getCompany();
            $companyName = $company->getName();
            $buildingName = $building->getName();
            $orderNumber = $shopOrder->getOrderNumber();

            $shopOrderProducts = $this->getContainer()
                ->get('doctrine')
                ->getRepository('SandboxApiBundle:Shop\ShopOrderProduct')
                ->findBy(array(
                    'order' => $shopOrder,
                ));

            $productName = '';
            $productType = '';
            foreach ($shopOrderProducts as $product) {
                $productJson = $product->getShopProductInfo();
                $productArray = json_decode($productJson, true);

                $productName .= $productArray['name']."\n";
                $productType .= $productArray['menu']['name']."\n";
            }

            $actualAmount = $shopOrder->getPrice();
            $refundAmount = $shopOrder->getRefundAmount();

            $payDate = $shopOrder->getPaymentDate()->format('Y-m-d H:i:s');

            $status = $this->get('translator')->trans(
                ProductOrderExport::TRANS_SHOP_ORDER_STATUS.$shopOrder->getStatus(),
                array(),
                null,
                $language
            );

            $orderType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.'own',
                array(),
                null,
                $language
            );

            $paymentChannel = $shopOrder->getPayChannel();
            $refundChannel = null;

            if (!is_null($paymentChannel)) {
                $paymentChannel = $this->get('translator')->trans(
                    ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$paymentChannel,
                    array(),
                    null,
                    $language
                );

                if (ShopOrder::STATUS_REFUNDED == $shopOrder->getStatus()) {
                    $refundChannel = $refundChannel = ProductOrder::REFUND_TO_ORIGIN;
                    $refundChannel = $this->get('translator')->trans(
                        ProductOrderExport::TRANS_PRODUCT_ORDER_REFUND_TO.$refundChannel,
                        array(),
                        null,
                        $language
                    );
                }
            }

            $creationDate = $shopOrder->getCreationDate()->format('Y-m-d H:i:s');

            $refundDate = !is_null($shopOrder->getRefundProcessedDate()) ? $shopOrder->getRefundProcessedDate()->format('Y-m-d H:i:s') : '';

            $body = $this->getExportBody(
                $companyName,
                $collection,
                $buildingName,
                $orderCategory,
                $orderNumber,
                trim($productName),
                trim($productType),
                null,
                null,
                null,
                null,
                $actualAmount,
                $refundAmount,
                $actualAmount,
                null,
                null,
                null,
                $creationDate,
                $payDate,
                $status,
                $refundChannel,
                $paymentChannel,
                $orderType,
                $refundDate
            );

            $shopBody[] = $body;
        }

        return $shopBody;
    }

    /**
     * @param $topUpOrders
     * @param $language
     *
     * @return array
     */
    private function setTopUpOrderArray(
        $topUpOrders,
        $language
    ) {
        $topUpBody = array();

        $collection = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_PROFILE_SANDBOX,
            array(),
            null,
            $language
        );

        $orderCategory = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_PROFILE_TOP_UP,
            array(),
            null,
            $language
        );

        foreach ($topUpOrders as $order) {
            $orderNumber = $order->getOrderNumber();
            $actualAmount = $order->getPrice();
            $creationDate = $order->getCreationDate();
            $payDate = $order->getPaymentDate();

            $paymentChannel = $order->getPayChannel();

            if (!is_null($paymentChannel)) {
                $paymentChannel = $this->get('translator')->trans(
                    ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$paymentChannel,
                    array(),
                    null,
                    $language
                );
            }

            $body = $this->getExportBody(
                $collection,
                $collection,
                null,
                $orderCategory,
                $orderNumber,
                null,
                null,
                null,
                null,
                null,
                null,
                $actualAmount,
                null,
                $actualAmount,
                null,
                null,
                $creationDate,
                $payDate,
                null,
                null,
                $paymentChannel,
                null,
                null,
                null
            );

            $topUpBody[] = $body;
        }

        return $topUpBody;
    }

    /**
     * @param $cardOrders
     * @param $language
     *
     * @return array
     */
    private function setCardOrderArray(
        $cardOrders,
        $language
    ) {
        $cardOrderBody = array();

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

        foreach ($cardOrders as $cardOrder) {
            $companyId = $cardOrder->getCard()->getCompanyId();

            if (is_null($companyId)) {
                $companyName = '';
            } else {
                $company = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->find($companyId);
                $companyName = $company ? $company->getName() : null;
            }

            $doors = $this->get('doctrine.orm.entity_manager')
                ->getRepository('SandboxApiBundle:User\UserGroupDoors')
                ->findBy(array(
                    'card' => $cardOrder->getCard(),
                ));

            $buildingName = '';
            foreach ($doors as $door) {
                $building = $this->get('doctrine.orm.entity_manager')
                    ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->find($door->getBuilding());

                $buildingName .= $building->getName()."\n";
            }
            $buildingName = trim($buildingName);

            $orderNumber = $cardOrder->getOrderNumber();
            $productName = $cardOrder->getCard()->getName().$cardOrder->getSpecification();
            $userId = $cardOrder->getUser();
            $basePrice = $cardOrder->getPrice();

            $unit = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_UNIT.$cardOrder->getUnitPrice(),
                array(),
                null,
                $language
            );
            $unit = $cardOrder->getValidPeriod().$unit;
            $actualAmount = $basePrice;
            $income = $actualAmount;
            $commission = $cardOrder->getServiceFee();
            $creationDate = $cardOrder->getCreationDate()->format('Y-m-d H:i:s');
            $payDate = $cardOrder->getPaymentDate()->format('Y-m-d H:i:s');

            $status = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.'completed',
                array(),
                null,
                $language
            );

            $paymentChannel = $cardOrder->getPayChannel();

            if (!is_null($paymentChannel)) {
                $paymentChannel = $this->get('translator')->trans(
                    ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$paymentChannel,
                    array(),
                    null,
                    $language
                );
            }

            $orderType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.'own',
                array(),
                null,
                $language
            );

            $body = $this->getExportBody(
                $companyName,
                $collection,
                $buildingName,
                $orderCategory,
                $orderNumber,
                $productName,
                null,
                $userId,
                $basePrice,
                $unit,
                $basePrice,
                $actualAmount,
                null,
                $income,
                $commission,
                null,
                $creationDate,
                $payDate,
                $status,
                null,
                null,
                $paymentChannel,
                $orderType,
                null
            );

            $cardOrderBody[] = $body;
        }

        return $cardOrderBody;
    }

    /**
     * @param $serviceOrders
     * @param $language
     *
     * @return array
     */
    private function setServiceOrderArray(
        $serviceOrders,
        $language
    ) {
        $serviceOrderBody = [];

        $collection = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_PROFILE_SANDBOX,
            array(),
            null,
            $language
        );

        $orderCategory = $this->get('translator')->trans(
            ProductOrderExport::TRANS_CLIENT_SERVICE_ORDER,
            array(),
            null,
            $language
        );

        foreach ($serviceOrders as $serviceOrder) {
            /** @var ServiceOrder $serviceOrder */
            $companyId = $serviceOrder->getCompanyId();

            if (is_null($companyId)) {
                $companyName = '';
            } else {
                $company = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->find($companyId);
                $companyName = $company ? $company->getName() : null;
            }

            $orderNumber = $serviceOrder->getOrderNumber();

            $service = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Service\Service')
                ->find($serviceOrder->getServiceId());
            /** @var Service $service */
            $serviceName = $service->getName();

            $serviceType = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Service\ServiceTypes')
                ->findOneBy([
                    'key' => $service->getType(),
                ]);
            $serviceTypeName = $serviceType->getName();

            $userId = $serviceOrder->getUserId();

            $price = $serviceOrder->getPrice();

            $serviceStartDate = $service->getServiceStartDate()->format('Y-m-d H:i:s');
            $serviceEndDate = $service->getServiceEndDate()->format('Y-m-d H:i:s');
            $creationDate = $serviceOrder->getCreationDate()->format('Y-m-d H:i:s');
            $paymentDate = $serviceOrder->getPaymentDate()->format('Y-m-d H:i:s');

            $cancelDate = !is_null($serviceOrder->getCancelledDate()) ? $serviceOrder->getCancelledDate()->format('Y-m-d H:i:s') : '';

            $status = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$serviceOrder->getStatus(),
                array(),
                null,
                $language
            );

            $paymentChannel = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$serviceOrder->getPayChannel(),
                array(),
                null,
                $language
            );

            $body = $this->getExportBody(
                $companyName,
                $collection,
                null,
                $orderCategory,
                $orderNumber,
                $serviceName,
                $serviceTypeName,
                $userId,
                $price,
                '1次',
                $price,
                $price,
                null,
                $price,
                null,
                $serviceStartDate,
                $serviceEndDate,
                $creationDate,
                $paymentDate,
                $status,
                null,
                $paymentChannel,
                null,
                $cancelDate
            );

            $serviceOrderBody[] = $body;
        }

        return $serviceOrderBody;
    }

    /**
     * @return array
     */
    private function getOfficialExcelHeaders(
        $language
    ) {
        return [
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_COMPANY_NAME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_COLLECTION_METHOD, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_BUILDING_NAME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_CATEGORY, array(), null, $language),
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
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_COMMISSION, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_START_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_END_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_PAYMENT_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_STATUS, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_REFUND_TO, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_PAYMENT_CHANNEL, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_TYPE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_REFUND_DATE, array(), null, $language),
        ];
    }

    /**
     * @param $companyName
     * @param $collection
     * @param $buildingName
     * @param $orderCategory
     * @param $orderNumber
     * @param $productName
     * @param $roomType
     * @param $userId
     * @param $basePrice
     * @param $unit
     * @param $price
     * @param $actualAmount
     * @param $refundAmount
     * @param $income
     * @param $commission
     * @param $startTime
     * @param $endTime
     * @param $creationDate
     * @param $payDate
     * @param $status
     * @param $refundTo
     * @param $payChannel
     * @param $orderType
     *
     * @return array
     */
    private function getExportBody(
        $companyName,
        $collection,
        $buildingName,
        $orderCategory,
        $orderNumber,
        $productName,
        $roomType,
        $userId,
        $basePrice,
        $unit,
        $price,
        $actualAmount,
        $refundAmount,
        $income,
        $commission,
        $startTime,
        $endTime,
        $creationDate,
        $payDate,
        $status,
        $refundTo,
        $payChannel,
        $orderType,
        $refundDate
    ) {
        // set excel body
        $body = array(
            ProductOrderExport::COMPANY_NAME => $companyName,
            ProductOrderExport::COLLECTION_METHOD => $collection,
            ProductOrderExport::BUILDING_NAME => $buildingName,
            ProductOrderExport::ORDER_CATEGORY => $orderCategory,
            ProductOrderExport::ORDER_NUMBER => $orderNumber,
            ProductOrderExport::PRODUCT_NAME => $productName,
            ProductOrderExport::ROOM_TYPE => $roomType,
            ProductOrderExport::USER_ID => $userId,
            ProductOrderExport::BASE_PRICE => $basePrice,
            ProductOrderExport::UNIT_PRICE => $unit,
            ProductOrderExport::AMOUNT => $price,
            ProductOrderExport::DISCOUNT_PRICE => $actualAmount,
            ProductOrderExport::REFUND_AMOUNT => $refundAmount,
            ProductOrderExport::ACTUAL_AMOUNT => $income,
            ProductOrderExport::COMMISSION => $commission,
            ProductOrderExport::START_TIME => $startTime,
            ProductOrderExport::END_TIME => $endTime,
            ProductOrderExport::CREATION_DATE => $creationDate,
            ProductOrderExport::PAYMENT_TIME => $payDate,
            ProductOrderExport::ORDER_STATUS => $status,
            ProductOrderExport::REFUND_TO => $refundTo,
            ProductOrderExport::PAYMENT_CHANNEL => $payChannel,
            ProductOrderExport::ORDER_TYPE => $orderType,
            ProductOrderExport::REFUND_DATE => $refundDate,
        );

        return $body;
    }
}
