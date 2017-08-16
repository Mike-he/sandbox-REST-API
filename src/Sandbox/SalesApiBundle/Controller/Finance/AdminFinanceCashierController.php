<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseRentTypes;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Finance Cashier Controller.
 */
class AdminFinanceCashierController extends SalesRestController
{
    const ORDER_TYPE_ORDER = 'order';
    const ORDER_TYPE_BILL = 'bill';

    /**
     * Get Finance Invoice Category.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="order_type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by order type"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="start_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
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
     * @Route("/finance/cashier")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getFinanceCashierAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_PLATFORM_CASHIER],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $orderType = $paramFetcher->get('order_type');
        $building = $paramFetcher->get('building');
        $type = $paramFetcher->get('type');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($salesCompanyId);

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getUnpaidPreOrders(
                $salesCompanyId,
                $building,
                $type,
                $startDate,
                $endDate,
                $keyword,
                $keywordSearch
            );

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->getUnpaidBills(
                $salesCompanyId,
                $building,
                $type,
                $startDate,
                $endDate,
                $keyword,
                $keywordSearch
            );

        $cashierOrders = array();
        $cashierBills = array();

        switch ($orderType) {
            case self::ORDER_TYPE_ORDER:
                foreach ($orders as $order) {
                    $cashierOrders[] = $this->generateCashierOrder($order, $company);
                }

                $result = $cashierOrders;
                break;
            case self::ORDER_TYPE_BILL:
                foreach ($bills as $bill) {
                    $cashierBills[] = $this->generateCashierBill($bill, $company);
                }

                $result = $cashierBills;
                break;
            default:
                foreach ($orders as $order) {
                    $cashierOrders[] = $this->generateCashierOrder($order, $company);
                }

                foreach ($bills as $bill) {
                    $cashierBills[] = $this->generateCashierBill($bill, $company);
                }

                $result = array_merge($cashierOrders, $cashierBills);
        }


        $count = count($result);

        // for pagination
        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

        $data = array();
        for ($i = $offset; $i < $offset + $limit; ++$i) {
            if (isset($result[$i])) {
                array_push($data, $result[$i]);
            }
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $data,
                'total_count' => (int) $count,
            )
        );

        return $view;

    }

    /**
     * @param ProductOrder $order
     * @param SalesCompany $company
     *
     * @return array
     */
    private function generateCashierOrder(
        $order,
        $company
    ) {
        if ($order->getCustomerId()) {
            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($order->getCustomerId());

            $drawee = $customer ? $customer->getName() : '';
        } else {
            $userProfile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy(array('userId' => $order->getUserId()));

            $drawee = $userProfile->getName();
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

    /**
     * @param LeaseBill    $bill
     * @param SalesCompany $company
     *
     * @return array
     */
    private function generateCashierBill(
        $bill,
        $company
    ) {
        $drawee = null;
        if ($bill->getCustomerId()) {
            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($bill->getCustomerId());

            $drawee = $customer ? $customer->getName() : '';
        }

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
            'drawee' => $drawee,
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

    /**
     * @param $productId
     *
     * @return array
     */
    private function getRoomData(
        $productId
    ) {
        $roomName = null;
        $roomTypeTag = null;
        if ($productId) {
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->find($productId);

            if ($product) {
                $roomName = $product->getRoom()->getName();
                $tag = $product->getRoom()->getTypeTag();

                $roomTypeTag = $this->get('translator')->trans(ProductOrderExport::TRANS_PREFIX.$tag);
            }
        }

        $result = array(
            'room_name' => $roomName,
            'room_type_tag' => $roomTypeTag,
        );

        return $result;
    }
}
