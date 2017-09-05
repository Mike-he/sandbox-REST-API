<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Traits\FinanceSalesExportTraits;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Constants\ProductOrderExport;

class AdminFinanceExportController extends SalesRestController
{
    use FinanceSalesExportTraits;

    /**
     * @Annotations\QueryParam(
     *    name="year",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="month",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
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
     * @Route("/finance/export/poundage")
     *
     * @return View
     */
    public function getFinanceExportPoundageAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_REPORT_DOWNLOAD,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $year = $paramFetcher->get('year');
        $month = $paramFetcher->get('month');
        $language = $paramFetcher->get('language');

        $startString = $year.'-'.$month.'-01';
        $endString = $year.'-'.$month.'-31';

        $serviceBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceLongRentServiceBill')
            ->findServiceBillList(
                $data['company_id'],
                null,
                null,
                null,
                $startString,
                $endString,
                null,
                null
            );

        return $this->getFinanceExportPoundage(
            $serviceBills,
            $startString,
            $language
        );
    }

    /**
     * @Annotations\QueryParam(
     *    name="year",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="month",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
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
     * @Route("/finance/export/summary")
     *
     * @return View
     */
    public function getFinanceSummaryExportAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_REPORT_DOWNLOAD,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $year = $paramFetcher->get('year');
        $month = $paramFetcher->get('month');
        $language = $paramFetcher->get('language');

        $startString = $year.'-'.$month.'-01';
        $startDate = new \DateTime($startString);

        $endString = $startDate->format('Y-m-t');
        $endDate = new \DateTime($endString);
        $endDate->setTime(23, 59, 59);

        // event orders
        $events = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getEventOrderSummary(
                $startDate,
                $endDate,
                $data['company_id']
            );

        $orderTypes = array(
            ProductOrder::OWN_TYPE,
            ProductOrder::OFFICIAL_PREORDER_TYPE,
        );
        $shortOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getCompletedOrderSummary(
                $startDate,
                $endDate,
                $data['company_id'],
                $orderTypes
            );

        $membershipOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getMembershipOrdersByDate(
                $startDate,
                $endDate,
                $data['company_id']
            );

        return $this->getFinanceSummaryExport(
            $startDate,
            $language,
            $events,
            $shortOrders,
            $membershipOrders
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    array=false,
     *    nullable=true,
     * )
     *
     * @Route("/finance/export/wallet_flows")
     * @Method({"GET"})
     *
     * @return mixed
     */
    public function exportSalesWalletFlowsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_REPORT_DOWNLOAD,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );
        $salesCompanyId = $data['company_id'];

        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $language = $paramFetcher->get('language');

        $now = new \DateTime('now');
        $beginDate = clone $now;
        $beginDate = $beginDate->modify('-30 days');
        $startDate = is_null($startDate) ? $beginDate : $startDate;
        $endDate = is_null($endDate) ? $now : $endDate;
        $startString = is_object($startDate) ? $startDate->format('Y-m-d') : $startDate;

        $flows = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSalesWalletFlow')
            ->getAdminWalletFlows(
                $salesCompanyId,
                $startDate,
                $endDate
            );

        return $this->getFinanceSalesWalletFlowsExport(
                    $flows,
                    $startString,
                    $language
        );
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    array=false,
     *    nullable=true,
     * )
     *
     * @Route("/finance/export/orders")
     * @Method({"GET"})
     *
     * @return View
     */
    public function exportSalesOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_REPORT_DOWNLOAD,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $startDate = new \DateTime($paramFetcher->get('startDate'));
        $endDate = new \DateTime($paramFetcher->get('endDate'));
        $endDate = $endDate->setTime('23', '59', '59');
        $language = $paramFetcher->get('language');

        // event orders
        $events = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getEventOrderSummary(
                $startDate,
                $endDate,
                $data['company_id']
            );

        $orderTypes = [
            ProductOrder::OWN_TYPE,
            ProductOrder::OFFICIAL_PREORDER_TYPE,
            ProductOrder::PREORDER_TYPE,
        ];
        $shortOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getCompletedOrderSummary(
                $startDate,
                $endDate,
                $data['company_id'],
                $orderTypes
            );

        $membershipOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getMembershipOrdersByDate(
                $startDate,
                $endDate,
                $data['company_id']
            );

        return $this->getFinanceSummaryExport(
            $startDate,
            $language,
            $events,
            $shortOrders,
            $membershipOrders
        );
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    array=false,
     *    nullable=true,
     * )
     *
     * @Route("/finance/export/cashiers")
     * @Method({"GET"})
     *
     * @return mixed
     */
    public function exportFianceCashierAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_REPORT_DOWNLOAD,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');
        $language = $paramFetcher->get('language');

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($data['company_id']);

        $myBuildingIds = $data['building_ids'];

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getUnpaidPreOrders(
                $myBuildingIds,
                null,
                null,
                $startDate,
                $endDate,
                null,
                null
            );

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->getUnpaidBills(
                $myBuildingIds,
                null,
                null,
                $startDate,
                $endDate,
                null,
                null
            );

        $em = $this->getContainer()->get('doctrine')->getManager();
        $payments = $em->getRepository('SandboxApiBundle:Payment\Payment')->findAll();
        $payChannels = array();
        foreach ($payments as $payment) {
            $payChannels[$payment->getChannel()] = $payment->getName();
        }

        $cashierOrders = array();
        $cashierBills = array();

        foreach ($orders as $order) {
            $cashierOrders[] = $this->generateCashierOrder($order, $company, $payChannels, $language);
        }

        foreach ($bills as $bill) {
            $cashierBills[] = $this->generateCashierBill($bill, $company, $payChannels, $language);
        }

        $results = array_merge($cashierOrders, $cashierBills);

        return $this->getFinanceCashierExport(
            $results,
            $language
        );
    }

    /**
     * @param $order
     * @param $company
     * @param $payChannels
     * @param $language
     * @return array
     */
    private function generateCashierOrder(
        $order,
        $company,
        $payChannels,
        $language
    ) {
        $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(
                    array(
                        'userId' => $order->getUserId(),
                        'companyId' => $company->getId(),
                    ));
        $product = $order->getProduct();
        $room = $product->getRoom();
        $building = $room->getBuilding();

        $roomType = $this->get('translator')->trans(
            ProductOrderExport::TRANS_ROOM_TYPE.$room->getType(),
            array(),
            null,
            $language
        );

        $unitDescription = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_UNIT.$order->getUnitPrice());
        $basePrice = $order->getUnitPrice() ? $order->getBasePrice().'元/'.$unitDescription : '';
        $discountPrice = $order->getDiscountPrice();
        $refundAmount = $order->getActualRefundAmount();
        $poundage = $discountPrice * $order->getServiceFee() / 100;
        $refundTo = null;
        if ($order->getRefundTo()) {
            if ($order->getRefundTo() == 'account') {
                $refundTo = '退款到余额';
            } else {
                $refundTo = '原路退回';
            }
        }

        $data = array(
            'building_name' => $building->getName(),
            'order_type' => '秒租订单',
            'serial_number' => $order->getOrderNumber(),
            'room_name' => $room->getName(),
            'room_type_tag' => $roomType,
            'customer' => $customer ? $customer->getName() : '',
            'order_method' => '销售方推单',
            'payment_method' => '销售方收款',
            'pay_channel' => $order->getPayChannel() ? $payChannels[$order->getPayChannel()] : '',
            'base_price' => $basePrice,
            'unit_price' => $unitDescription,
            'amount' => $order->getPrice(),
            'revised_amount' => $order->getDiscountPrice(),
            'refund_amount' => $order->getActualRefundAmount(),
            'poundage' => $poundage,
            'settlement_amount' => $discountPrice - $refundAmount - $poundage,
            'start_date' => $order->getStartDate()->format('Y-m-d H:i:s'),
            'end_date' => $order->getEndDate()->format('Y-m-d H:i:s'),
            'creation_date' => $order->getCreationDate()->format('Y-m-d H:i:s'),
            'payment_date' => $order->getPaymentDate() ? $order->getPaymentDate()->format('Y-m-d H:i:s'):'',
            'status' => $order->getStatus(),
            'refundTo' => $refundTo,
            'customer_phone' => $customer ? $customer->getPhone() : '',
            'customer_email' => $customer ? $customer->getEmail() : ''
        );

        return $data;
    }

    /**
     * @param $bill
     * @param $company
     * @param $payChannels
     * @param $language
     * @return array
     */
    private function generateCashierBill(
        $bill,
        $company,
        $payChannels,
        $language
    ) {
        $leaseRentTypes = $bill->getLease()->getLeaseRentTypes();

        $customer = null;
        if($bill->getCustomerId()){
            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($bill->getCustomerId());
        }

        $product = $bill->getLease()->getProduct();
        $room = $product->getRoom();
        $building = $room->getBuilding();

        $roomType = $this->get('translator')->trans(
            ProductOrderExport::TRANS_ROOM_TYPE.$room->getType(),
            array(),
            null,
            $language
        );

        $data = array(
            'building_name' => $building->getName(),
            'order_type' => '长租账单',
            'serial_number' => $bill->getSerialNumber(),
            'room_name' => $room->getName(),
            'room_type_tag' => $roomType,
            'customer' => $customer ? $customer->getName() : '',
            'order_method' => '销售方推单',
            'payment_method' => '销售方收款',
            'pay_channel' => $bill->getPayChannel() ? $payChannels[$bill->getPayChannel()] : '',
            'base_price' => '',
            'unit_price' => '',
            'amount' => $bill->getAmount(),
            'revised_amount' => $bill->getRevisedAmount(),
            'refund_amount' => '',
            'poundage' => '',
            'settlement_amount' => '',
            'start_date' => $bill->getStartDate()->format('Y-m-d H:i:s'),
            'end_date' => $bill->getEndDate()->format('Y-m-d H:i:s'),
            'creation_date' => $bill->getCreationDate()->format('Y-m-d H:i:s'),
            'payment_date' => $bill->getPaymentDate() ? $bill->getPaymentDate()->format('Y-m-d H:i:s') : '',
            'status' => $bill->getStatus(),
            'refundTo' => '',
            'customer_phone' => $customer ? $customer->getPhone() : '',
            'customer_email' => $customer ? $customer->getEmail() : ''
        );

        return $data;
    }
}
