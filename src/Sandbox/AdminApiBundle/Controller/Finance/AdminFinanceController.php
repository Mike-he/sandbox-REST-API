<?php

namespace Sandbox\AdminApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class AdminFinanceController.
 */
class AdminFinanceController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/invoice/categories")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getFinanceInvoiceCategoryAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $categories = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->getAdminInvoiceCategories();

        return new View($categories);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="year",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="month",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
     * )
     *
     * @Route("/finance/export")
     * @Method({"GET"})
     *
     * @return View
     */
    public function exportOrderSumAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //authenticate with web browser cookie
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();
        $this->checkAdminFinancePermission(
            AdminPermission::OP_LEVEL_VIEW,
            $adminId,
            AdminPermission::PERMISSION_PLATFORM_OFFICIAL
        );

        $channels = array(
            'wx',
            'alipay',
            'upacp',
            'account',
            'offline',
            'wx_pub',
        );

        $year = $paramFetcher->get('year');
        $month = $paramFetcher->get('month');

        if (is_null($year) ||
            is_null($month) ||
            empty($year) ||
            empty($month)
        ) {
            return new View();
        }

        $startString = $year.'-'.$month.'-01';
        $startDate = new \DateTime($startString);
        $startDate->setTime(0, 0, 0);

        $endString = $startDate->format('Y-m-t');
        $endDate = new \DateTime($endString);
        $endDate->setTime(23, 59, 59);

        $roomTypes = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomTypes')
            ->findAll();

        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getRoomBuildingWithOrders(
                $startDate,
                $endDate
            );

        $shops = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Shop\ShopOrder')
            ->getShopWithOrders(
                $startDate,
                $endDate
            );

        $data = array();

        foreach ($channels as $channel) {
            $buildingArray = array();
            $shopArray = array();

            foreach ($buildings as $building) {
                $cityName = $building->getCity()->getName();
                $buildingName = $cityName.$building->getName();
                $buildingId = $building->getId();

                $typeArray = array();

                foreach ($roomTypes as $roomType) {
                    $typeName = $roomType->getName();

                    $completedSum = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Order\ProductOrder')
                        ->sumOrdersByType(
                            $channel,
                            $buildingId,
                            $typeName,
                            $startDate,
                            $endDate,
                            ProductOrder::STATUS_COMPLETED
                        );

                    if (is_null($completedSum)) {
                        $completedSum = '0.00';
                    }

                    $paidSum = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Order\ProductOrder')
                        ->sumOrdersByType(
                            $channel,
                            $buildingId,
                            $typeName,
                            $startDate,
                            $endDate,
                            ProductOrder::STATUS_PAID
                        );

                    if (is_null($paidSum)) {
                        $paidSum = '0.00';
                    }

                    $sumArray = array(
                        'type_name' => $typeName,
                        'completed' => $completedSum,
                        'paid' => $paidSum,
                    );

                    array_push($typeArray, $sumArray);
                }

                $buildingInfo = array(
                    'building_name' => $buildingName,
                    'room_type' => $typeArray,
                );

                array_push($buildingArray, $buildingInfo);
            }

            foreach ($shops as $shop) {
                $paid = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Shop\ShopOrder')
                    ->getOrderPaidSums(
                        $shop,
                        $channel,
                        $startDate,
                        $endDate
                    );

                $refund = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Shop\ShopOrder')
                    ->getOrderRefundSums(
                        $shop,
                        $channel,
                        $startDate,
                        $endDate
                    );

                $sums = array(
                    'shop_name' => $shop->getName(),
                    'completed' => $paid,
                    'refund' => $refund,
                );

                array_push($shopArray, $sums);
            }

            $channelArray = array(
                'channel_name' => $channel,
                'building' => $buildingArray,
                'shop' => $shopArray,
            );

            array_push($data, $channelArray);
        }

        return $this->getOrderSumExport(
            $data,
            $startString,
            $endString
        );
    }

    /**
     * @param $url
     *
     * @return mixed|void
     */
    private function getBalanceInfo(
        $url
    ) {
        // init curl
        $ch = curl_init($url);

        $response = $this->callAPI(
            $ch,
            'GET'
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $dataArray
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws \PHPExcel_Exception
     */
    private function getOrderSumExport(
        $dataArray,
        $startString,
        $endString
    ) {
        $title = $startString.'_'.$endString.'_Sandbox3_Financial_Report';

        $phpExcelObject = new \PHPExcel();
        $x = 0;

        $globals = $this->getGlobals();
        $url = $globals['crm_api_url']."/admin/dashboard/balance/export?startDate=$startString&endDate=$endString&channel=";

        foreach ($dataArray as $data) {
            if ($x > 0) {
                $phpExcelObject->createSheet($x);
                $phpExcelObject->setActiveSheetIndex($x);
            }
            ++$x;

            $channel = $this->get('translator')
                ->trans(
                    ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$data['channel_name'],
                    array(),
                    null,
                    'zh'
                );

            $phpExcelObject->getActiveSheet()->setTitle($channel);
            $phpExcelObject->getActiveSheet()->setCellValue('A1', '支付渠道');
            $phpExcelObject->getActiveSheet()->setCellValue('B1', $channel);
            $phpExcelObject->getActiveSheet()
                ->getStyle('A1:B1')
                ->getFill()
                ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('ADD8E6');

            $roomCompleted = $this->setRoomTables(
                $phpExcelObject,
                $data,
                '已完成订单',
                'FFC0CB',
                'completed'
            );

            $roomPaid = $this->setRoomTables(
                $phpExcelObject,
                $data,
                '已付款订单',
                '90EE90',
                'paid'
            );

            $shopSum = $this->setShopTables(
                $phpExcelObject,
                $data,
                '店铺订单',
                'FFFF00'
            );

            $result = $this->getBalanceInfo($url.$data['channel_name']);

            $this->setTotalTables(
                $phpExcelObject,
                $result,
                $roomCompleted,
                $roomPaid,
                $shopSum
            );

            $phpExcelObject->getActiveSheet()->getSheetView()->setZoomScale(120);
            $phpExcelObject->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        }

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $title.'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @param \PHPExcel() $phpExcelObject
     * @param $data
     */
    private function setTotalTables(
        $phpExcelObject,
        $data,
        $roomCompleted,
        $roomPaid,
        $shopSum
    ) {
        $currentRow = $phpExcelObject->getActiveSheet()->getHighestRow();
        $firstRow = $currentRow + 3;
        $nextRow = $firstRow + 1;
        $lastRow = $nextRow + 1;

        $roomCompletedTaxFree = round($roomCompleted / 1.06, 2);
        $roomPaidTaxFree = round($roomPaid / 1.06, 2);
        $shopSumTaxFree = round($shopSum / 1.06, 2);
        $topUp = round($data['top_up'], 2);
        $topUpTaxFree = round($topUp / 1.06, 2);
        $sum = $roomCompleted + $roomPaid + $shopSum + $topUp;
        $sumTaxFree = $roomCompletedTaxFree + $roomPaidTaxFree + $shopSumTaxFree + $topUpTaxFree;

        $phpExcelObject->getActiveSheet()->setCellValue("B$firstRow", '已完成房间订单');
        $phpExcelObject->getActiveSheet()->setCellValue("B$nextRow", $roomCompleted);
        $phpExcelObject->getActiveSheet()->setCellValue("B$lastRow", $roomCompletedTaxFree);

        $phpExcelObject->getActiveSheet()->setCellValue("C$firstRow", '已付款房间订单');
        $phpExcelObject->getActiveSheet()->setCellValue("C$nextRow", $roomPaid);
        $phpExcelObject->getActiveSheet()->setCellValue("C$lastRow", $roomPaidTaxFree);

        $phpExcelObject->getActiveSheet()->setCellValue("D$firstRow", '店铺订单');
        $phpExcelObject->getActiveSheet()->setCellValue("D$nextRow", $shopSum);
        $phpExcelObject->getActiveSheet()->setCellValue("D$lastRow", $shopSumTaxFree);

        $phpExcelObject->getActiveSheet()->setCellValue("E$firstRow", '余额充值');
        $phpExcelObject->getActiveSheet()->setCellValue("E$nextRow", $topUp);
        $phpExcelObject->getActiveSheet()->setCellValue("E$lastRow", $topUpTaxFree);

        $phpExcelObject->getActiveSheet()->setCellValue("F$firstRow", '上月余额');
        $phpExcelObject->getActiveSheet()->setCellValue("F$nextRow", round($data['previous_total_balance'], 2));

        $phpExcelObject->getActiveSheet()->setCellValue("G$firstRow", '本月余额');
        $phpExcelObject->getActiveSheet()->setCellValue("G$nextRow", round($data['latest_total_balance'], 2));

        $phpExcelObject->getActiveSheet()->setCellValue("A$nextRow", "合计金额(含税)= $sum");
        $phpExcelObject->getActiveSheet()->setCellValue("A$lastRow", "合计金额(未税)= $sumTaxFree");

        $phpExcelObject->getActiveSheet()
            ->getStyle("B$firstRow:G$firstRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpExcelObject->getActiveSheet()
            ->getStyle("B$nextRow:G$nextRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpExcelObject->getActiveSheet()
            ->getStyle("B$lastRow:G$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$nextRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:G$firstRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$nextRow:G$nextRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$lastRow:G$lastRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
    }

    /**
     * @param \PHPExcel() $phpExcelObject
     * @param $data
     * @param $header
     * @param $color
     */
    private function setShopTables(
        $phpExcelObject,
        $data,
        $header,
        $color
    ) {
        $currentRow = $phpExcelObject->getActiveSheet()->getHighestRow();
        $firstRow = $currentRow + 3;
        $startRow = $firstRow + 1;

        $phpExcelObject->getActiveSheet()->setCellValue("A$firstRow", $header);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow")
            ->getFill()
            ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB($color);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow")
            ->getBorders()
            ->getRight()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $phpExcelObject->getActiveSheet()->setCellValue("B$firstRow", '订单价格');
        $phpExcelObject->getActiveSheet()->setCellValue("C$firstRow", '退款金额');
        $phpExcelObject->getActiveSheet()->setCellValue("D$firstRow", '最终收入');
        $phpExcelObject->getActiveSheet()->setCellValue("E$firstRow", '未税金额');

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:E$firstRow")
            ->getBorders()
            ->getTop()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:E$firstRow")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:E$firstRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:E$firstRow")
            ->getBorders()
            ->getRight()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $paidAmountSum = 0;
        $refundAmountSum = 0;
        $actualAmountSum = 0;
        $amountTaxFreeSum = 0;

        foreach ($data['shop'] as $shopItem) {
            $column = 'A';

            $name = $shopItem['shop_name'];
            $paidAmount = round($shopItem['completed'], 2);
            $paidAmountSum += $paidAmount;

            $refundAmount = round($shopItem['refund'], 2);
            $refundAmountSum += $refundAmount;

            $actualAmount = $paidAmount - $refundAmount;
            $actualAmountSum += $actualAmount;

            $amountTaxFree = round($actualAmount / 1.06, 2);
            $amountTaxFreeSum += $amountTaxFree;

            $phpExcelObject->getActiveSheet()->setCellValue($column."$startRow", $name);
            $phpExcelObject->getActiveSheet()
                ->getStyle($column."$startRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $phpExcelObject->getActiveSheet()
                ->getStyle($column."$startRow")
                ->getBorders()
                ->getRight()
                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

            ++$column;
            $phpExcelObject->getActiveSheet()->setCellValue($column."$startRow", $paidAmount);
            $phpExcelObject->getActiveSheet()
                ->getStyle($column."$startRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            ++$column;
            $phpExcelObject->getActiveSheet()->setCellValue($column."$startRow", $refundAmount);
            $phpExcelObject->getActiveSheet()
                ->getStyle($column."$startRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            ++$column;
            $phpExcelObject->getActiveSheet()->setCellValue($column."$startRow", $actualAmount);
            $phpExcelObject->getActiveSheet()
                ->getStyle($column."$startRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            ++$column;
            $phpExcelObject->getActiveSheet()->setCellValue($column."$startRow", $amountTaxFree);
            $phpExcelObject->getActiveSheet()
                ->getStyle($column."$startRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $phpExcelObject->getActiveSheet()
                ->getStyle("A$startRow:".$column."$startRow")
                ->getBorders()
                ->getRight()
                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        }

        $column = 'A';

        $lastRow = $phpExcelObject->getActiveSheet()->getHighestRow() + 1;
        $phpExcelObject->getActiveSheet()->setCellValue($column."$lastRow", '总计');
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getFont()
            ->setBold(true);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getBorders()
            ->getRight()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        ++$column;
        $phpExcelObject->getActiveSheet()->setCellValue($column."$lastRow", $paidAmountSum);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        ++$column;
        $phpExcelObject->getActiveSheet()->setCellValue($column."$lastRow", $refundAmountSum);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        ++$column;
        $phpExcelObject->getActiveSheet()->setCellValue($column."$lastRow", $actualAmountSum);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        ++$column;
        $phpExcelObject->getActiveSheet()->setCellValue($column."$lastRow", $amountTaxFreeSum);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $phpExcelObject->getActiveSheet()
            ->getStyle($column."$lastRow")
            ->getBorders()
            ->getRight()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$lastRow:".$column."$lastRow")
            ->getBorders()
            ->getTop()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$lastRow:".$column."$lastRow")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        return $actualAmountSum;
    }

    /**
     * @param \PHPExcel() $phpExcelObject
     * @param $data
     * @param $header
     * @param $color
     * @param $payStatus
     */
    private function setRoomTables(
        $phpExcelObject,
        $data,
        $header,
        $color,
        $payStatus
    ) {
        $currentRow = $phpExcelObject->getActiveSheet()->getHighestRow();
        $firstRow = $currentRow + 3;
        $secondRow = $firstRow + 1;
        $thirdRow = $secondRow + 1;
        $fourthRow = $thirdRow + 1;
        $startRow = $fourthRow;
        $total = 0;

        $phpExcelObject->getActiveSheet()->setCellValue("A$firstRow", $header);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow")
            ->getFill()
            ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB($color);

        $phpExcelObject->getActiveSheet()->setCellValue("A$thirdRow", '社区名称');
        $phpExcelObject->getActiveSheet()->mergeCells("A$firstRow:A$secondRow");

        $y = 0;

        foreach ($data['building'] as $companyItem) {
            $column = 'A';
            $companySum = 0;
            $companySumTaxFree = 0;

            foreach ($companyItem['room_type'] as $roomType) {
                ++$column;
                $nextColumn = $column;
                ++$nextColumn;

                if ($y == 0) {
                    $typeText = $this->get('translator')->trans(
                        ProductOrderExport::TRANS_ROOM_TYPE.$roomType['type_name'],
                        array(),
                        null,
                        'zh'
                    );

                    $phpExcelObject->getActiveSheet()->setCellValue($column."$secondRow", $typeText);
                    $phpExcelObject->getActiveSheet()->mergeCells($column."$secondRow:".$nextColumn."$secondRow");

                    $phpExcelObject->getActiveSheet()->setCellValue($column."$thirdRow", '实收款');
                    $phpExcelObject->getActiveSheet()->setCellValue($nextColumn."$thirdRow", '未税金额');
                }

                $amount = round($roomType[$payStatus], 2);
                $companySum += $amount;
                $amountTaxFree = round($roomType[$payStatus] / 1.06, 2);
                $companySumTaxFree += $amountTaxFree;

                $phpExcelObject->getActiveSheet()->setCellValue($column."$startRow", $amount);
                $phpExcelObject->getActiveSheet()
                    ->getStyle($column."$startRow")
                    ->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $phpExcelObject->getActiveSheet()
                    ->getStyle($column."$startRow")
                    ->getBorders()
                    ->getLeft()
                    ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

                $phpExcelObject->getActiveSheet()->setCellValue($nextColumn."$startRow", $amountTaxFree);
                $phpExcelObject->getActiveSheet()
                    ->getStyle($nextColumn."$startRow")
                    ->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                ++$column;
            }

            $y = 1;

            $currentRow = $phpExcelObject->getActiveSheet()->getHighestRow();
            $currentColumn = $phpExcelObject->getActiveSheet()->getHighestColumn($currentRow);
            ++$currentColumn;
            $afterColumn = $currentColumn;
            ++$afterColumn;

            $phpExcelObject->getActiveSheet()->setCellValue($currentColumn."$currentRow", $companySum);
            $phpExcelObject->getActiveSheet()
                ->getStyle($currentColumn."$currentRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $phpExcelObject->getActiveSheet()
                ->getStyle($currentColumn."$currentRow")
                ->getBorders()
                ->getLeft()
                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $phpExcelObject->getActiveSheet()
                ->getStyle($currentColumn."$currentRow")
                ->getBorders()
                ->getRight()
                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

            $phpExcelObject->getActiveSheet()->setCellValue($afterColumn."$currentRow", $companySumTaxFree);
            $phpExcelObject->getActiveSheet()
                ->getStyle($afterColumn."$currentRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $phpExcelObject->getActiveSheet()
                ->getStyle($afterColumn."$currentRow")
                ->getBorders()
                ->getRight()
                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

            $phpExcelObject->getActiveSheet()->setCellValue("A$currentRow", $companyItem['building_name']);
            $phpExcelObject->getActiveSheet()
                ->getStyle("A$currentRow")
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            ++$startRow;
        }

        $phpExcelObject->getActiveSheet()->setCellValue("B$firstRow", '房间类型');
        $toColumn = $phpExcelObject->getActiveSheet()->getHighestColumn($secondRow);
        $phpExcelObject->getActiveSheet()->mergeCells("B$firstRow:".$toColumn."$firstRow");

        ++$toColumn;
        $nextColumn = $toColumn;
        ++$nextColumn;

        $phpExcelObject->getActiveSheet()->setCellValue($toColumn."$firstRow", '合计');
        $phpExcelObject->getActiveSheet()->mergeCells($toColumn."$firstRow:".$nextColumn."$firstRow");

        $phpExcelObject->getActiveSheet()->setCellValue($toColumn."$secondRow", '实收款总汇');
        $phpExcelObject->getActiveSheet()->mergeCells($toColumn."$secondRow:".$toColumn."$thirdRow");

        $phpExcelObject->getActiveSheet()->setCellValue($nextColumn."$secondRow", '未税金额总汇');
        $phpExcelObject->getActiveSheet()->mergeCells($nextColumn."$secondRow:".$nextColumn."$thirdRow");

        $currentRow = $phpExcelObject->getActiveSheet()->getHighestRow() + 1;
        $phpExcelObject->getActiveSheet()->setCellValue("A$currentRow", '总计');
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$currentRow")
            ->getFont()
            ->setBold(true);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$currentRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$currentRow")
            ->getBorders()
            ->getRight()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:".$nextColumn."$firstRow")
            ->getFont()
            ->setBold(true);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:".$nextColumn."$firstRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$secondRow:".$nextColumn."$secondRow")
            ->getFont()
            ->setBold(true);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$secondRow:".$nextColumn."$secondRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$thirdRow:".$nextColumn."$thirdRow")
            ->getFont()
            ->setBold(true);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$thirdRow:".$nextColumn."$thirdRow")
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $count = 1;

        for ($startColumn = 'B'; $startColumn <= $nextColumn; ++$startColumn) {
            $sum = 0;

            for ($i = $fourthRow; $i < $currentRow; ++$i) {
                $value = $phpExcelObject->getActiveSheet()->getCell($startColumn."$i")->getValue();

                $sum += $value;
            }

            $phpExcelObject->getActiveSheet()->setCellValue($startColumn."$currentRow", $sum);

            if ($count % 2 == 0) {
                $phpExcelObject->getActiveSheet()
                    ->getStyle($startColumn."$currentRow")
                    ->getBorders()
                    ->getRight()
                    ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            }

            ++$count;

            if ($startColumn < $nextColumn) {
                $total = $sum;
            }
        }

        $phpExcelObject->getActiveSheet()
            ->getStyle("A$firstRow:".$nextColumn."$firstRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$secondRow:".$nextColumn."$secondRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$thirdRow:".$nextColumn."$thirdRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$currentRow:".$nextColumn."$currentRow")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle("A$currentRow:".$nextColumn."$currentRow")
            ->getBorders()
            ->getTop()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $phpExcelObject->getActiveSheet()
            ->getStyle($nextColumn."$currentRow")
            ->getBorders()
            ->getLeft()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        return $total;
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminFinancePermission(
        $opLevel,
        $adminId = null,
        $platform = null
    ) {
        if (is_null($adminId)) {
            $adminId = $this->getAdminId();
        }

        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_FINANCE],
            ],
            $opLevel,
            $platform
        );
    }
}
