<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\EventOrderExport;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * Admin Finance Summary Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminFinanceSummaryController extends PaymentController
{
    /**
     * @param Request $request
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Annotations\QueryParam(
     *    name="year",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="year"
     * )
     *
     * @Method({"GET"})
     * @Route("/finance/summary")
     *
     * @return View
     */
    public function getFinanceSummaryAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->checkAdminSalesFinanceSummaryPermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $year = $paramFetcher->get('year');
        if (is_null($year) || empty($year)) {
            $now = new \DateTime();
            $year = $now->format('Y');
        }

        $yearStart = new \DateTime("$year-01-01 00:00:00");
        $yearEnd = new \DateTime("$year-12-31 23:59:59");

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $offset = ($pageIndex - 1) * $pageLimit;

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSummary')
            ->countFinanceSummary(
                $salesCompanyId,
                $yearStart,
                $yearEnd
            );

        $summary = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSummary')
            ->getFinanceSummary(
                $salesCompanyId,
                $yearStart,
                $yearEnd,
                $pageLimit,
                $offset
            );

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $summary,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Method({"GET"})
     * @Route("/finance/summary/current")
     *
     * @return View
     */
    public function getCurrentFinanceSummaryAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->checkAdminSalesFinanceSummaryPermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $now = new \DateTime();
        $start = clone $now;
        $start->modify('first day of this month');
        $start->setTime(0, 0, 0);

        $summary = $this->getShortRentAndLongRentArray(
            $salesCompanyId,
            $start,
            $now
        );

        $summary['current_month'] = $now->format('m');

        $view = new View();
        $view->setData($summary);

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Method({"GET"})
     * @Route("/finance/summary/years")
     *
     * @return View
     */
    public function getSummaryYearsAction(
        Request $request
    ) {
        $this->checkAdminSalesFinanceSummaryPermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $years = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSummary')
            ->getFinanceSummaryYear($salesCompanyId);

        $yearArray = [];
        foreach ($years as $year) {
            $yearString = $year['creationDate']->format('Y');

            if (in_array($yearString, $yearArray)) {
                continue;
            }
            array_push($yearArray, $yearString);
        }

        return new View(['years' => $yearArray]);
    }

    /**
     * @param Request $request
     *
     * @Method({"GET"})
     * @Route("/finance/summary/counts")
     *
     * @return View
     */
    public function getSummaryNumberCountsAction(
        Request $request
    ) {
        $adminPlatform = $this->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $salesCompanyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $invoices = $this->getSalesAdminInvoices();
        $invoiceCount = (int) $invoices['total_count'];

        $billCount = (int) $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBillByCompany(
                LeaseBill::STATUS_VERIFY,
                $salesCompanyId
            );

        $shortRentAmount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoice')
            ->sumPendingShortRentInvoices($salesCompanyId);
        if (is_null($shortRentAmount)) {
            $shortRentAmount = 0;
        }

        //get long rent amount
        $longRentAmount = 0;
        $longRent = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSalesWallet')
            ->findOneBy(['companyId' => $salesCompanyId]);
        if (!is_null($longRent)) {
            $longRentAmount = $longRent->getBillAmount();
        }

        $pendingLongRent = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentBill')
            ->sumBillAmount(
                $salesCompanyId,
                FinanceLongRentBill::STATUS_PENDING
            );
        if (is_null($pendingLongRent)) {
            $pendingLongRent = 0;
        }

        $longRentAmount = $longRentAmount - $pendingLongRent;

        $view = new View();
        $view->setData([
            'long_rent_amount' => (float) $longRentAmount,
            'short_rent_amount' => (float) $shortRentAmount,
            'user_invoice_count' => $invoiceCount,
            'offline_verify_count' => $billCount,
        ]);

        return $view;
    }

    /**
     * @Annotations\QueryParam(
     *    name="summary_id",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="summary id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    default="zh",
     *    nullable=true,
     *    requirements="(zh|en)",
     *    strict=true,
     *    description="export language"
     * )
     *
     * @Method({"GET"})
     * @Route("/finance/summary/export")
     *
     * @return View
     */
    public function getFinanceSummaryExportAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //authenticate with web browser cookie
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();
        $token = $_COOKIE[self::ADMIN_COOKIE_NAME];

        $userToken = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserToken')
            ->findOneBy([
                'userId' => $adminId,
                'token' => $token,
            ]);
        $this->throwNotFoundIfNull($userToken, self::NOT_FOUND_MESSAGE);

        $adminPlatform = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPlatform')
            ->findOneBy(array(
                'userId' => $adminId,
                'clientId' => $userToken->getClientId(),
            ));
        if (is_null($adminPlatform)) {
            throw new PreconditionFailedHttpException(self::PRECONDITION_NOT_SET);
        }

        $companyId = $adminPlatform->getSalesCompanyId();

        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            [
                ['key' => AdminPermission::KEY_SALES_PLATFORM_FINANCIAL_SUMMARY],
            ],
            AdminPermission::OP_LEVEL_VIEW,
            $adminPlatform->getPlatform(),
            $companyId
        );

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findOneBy([
                'id' => $companyId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $language = $paramFetcher->get('language');
        $id = $paramFetcher->get('summary_id');

        $summary = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSummary')
            ->findOneBy([
                'id' => $id,
                'companyId' => $companyId,
            ]);
        $this->throwNotFoundIfNull($summary, self::NOT_FOUND_MESSAGE);

        $lastDate = $summary->getSummaryDate();
        $firstDate = clone $lastDate;
        $firstDate->modify('first day of this month');
        $firstDate->setTime(0, 0, 0);

        // event orders
        $events = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getEventOrderSummary(
                $firstDate,
                $lastDate,
                $companyId
            );

        $shortOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getCompletedOrderSummary(
                $firstDate,
                $lastDate,
                $companyId
            );

        $longOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
            ->getServiceBillsByMonth(
                $firstDate,
                $lastDate,
                $companyId
            );

        return $this->getSummaryExport(
            $company,
            $events,
            $shortOrders,
            $longOrders,
            $firstDate,
            $language
        );
    }

    /**
     * @param $company
     * @param $events
     * @param $shortOrders
     * @param $longOrders
     * @param $firstDate
     * @param $language
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws \PHPExcel_Exception
     */
    private function getSummaryExport(
        $company,
        $events,
        $shortOrders,
        $longOrders,
        $firstDate,
        $language
    ) {
        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Finance Summary');

        $companyName = $company->getName();

        $eventBody = $this->setEventArray(
            $companyName,
            $company,
            $events,
            $language
        );

        $shortBody = $this->setShortOrderArray(
            $companyName,
            $company,
            $shortOrders,
            $language
        );

        $longBody = $this->setLongOrderArray(
            $companyName,
            $company,
            $longOrders,
            $language
        );

        $headers = [
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
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ACTUAL_AMOUNT, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_COMMISSION, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_LEASING_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_PAYMENT_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_STATUS, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_TYPE, array(), null, $language),
        ];

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 1;

        if (!empty($shortBody)) {
            $phpExcelObject->setActiveSheetIndex(0)->fromArray($shortBody, ' ', "A$row");
            $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 3;
        }

        if (!empty($longBody)) {
            $phpExcelObject->setActiveSheetIndex(0)->fromArray($longBody, ' ', "A$row");
            $row = $phpExcelObject->getActiveSheet()->getHighestRow() + 3;
        }

        if (!empty($eventBody)) {
            $phpExcelObject->setActiveSheetIndex(0)->fromArray($eventBody, ' ', "A$row");
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
     * @param $companyName
     * @param $events
     * @param $language
     *
     * @return array
     */
    private function setEventArray(
        $companyName,
        $company,
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
            $building = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);
            $buildingName = $building ? $building->getName() : null;

            $orderNumber = $event->getOrderNumber();

            $productName = $event->getEvent()->getName();

            $userId = $event->getUserId();

            $basePrice = $event->getPrice();

            $unit = null;

            $price = $basePrice;

            $actualAmount = $price;

            $income = $actualAmount - $actualAmount * $event->getServiceFee();

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

            $body = $this->getExportBody(
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
                $income,
                $commission,
                $leasingTime,
                $creationDate,
                $payDate,
                $status,
                $orderType
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
        $companyName,
        $company,
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
            $buildingName = $building->getName();

            $orderNumber = $order->getOrderNumber();

            $productInfo = json_decode($order->getProductInfo(), true);
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
                if ($method == SalesCompanyServiceInfos::COLLECTION_METHOD_SALES) {
                    $collection = $companyName;
                }
            }

            $userId = $order->getUserId();

            $basePrice = $productInfo['base_price'];

            $unit = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_UNIT.$productInfo['unit_price'],
                array(),
                null,
                $language
            );

            $price = $order->getPrice();

            $actualAmount = $order->getDiscountPrice();

            $income = $actualAmount - $actualAmount * $order->getServiceFee();

            $leasingTime = $order->getStartDate()->format('Y-m-d H:i:s')
                .' - '
                .$order->getEndDate()->format('Y-m-d H:i:s');

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

            $body = $this->getExportBody(
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
                $income,
                $commission,
                $leasingTime,
                $creationDate,
                $payDate,
                $status,
                $orderType
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
        $companyName,
        $company,
        $longOrders,
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

        foreach ($longOrders as $order) {
            $bill = $order->getBill();
            $lease = $bill->getLease();
            $product = $lease->getProduct();
            $room = $product->getRoom();
            $building = $room->getBuilding();
            $buildingName = $building->getName();
            $city = $building->getCity();

            $orderNumber = $bill->getSerialNumber();

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

            $userId = $lease->getSupervisor()->getId();

            $basePrice = $product->getBasePrice();

            $unit = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_UNIT.$product->getUnitPrice(),
                array(),
                null,
                $language
            );

            $price = $bill->getAmount();

            $actualAmount = $bill->getRevisedAmount();

            $income = $actualAmount;

            $commission = $order->getAmount();

            $leasingTime = $bill->getStartDate()->format('Y-m-d H:i:s')
                .' - '
                .$bill->getEndDate()->format('Y-m-d H:i:s');

            $creationDate = $bill->getCreationDate()->format('Y-m-d H:i:s');

            $payDate = $bill->getPaymentDate()->format('Y-m-d H:i:s');

            $status = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$bill->getStatus(),
                array(),
                null,
                $language
            );

            $type = $bill->getOrderMethod();
            $orderType = $this->get('translator')->trans(
                LeaseConstants::TRANS_LEASE_BILL_ORDER_METHOD.$type,
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
                $userId,
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
                $orderType
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
     * @param $userId
     * @param $basePrice
     * @param $unit
     * @param $price
     * @param $actualAmount
     * @param $income
     * @param $commission
     * @param $leasingTime
     * @param $creationDate
     * @param $payDate
     * @param $status
     * @param $orderType
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
        $userId,
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
        $orderType
    ) {
        // set excel body
        $body = array(
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
            ProductOrderExport::ACTUAL_AMOUNT => $income,
            ProductOrderExport::COMMISSION => $commission,
            ProductOrderExport::LEASING_TIME => $leasingTime,
            ProductOrderExport::CREATION_DATE => $creationDate,
            ProductOrderExport::PAYMENT_TIME => $payDate,
            ProductOrderExport::ORDER_STATUS => $status,
            ProductOrderExport::ORDER_TYPE => $orderType,
        );

        return $body;
    }

    /**
     * @param $salesCompanyId
     * @param $start
     * @param $end
     *
     * @return array
     */
    private function getShortRentAndLongRentArray(
        $salesCompanyId,
        $start,
        $end
    ) {
        // short rent orders
        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getCompletedOrders(
                $start,
                $end,
                $salesCompanyId
            );

        $amount = 0;
        foreach ($orders as $order) {
            $amount += $order['discountPrice'] * (1 - $order['serviceFee'] / 100);
        }

        // long rent orders
        $longRents = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
            ->getServiceBillsByMonth(
                $start,
                $end,
                $salesCompanyId
            );

        $serviceAmount = 0;
        $incomeAmount = 0;
        foreach ($longRents as $longRent) {
            $serviceAmount += $longRent->getAmount();
            $incomeAmount += $longRent->getBill()->getRevisedAmount();
        }

        // event orders
        $events = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getSumEventOrders(
                $start,
                $end,
                $salesCompanyId
            );

        $eventBalance = 0;
        foreach ($events as $event) {
            $eventBalance += $event['price'];
        }

        $summaryArray = [
            'total_income' => $amount + $incomeAmount + $eventBalance,
            'short_rent_balance' => $amount,
            'long_rent_balance' => $incomeAmount,
            'event_order_balance' => $eventBalance,
            'total_service_bill' => $serviceAmount,
            'long_rent_service_bill' => $serviceAmount,
        ];

        return $summaryArray;
    }

    /**
     * @param $adminId
     * @param $opLevel
     */
    private function checkAdminSalesFinanceSummaryPermission(
        $adminId,
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            [
                ['key' => AdminPermission::KEY_SALES_PLATFORM_FINANCIAL_SUMMARY],
            ],
            $opLevel
        );
    }
}
