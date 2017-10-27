<?php

namespace Sandbox\ApiBundle\Service;

use Sandbox\ApiBundle\Constants\EventOrderExport;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\GenericList\GenericList;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;
use Sandbox\ApiBundle\Entity\Lease\LeaseOffer;
use Sandbox\ApiBundle\Entity\Lease\LeaseRentTypes;
use Sandbox\ApiBundle\Entity\MembershipCard\MembershipOrder;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdminExportService
{
    private $container;
    private $doctrine;
    private $user;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
        $this->doctrine = $container->get('doctrine');

        $token = $this->container->get('security.token_storage')->getToken();
        $this->user = isset($token) ? $token->getUser() : null;
    }

    public function exportExcel(
        $data,
        $key,
        $adminId,
        $language,
        $min = null,
        $max = null
    ) {
        $lists = $this->getGenericLists($key, $adminId);

        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Sandbox Excel');

        $headers = [];
        foreach ($lists as $list) {
            $headers[] = $list;
        }

        switch ($key) {
            case GenericList::OBJECT_LEASE_CLUE:
                $excelBody = $this->getExcelClueData($data, $lists, $language);
                $fileName = '线索'.$min.' - '.$max;
                break;
            case GenericList::OBJECT_LEASE_OFFER:
                $excelBody = $this->getExcelOfferData($data, $lists, $language);
                $fileName = '报价'.$min.' - '.$max;
                break;
            case GenericList::OBJECT_LEASE:
                $excelBody = $this->getExcelLeaseData($data, $lists, $language);
                $fileName = '合同'.$min.' - '.$max;
                break;
            case GenericList::OBJECT_LEASE_BILL:
                $excelBody = $this->getExcelBillData($data, $lists, $language);
                $fileName = '账单'.$min.' - '.$max;
                break;
            case GenericList::OBJECT_CASHIER:
                $excelBody = $this->getExcelFinansherCrashier($data, $lists, $language);
                $fileName = '收银台'.$min.' - '.$max;
                break;
            case GenericList::OBJECT_PRODUCT_ORDER:
                $excelBody = $this->getExcelProductOrder($data, $lists, $language);
                $fileName = '空间订单'.$min.' - '.$max;
                break;
            case GenericList::OBJECT_MEMBERSHIP_ORDER:
                $excelBody = $this->getExcelMembershipOrder($data, $lists, $language);
                $fileName = '会员卡订单'.$min.' - '.$max;
                break;
            case GenericList::OBJECT_EVENT_ORDER:
                $excelBody = $this->getExcelEventOrder($data, $lists, $language);
                $fileName = '活动订单'.$min.' - '.$max;
                break;
            default:
                $excelBody = array();
                $fileName = null;
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

        $filename = $fileName.'.xls';

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

        return $response;
    }

    /**
     * @param $object
     * @param $adminId
     *
     * @return array
     */
    private function getGenericLists(
        $object,
        $adminId
    ) {
        $genericUserLists = $this->doctrine
            ->getRepository('SandboxApiBundle:GenericList\GenericUserList')
            ->findBy(
                array(
                    'object' => $object,
                    'userId' => $adminId,
                )
            );

        $lists = array();
        if ($genericUserLists) {
            foreach ($genericUserLists as $genericUserList) {
                $lists[$genericUserList->getList()->getColumn()] = $genericUserList->getList()->getName();
            }
        } else {
            $genericLists = $this->doctrine
                ->getRepository('SandboxApiBundle:GenericList\GenericList')
                ->findBy(
                    array(
                        'object' => $object,
                        'platform' => AdminPermission::PERMISSION_PLATFORM_SALES,
                        'default' => true,
                    )
                );
            foreach ($genericLists as $genericList) {
                $lists[$genericList->getColumn()] = $genericList->getName();
            }
        }

        return $lists;
    }

    /**
     * @param LeaseClue $clues
     * @param $lists
     * @param $language
     *
     * @return array
     */
    private function getExcelClueData(
        $clues,
        $lists,
        $language
    ) {
        $status = array(
            LeaseClue::LEASE_CLUE_STATUS_CLUE => '新线索',
            LeaseClue::LEASE_CLUE_STATUS_OFFER => '转为报价',
            LeaseClue::LEASE_CLUE_STATUS_CONTRACT => '转为合同',
            LeaseClue::LEASE_CLUE_STATUS_CLOSED => '已关闭',
        );

        $excelBody = array();
        foreach ($clues as $clue) {
            /** @var LeaseClue $clue */
            $appointmentName = null;
            if ($clue->getProductAppointmentId()) {
                $appointment = $this->doctrine
                    ->getRepository('SandboxApiBundle:Product\ProductAppointment')
                    ->find($clue->getProductAppointmentId());

                $appointmentName = $appointment ? $appointment->getApplicantName() : null;
            }

            $customer = $this->doctrine
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($clue->getLesseeCustomer());

            $roomData = $this->getRoomData($clue->getProductId(), $language);

            $clueList = array(
                'serial_number' => $clue->getSerialNumber(),
                'room_name' => $roomData['room_name'],
                'room_type_tag' => $roomData['room_type_tag'],
                'lessee_name' => $clue->getLesseeName(),
                'lessee_address' => $clue->getLesseeAddress(),
                'lessee_customer' => $customer->getName(),
                'lessee_email' => $clue->getLesseeEmail(),
                'lessee_phone' => $clue->getLesseePhone(),
                'start_date' => $clue->getStartDate() ? $clue->getStartDate()->format('Y-m-d H:i:s') : '',
                'cycle' => $clue->getCycle() ? $clue->getCycle().'个月' : '',
                'monthly_rent' => $clue->getMonthlyRent() ? $clue->getMonthlyRent().'元/月起' : '',
                'number' => $clue->getNumber(),
                'creation_date' => $clue->getCreationDate()->format('Y-m-d H:i:s'),
                'status' => $status[$clue->getStatus()],
                'total_rent' => $clue->getMonthlyRent() * $clue->getNumber(),
                'appointment_user' => $appointmentName,
            );

            $body = array();
            foreach ($lists as $key => $value) {
                $body[] = $clueList[$key];
            }

            $excelBody[] = $body;
        }

        return $excelBody;
    }

    /**
     * @param $productId
     * @param $language
     *
     * @return array
     */
    private function getRoomData(
        $productId,
        $language
    ) {
        $roomName = null;
        $roomTypeTag = null;
        if ($productId) {
            $product = $this->doctrine
                ->getRepository('SandboxApiBundle:Product\Product')
                ->find($productId);

            if ($product) {
                $roomName = $product->getRoom()->getName();
                $tag = $product->getRoom()->getTypeTag();

                $roomTypeTag = $this->container->get('translator')->trans(
                    ProductOrderExport::TRANS_PREFIX.$tag,
                    array(),
                    null,
                    $language
                );
            }
        }

        $result = array(
            'room_name' => $roomName,
            'room_type_tag' => $roomTypeTag,
        );

        return $result;
    }

    /**
     * @param LeaseOffer $offers
     * @param $lists
     * @param $language
     *
     * @return array
     */
    private function getExcelOfferData(
        $offers,
        $lists,
        $language
    ) {
        $status = array(
            LeaseOffer::LEASE_OFFER_STATUS_OFFER => '报价中',
            LeaseOffer::LEASE_OFFER_STATUS_CONTRACT => '转为合同',
            LeaseOffer::LEASE_OFFER_STATUS_CLOSED => '已关闭',
        );

        $excelBody = array();
        foreach ($offers as $offer) {
            /** @var LeaseOffer $offer */
            $customer = $this->doctrine
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($offer->getLesseeCustomer());

            $enterpriseName = null;
            if ($offer->getLesseeEnterprise()) {
                $enterprise = $this->doctrine
                    ->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
                    ->find($offer->getLesseeEnterprise());

                $enterpriseName = $enterprise ? $enterprise->getName() : null;
            }

            $startDate = $offer->getStartDate() ? $offer->getStartDate()->format('Y-m-d H:i:s') : '';
            $endDate = $offer->getEndDate() ? $offer->getEndDate()->format('Y-m-d H:i:s') : '';

            $leaseRentTypes = $offer->getLeaseRentTypes();
            $taxTypes = array();
            foreach ($leaseRentTypes as $leaseRentType) {
                if (LeaseRentTypes::RENT_TYPE_TAX == $leaseRentType->getType()) {
                    $taxTypes[] = $leaseRentType->getName();
                }
            }

            $taxTypes = implode(',', $taxTypes);

            $roomData = $this->getRoomData($offer->getProductId(), $language);

            $offerList = array(
                'serial_number' => $offer->getSerialNumber(),
                'room_name' => $roomData['room_name'],
                'room_type_tag' => $roomData['room_type_tag'],
                'lessee_type' => LeaseOffer::LEASE_OFFER_LESSEE_TYPE_PERSONAL == $offer->getLesseeType() ? '个人承租' : '企业承租',
                'lessee_enterprise' => $enterpriseName,
                'lessee_customer' => $customer->getName(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'monthly_rent' => $offer->getMonthlyRent() ? $offer->getMonthlyRent().'元/月起' : '',
                'deposit' => $offer->getDeposit() ? $offer->getDeposit().'元' : '',
                'lease_rent_types' => $taxTypes,
                'creation_date' => $offer->getCreationDate()->format('Y-m-d H:i:s'),
                'status' => $status[$offer->getStatus()],
                'total_rent' => $offer->getTotalRent(),
            );

            $body = array();
            foreach ($lists as $key => $value) {
                $body[] = $offerList[$key];
            }

            $excelBody[] = $body;
        }

        return $excelBody;
    }

    /**
     * @param Lease $leases
     * @param $lists
     * @param $language
     *
     * @return array
     */
    private function getExcelLeaseData(
        $leases,
        $lists,
        $language
    ) {
        $excelBody = array();
        foreach ($leases as $lease) {
            /** @var Lease $lease */
            $customer = $this->doctrine
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($lease->getLesseeCustomer());

            $enterpriseName = null;
            if ($lease->getLesseeEnterprise()) {
                $enterprise = $this->doctrine
                    ->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
                    ->find($lease->getLesseeEnterprise());

                $enterpriseName = $enterprise ? $enterprise->getName() : null;
            }

            $startDate = $lease->getStartDate() ? $lease->getStartDate()->format('Y-m-d H:i:s') : '';
            $endDate = $lease->getEndDate() ? $lease->getEndDate()->format('Y-m-d H:i:s') : '';

            $leaseRentTypes = $lease->getLeaseRentTypes();
            $taxTypes = array();
            foreach ($leaseRentTypes as $leaseRentType) {
                if (LeaseRentTypes::RENT_TYPE_TAX == $leaseRentType->getType()) {
                    $taxTypes[] = $leaseRentType->getName();
                }
            }

            $taxTypes = implode(',', $taxTypes);

            $roomData = $this->getRoomData($lease->getProductId(), $language);

            $leaseBillsCount = $this->doctrine
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease,
                    LeaseBill::TYPE_LEASE
                );

            $otherBillsCount = $this->doctrine
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease,
                    LeaseBill::TYPE_OTHER
                );

            $status = $status = $this->container->get('translator')
                ->trans(LeaseConstants::TRANS_LEASE_STATUS.$lease->getStatus());

            $leaseList = array(
                'serial_number' => $lease->getSerialNumber(),
                'room_name' => $roomData['room_name'],
                'room_type_tag' => $roomData['room_type_tag'],
                'lessee_type' => Lease::LEASE_LESSEE_TYPE_PERSONAL == $lease->getLesseeType() ? '个人承租' : '企业承租',
                'lessee_enterprise' => $enterpriseName,
                'lessee_customer' => $customer ? $customer->getName() : '',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'monthly_rent' => $lease->getMonthlyRent() ? $lease->getMonthlyRent().'元/月起' : '',
                'deposit' => $lease->getDeposit() ? $lease->getDeposit().'元' : '',
                'lease_rent_types' => $taxTypes,
                'creation_date' => $lease->getCreationDate()->format('Y-m-d H:i:s'),
                'status' => $status,
                'total_rent' => $lease->getTotalRent(),
                'lease_bill' => $leaseBillsCount,
                'other_bill' => $otherBillsCount,
            );

            $body = array();
            foreach ($lists as $key => $value) {
                $body[] = $leaseList[$key];
            }

            $excelBody[] = $body;
        }

        return $excelBody;
    }

    /**
     * @param LeaseBill $bills
     * @param $lists
     * @param $language
     *
     * @return array
     */
    private function getExcelBillData(
        $bills,
        $lists,
        $language
    ) {
        $status = array(
            LeaseBill::STATUS_PENDING => '未推送',
            LeaseBill::STATUS_UNPAID => '未付款',
            LeaseBill::STATUS_PAID => '已付款',
            LeaseBill::STATUS_VERIFY => '待确认',
            LeaseBill::STATUS_CANCELLED => '已取消',
        );

        $excelBody = array();

        $payments = $this->doctrine->getRepository('SandboxApiBundle:Payment\Payment')->findAll();
        $payChannel = array();
        foreach ($payments as $payment) {
            $payChannel[$payment->getChannel()] = $payment->getName();
        }

        foreach ($bills as $bill) {
            /** @var LeaseBill $bill */
            $company = $this->doctrine
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
                ->find($bill->getLease()->getCompanyId());

            $drawee = null;
            if ($bill->getCustomerId()) {
                $customer = $this->doctrine
                    ->getRepository('SandboxApiBundle:User\UserCustomer')
                    ->find($bill->getCustomerId());

                $drawee = $customer ? $customer->getName() : '';
            }

            $startDate = $bill->getStartDate() ? $bill->getStartDate()->format('Y-m-d H:i:s') : '';
            $endDate = $bill->getEndDate() ? $bill->getEndDate()->format('Y-m-d H:i:s') : '';

            $invoice = false;
            $leaseRentTypes = $bill->getLease()->getLeaseRentTypes();
            foreach ($leaseRentTypes as $leaseRentType) {
                if (LeaseRentTypes::RENT_TYPE_TAX == $leaseRentType->getType()) {
                    $invoice = true;
                }
            }

            $billList = array(
                'serial_number' => $bill->getSerialNumber(),
                'lease_serial_number' => $bill->getLease()->getSerialNumber(),
                'drawer' => $bill->isSalesInvoice() ? $company->getName().'开票' : '创合开票',
                'name' => $bill->getName(),
                'description' => $bill->getDescription(),
                'amount' => $bill->getAmount(),
                'invoice' => $invoice ? '包含发票' : '不包含发票',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'drawee' => $drawee,
                'order_method' => LeaseBill::ORDER_METHOD_BACKEND == $bill->getOrderMethod() ? '后台推送' : '自动推送',
                'pay_channel' => $bill->getPayChannel() ? $payChannel[$bill->getPayChannel()] : '',
                'send_date' => $bill->getSendDate() ? $bill->getSendDate()->format('Y-m-d H:i:s') : '',
                'status' => $status[$bill->getStatus()],
                'revised_amount' => $bill->getRevisedAmount() ? $bill->getRevisedAmount() : '',
                'remark' => $bill->getRemark(),
            );

            $body = array();
            foreach ($lists as $key => $value) {
                $body[] = $billList[$key];
            }

            $excelBody[] = $body;
        }

        return $excelBody;
    }

    /**
     * @param $crashiers
     * @param $lists
     * @param $language
     *
     * @return array
     */
    private function getExcelFinansherCrashier(
        $crashiers,
        $lists,
        $language
    ) {
        $excelBody = array();
        foreach ($crashiers as $crashier) {
            $body = array();
            foreach ($lists as $key => $value) {
                if ('status' == $key) {
                    $body[] = 'unpaid' == $value ? '未付款' : '已付款';
                } else {
                    $body[] = $crashier[$key];
                }
            }

            $excelBody[] = $body;
        }

        return $excelBody;
    }

    /**
     * @param ProductOrder $orders
     * @param $lists
     * @param $language
     *
     * @return array
     */
    private function getExcelProductOrder(
        $orders,
        $lists,
        $language
    ) {
        $excelBody = array();
        foreach ($orders as $order) {
            /** @var ProductOrder $order */
            $productInfo = json_decode($order->getProductInfo(), true);

            // set product type
            $productTypeKey = $productInfo['room']['type'];

            switch ($productTypeKey) {
                case 'studio':
                    $productTypeKey = 'others';
                    break;
                case 'space':
                    $productTypeKey = 'others';
                    break;
                case 'fixed':
                    $productTypeKey = 'desk';
                    break;
                case 'flexible':
                    $productTypeKey = 'desk';
                    break;
                default:
                    break;
            }

            $productType = $this->container->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$productTypeKey,
                array(),
                null,
                $language
            );

            // set unit price
            $basePrice = null;
            if (isset($productInfo['unit_price']) && isset($productInfo['base_price'])) {
                $unitPriceKey = $productInfo['unit_price'];
                $basePrice = $productInfo['base_price'];
            } elseif (isset($productInfo['order']['unit_price'])) {
                $unitPriceKey = $productInfo['order']['unit_price'];

                if (isset($productInfo['room']['leasing_set'])) {
                    foreach ($productInfo['room']['leasing_set'] as $item) {
                        if ($item['unit_price'] == $unitPriceKey) {
                            $basePrice = $item['base_price'];
                        }
                    }
                }
            } elseif (isset($productInfo['room']['leasing_set'])) {
                $unitPriceKey = $productInfo['room']['leasing_set'][0]['unit_price'];
                $basePrice = $productInfo['room']['leasing_set'][0]['base_price'];
            }

            $unitPrice = $this->container->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_UNIT.$unitPriceKey,
                array(),
                null,
                $language
            );

            // set status
            $statusKey = $order->getStatus();
            $status = $this->container->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$statusKey,
                array(),
                null,
                $language
            );

            $startTime = $order->getStartDate()->format('Y-m-d H:i:s');
            $endTime = $order->getEndDate()->format('Y-m-d H:i:s');

            $orderType = $this->container->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.$order->getType(),
                array(),
                null,
                $language
            );

            $customerId = $order->getCustomerId();
            if ($customerId) {
                $customer = $this->doctrine->getRepository('SandboxApiBundle:User\UserCustomer')
                    ->find($customerId);
            }

            $paymentDate = $order->getPaymentDate();

            $paymentChannel = $order->getPayChannel();
            if (!is_null($paymentChannel) && !empty($paymentChannel)) {
                $paymentChannel = $this->container->get('translator')->trans(
                    ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$paymentChannel,
                    array(),
                    null,
                    $language
                );
            }

            $orderList = array(
                'order_number' => $order->getOrderNumber(),
                'rent_period' => $startTime.'至'.$endTime,
                'base_price' => $basePrice.'元/'.$unitPrice,
                'price' => $order->getPrice(),
                'discount_price' => $order->getDiscountPrice(),
                'status' => $status,
                'payment_user_id' => empty($customer) ? '' : $customer->getName(),
                'creation_date' => $order->getCreationDate()->format('Y-m-d H:i:s'),
                'payment_date' => $paymentDate ? $paymentDate->format('Y-m-d') : '',
                'invoice' => '包含发票',
                'invoiced' => $order->isInvoiced() ? '已开票' : '未开票',
                'type' => $orderType,
                'description' => $orderType,
                'room_type' => $productType,
                'pay_channel' => $paymentChannel,
            );

            $body = array();
            foreach ($lists as $key => $val) {
                $body[] = $orderList[$key];
            }
            $excelBody[] = $body;
        }

        return $excelBody;
    }

    /**
     * @param MembershipOrder $orders
     * @param $lists
     * @param $language
     *
     * @return array
     */
    private function getExcelMembershipOrder(
        $orders,
        $lists,
        $language
    ) {
        $excelBody = array();
        foreach ($orders as $order) {
            /** @var MembershipOrder $order */
            $payments = $this->doctrine->getRepository('SandboxApiBundle:Payment\Payment')->findAll();
            $payChannel = array();
            foreach ($payments as $payment) {
                $payChannel[$payment->getChannel()] = $payment->getName();
            }

            $customer = $this->doctrine
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(array(
                    'userId' => $order->getUser(),
                    'companyId' => $order->getCard()->getCompanyId(),
                ));

            $drawee = $customer ? $customer->getName() : '';

            $billList = array(
                'order_number' => $order->getOrderNumber(),
                'price' => $order->getPrice(),
                'valid_period' => $order->getValidPeriod(),
                'discount_price' => $order->getPrice(),
                'status' => '已完成',
                'user_id' => $drawee,
                'creation_date' => $order->getCreationDate()->format('Y-m-d H:i:s'),
                'pay_channel' => $payChannel[$order->getPayChannel()],
                'name' => $order->getCard()->getName(),
                'specification' => $order->getSpecification(),
            );

            $body = array();
            foreach ($lists as $key => $value) {
                $body[] = $billList[$key];
            }

            $excelBody[] = $body;
        }

        return $excelBody;
    }

    /**
     * @param $orders
     * @param $lists
     * @param $language
     *
     * @return array
     */
    private function getExcelEventOrder(
        $orders,
        $lists,
        $language
    ) {
        $excelBody = array();

        foreach ($orders as $order) {
            /** @var EventOrder $order */
            /** @var Event $event */
            $event = $order->getEvent();

            // get order number
            $orderNumber = $order->getOrderNumber();

            // get user
            $userId = $order->getUserId();
            $userProfile = $this->doctrine
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy(['userId' => $userId]);

            // get status
            $statusKey = $order->getStatus();
            $status = $this->container->get('translator')->trans(
                EventOrderExport::TRANS_EVENT_ORDER_STATUS.$statusKey,
                array(),
                null,
                $language
            );

            $paymentChannel = $order->getPayChannel();
            if (!is_null($paymentChannel) && !empty($paymentChannel)) {
                $paymentChannel = $this->container->get('translator')->trans(
                    EventOrderExport::TRANS_EVENT_ORDER_CHANNEL.$paymentChannel,
                    array(),
                    null,
                    $language
                );
            }

            $orderList = [
                'order_number' => $orderNumber,
                'address' => $event->getAddress(),
                'event_start_date' => $event->getEventStartDate(),
                'price' => $order->getPrice(),
                'status' => $status,
                'user_id' => $userProfile->getName(),
                'creation_date' => $order->getCreationDate(),
                'publish_company' => $event->getPublishCompany(),
                'name' => $event->getName(),
                'pay_channel' => $paymentChannel,
                'description' => $event->getDescription(),
            ];

            $body = array();
            foreach ($lists as $key => $value) {
                $body[] = $orderList[$key];
            }

            $excelBody[] = $body;
        }

        return $excelBody;
    }
}
