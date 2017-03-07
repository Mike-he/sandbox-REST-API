<?php

namespace Sandbox\ApiBundle\Controller\Shop;

use Sandbox\AdminShopApiBundle\Controller\ShopRestController;
use Sandbox\ApiBundle\Constants\ShopOrderExport;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Shop Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ShopController extends ShopRestController
{
    /**
     * @param $id
     *
     * @return Shop $shop
     */
    public function findShopById(
        $id
    ) {
        $shop = $this->getRepo('Shop\Shop')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($shop, self::NOT_FOUND_MESSAGE);

        return $shop;
    }

    /**
     * @param array  $orders
     * @param string $language
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws \PHPExcel_Exception
     */
    public function getShopOrderExport(
        $orders,
        $language,
        $start,
        $end
    ) {
        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Sandbox Shop Orders');

        $excelBody = [];
        $orderCount = 0;
        $total = 0;
        $refundOrderCount = 0;
        $totalRefund = 0;
        $productArray = [];
        $amount = 0;
        $price = 0;

        // set excel body
        foreach ($orders as $order) {
            $amountString = '';
            $menuString = '';
            $productString = '';

            $orderNumber = $order->getOrderNumber();
            $shopName = $order->getShop()->getName();
            $buildingName = $order->getShop()->getBuilding()->getName();

            $orderTime = null;
            $paymentDate = $order->getPaymentDate();

            if (!is_null($paymentDate)) {
                $orderTime = $order->getPaymentDate()->format('Y-m-d H:i:s');
            }

            // set status
            $statusKey = $order->getStatus();
            $status = $this->get('translator')->trans(
                ShopOrderExport::TRANS_SHOP_ORDER_STATUS.$statusKey,
                array(),
                null,
                $language
            );

            $orderProducts = $order->getShopOrderProducts();
            foreach ($orderProducts as $orderProduct) {
                $shopProductInfo = json_decode($orderProduct->getShopProductInfo(), true);

                $menuName = $shopProductInfo['menu']['name'];
                $productName = $shopProductInfo['name'];

                $orderProductSpecs = $orderProduct->getShopOrderProductSpecs();
                foreach ($orderProductSpecs as $orderProductSpec) {
                    $shopProductSpecInfo = json_decode($orderProductSpec->getShopProductSpecInfo(), true);

                    if ($shopProductSpecInfo['spec']['has_inventory']) {
                        $orderProductSpecItem = $this->getRepo('Shop\ShopOrderProductSpecItem')
                            ->findOneBySpecId($orderProductSpec->getId());

                        if (is_null($orderProductSpecItem)) {
                            continue;
                        }

                        $amount = $orderProductSpecItem->getAmount();
                        $itemInfo = json_decode($orderProductSpecItem->getShopProductSpecItemInfo(), true);

                        $price = $amount * $itemInfo['price'];

                        $amountString .= $amount."\n";
                    }
                }

                if ($statusKey == ShopOrder::STATUS_COMPLETED) {
                    $productId = $shopProductInfo['id'];

                    if (array_key_exists("$productId", $productArray)) {
                        $productArray[$productId]['amount'] += $amount;
                        $productArray[$productId]['price'] += $price;
                    } else {
                        $productArray["$productId"] = [
                            'menu_name' => $menuName,
                            'name' => $productName,
                            'amount' => $amount,
                            'price' => $price,
                        ];
                    }
                }

                $menuString .= $menuName."\n";
                $productString .= $productName."\n";
            }

            $user = $this->getRepo('User\User')->find($order->getUserId());

            $phone = null;
            $email = null;
            if (!is_null($user)) {
                $phone = $user->getPhone();
                $email = $user->getEmail();
            }

            $price = $order->getPrice();
            $refund = $order->getRefundAmount();

            if ($statusKey == ShopOrder::STATUS_COMPLETED) {
                ++$orderCount;

                if ($order->IsUnoriginal()) {
                    $oldOrder = $order->getLinkedOrder();
                    $oldPrice = $oldOrder->getPrice();

                    if ($oldPrice >= $price) {
                        $total += $price;
                    } else {
                        $total += $oldPrice;
                    }
                } else {
                    $total += $price;
                }
            } elseif ($statusKey == ShopOrder::STATUS_REFUNDED) {
                ++$refundOrderCount;

                $totalRefund += $refund;
            }

            $paymentChannel = $order->getPayChannel();
            if (!is_null($paymentChannel) && !empty($paymentChannel)) {
                $paymentChannel = $this->get('translator')->trans(
                    ShopOrderExport::TRANS_SHOP_ORDER_CHANNEL.$paymentChannel,
                    array(),
                    null,
                    $language
                );
            }

            // set excel body
            $body = array(
                ShopOrderExport::BUILDING_NAME => $buildingName,
                ShopOrderExport::ORDER_NUMBER => $orderNumber,
                ShopOrderExport::SHOP_NAME => $shopName,
                ShopOrderExport::ORDER_TIME => $orderTime,
                ShopOrderExport::PRODUCT_NAME => $productString,
                ShopOrderExport::PRODUCT_TYPE => $menuString,
                ShopOrderExport::USER_PHONE => $phone,
                ShopOrderExport::USER_EMAIL => $email,
                ShopOrderExport::TOTAL_AMOUNT => $amountString,
                ShopOrderExport::TOTAL_PRICE => $price,
                ShopOrderExport::TOTAL_REFUND => $refund,
                ShopOrderExport::ORDER_STATUS => $status,
                ShopOrderExport::PAY_CHANNEL => $paymentChannel,
            );

            $excelBody[] = $body;
        }

        $headers = [
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_BUILDING_NAME, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_ORDER_NO, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_SHOP, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_ORDER_TIME, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_PRODUCT_NAME, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_PRODUCT_TYPE, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_USER_PHONE, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_USER_EMAIL, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_AMOUNT, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_TOTAL_PRICE, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_REFUND, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_STATUS, array(), null, $language),
            $this->get('translator')->trans(ShopOrderExport::TRANS_SHOP_ORDER_HEADER_CHANNEL, array(), null, $language),
        ];

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($excelBody, null, 'A2');

        $phpExcelObject->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);
        $phpExcelObject->getActiveSheet()->getStyle('D2:D'.$phpExcelObject->getActiveSheet()->getHighestRow())
            ->getAlignment()->setWrapText(true);
        $phpExcelObject->getActiveSheet()->getStyle('E2:E'.$phpExcelObject->getActiveSheet()->getHighestRow())
            ->getAlignment()->setWrapText(true);
        $phpExcelObject->getActiveSheet()->getStyle('G2:G'.$phpExcelObject->getActiveSheet()->getHighestRow())
            ->getAlignment()->setWrapText(true);

        $phpExcelObject->getActiveSheet()->insertNewRowBefore($phpExcelObject->getActiveSheet()->getHighestRow() + 1, 1);
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '统计-----');
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '日期');
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(1, $phpExcelObject->getActiveSheet()->getHighestRow(), $start.' - '.$end);

        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '总订单数量');
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(1, $phpExcelObject->getActiveSheet()->getHighestRow(), $orderCount.'笔');

        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '总金销售额');
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(1, $phpExcelObject->getActiveSheet()->getHighestRow(), round($total, 2).'元');

        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '退款订单数量');
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(1, $phpExcelObject->getActiveSheet()->getHighestRow(), $refundOrderCount.'笔');

        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '退款总金额');
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(1, $phpExcelObject->getActiveSheet()->getHighestRow(), $totalRefund.'元');

        $phpExcelObject->getActiveSheet()->insertNewRowBefore($phpExcelObject->getActiveSheet()->getHighestRow() + 1, 1);
        $phpExcelObject->getActiveSheet()->setCellValueByColumnAndRow(0, $phpExcelObject->getActiveSheet()->getHighestRow() + 1, '销售商品数表');

        $bodyArray = [];

        foreach ($productArray as $item) {
            $body = array(
                'menu_name' => $item['menu_name'],
                'name' => $item['name'],
                'amount' => $item['amount'].'个',
                'price' => round($item['price'], 2).'元',
            );

            $bodyArray[] = $body;
        }

        $phpExcelObject->setActiveSheetIndex(0)->fromArray($bodyArray, null, 'A'.($phpExcelObject->getActiveSheet()->getHighestRow() + 1));

        //set column dimension
        for ($col = ord('a'); $col <= ord('o'); ++$col) {
            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension(chr($col))->setAutoSize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('Shop Orders');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        $date = new \DateTime('now');
        $stringDate = $date->format('Y-m-d');

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'shop_orders_'.$stringDate.'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}
