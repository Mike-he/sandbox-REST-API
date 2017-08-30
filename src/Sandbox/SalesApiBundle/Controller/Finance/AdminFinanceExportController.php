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

    public function getFinanceCrashierExportAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ){
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_REPORT_DOWNLOAD,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->fing($data['company_id']);
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_CASHIER,
            )
        );
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');
        $language = $paramFetcher->get('language');

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

        $crashierOrders = array();
        $crashierBills = array();
        foreach($orders as $order){
            $crashierOrders[] = $this->getCrashierOrder($order,$company);
        }
        foreach($bills as $bill){
            $crashierBills[] = $this->getCrashierBill($bill,$company);
        }
    }

    private function getCrashierOrder($order,$company)
    {
        if ($order->getCustomerId()) {
            $drawee = $order->getCustomerId();
        } else {
            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(
                    array(
                        'userId' => $order->getUserId(),
                        'companyId' => $company->getId(),
                    )
                );

            $drawee = $customer ? $customer->getId() :
                $this->get('sandbox_api.sales_customer')->createCustomer($order->getUserId(), $company->getId());
        }

        $roomData = $this->getRoomData($order->getProductId());

        $unitDescription = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_UNIT.$order->getUnitPrice());
        $basePrice = $order->getUnitPrice() ? $order->getBasePrice().'元/'.$unitDescription : '';

        $data = array(
            'id' => $order->getId(),
            'order_type' => 'order',
            'serial_number' => $order->getOrderNumber(),
            'lease_serial_number' => '',
            'name' => '',
            'base_price' => $basePrice,
            'start_date' => $order->getStartDate(),
            'end_date' => $order->getEndDate(),
            'amount' => $order->getPrice(),
            'revised_amount' => $order->getDiscountPrice(),
            'status' => $order->getStatus(),
            'drawee' => $drawee,
            'send_date' => $order->getCreationDate(),
            'invoice' => true,
            'drawer' => $company->getName().'开票',
            'order_method' => '后台推送',
            'remark' => $order->getEditComment(),
            'description' => '',
            'room_name' => $roomData['room_name'],
            'room_type_tag' => $roomData['room_type_tag'],
        );

        return $data;
    }

    private function getCrashierBill($bill,$company)
    {
        $invoice = false;
        $leaseRentTypes = $bill->getLease()->getLeaseRentTypes();
        foreach ($leaseRentTypes as $leaseRentType) {
            if ($leaseRentType->getType() == LeaseRentTypes::RENT_TYPE_TAX) {
                $invoice = true;
            }
        }

        $roomData = $this->getRoomData($bill->getLease()->getProductId());

        $data = array(
            'id' => $bill->getId(),
            'lease_id' => $bill->getLease()->getId(),
            'order_type' => 'bill',
            'serial_number' => $bill->getSerialNumber(),
            'lease_serial_number' => $bill->getLease()->getSerialNumber(),
            'name' => $bill->getName(),
            'base_price' => $bill->getAmount(),
            'start_date' => $bill->getStartDate(),
            'end_date' => $bill->getEndDate(),
            'amount' => $bill->getAmount(),
            'revised_amount' => $bill->getRevisedAmount(),
            'status' => $bill->getStatus(),
            'drawee' => $bill->getLease()->getLesseeCustomer(),
            'send_date' => $bill->getSendDate(),
            'invoice' => $invoice,
            'drawer' => $company->getName().'开票',
            'order_method' => $bill->getOrderMethod() == LeaseBill::ORDER_METHOD_BACKEND ? '后台推送' : '自动推送',
            'remark' => $bill->getRemark(),
            'description' => $bill->getDescription(),
            'room_name' => $roomData['room_name'],
            'room_type_tag' => $roomData['room_type_tag'],
        );

        return $data;
    }
}
