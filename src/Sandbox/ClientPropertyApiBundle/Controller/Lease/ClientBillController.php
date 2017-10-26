<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Lease;

use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminStatusLog;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Form\Lease\LeaseBillPatchType;
use Sandbox\ApiBundle\Traits\FinanceTrait;
use Sandbox\ApiBundle\Traits\LeaseTrait;
use Sandbox\ApiBundle\Traits\SendNotification;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;

class ClientBillController extends SalesRestController
{
    use GenerateSerialNumberTrait;
    use SendNotification;
    use FinanceTrait;
    use LeaseTrait;

    /**
     * Get Lease Bills.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Annotations\QueryParam(
     *    name="channel",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    description="payment channel"
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
     *    name="send_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="send start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="send_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="send end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pay_start_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="pay_end_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="status of lease"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="product",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by product id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for the page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="start of the page"
     * )
     *
     * @Route("/bills")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAllBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $channels = $paramFetcher->get('channel');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $sendStart = $paramFetcher->get('send_start');
        $sendEnd = $paramFetcher->get('send_end');
        $payStartDate = $paramFetcher->get('pay_start_date');
        $payEndDate = $paramFetcher->get('pay_end_date');
        $status = $paramFetcher->get('status');
        $building = $paramFetcher->get('building');
        $product = $paramFetcher->get('product');

        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_LEASE_BILL,
            )
        );

        $leaseStatus = array(
            Lease::LEASE_STATUS_PERFORMING,
            Lease::LEASE_STATUS_TERMINATED,
            Lease::LEASE_STATUS_MATURED,
            Lease::LEASE_STATUS_END,
            Lease::LEASE_STATUS_CLOSED,
        );

        $defaultStatusSort = [
            LeaseBill::STATUS_UNPAID,
            LeaseBill::STATUS_PAID,
            LeaseBill::STATUS_PENDING,
            LeaseBill::STATUS_CANCELLED,
        ];

        if (is_null($status) || empty($status)) {
            $statusSorts = $defaultStatusSort;
        } else {
            $statusSorts = array_intersect($defaultStatusSort, $status);
        }
        $ids = [];
        foreach ($statusSorts as $statusSort) {
            $billIds = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->findBillsForPropertyClient(
                    $myBuildingIds,
                    $building,
                    $product,
                    $statusSort,
                    $channels,
                    $keyword,
                    $keywordSearch,
                    $sendStart,
                    $sendEnd,
                    $payStartDate,
                    $payEndDate,
                    $leaseStatus
                );

            $ids = array_merge($ids, $billIds);
        }

        $receivableTypes = [
            'sales_wx' => '微信',
            'sales_alipay' => '支付宝支付',
            'sales_cash' => '现金',
            'sales_others' => '其他',
            'sales_pos' => 'POS机',
            'sales_remit' => '线下汇款',
        ];

        $billStatus = array(
            LeaseBill::STATUS_PENDING => '未推送',
            LeaseBill::STATUS_UNPAID => '未付款',
            LeaseBill::STATUS_PAID => '已付款',
            LeaseBill::STATUS_CANCELLED => '已取消',
        );

        $bills = $this->handleBillData($ids, $limit, $offset, $receivableTypes, $billStatus);

        $view = new View();

        $view->setData($bills);

        return $view;
    }

    private function handleBillData(
        $billIds,
        $limit,
        $offset,
        $receivableTypes,
        $billStatus
    ) {
        $ids = array();
        for ($i = $offset; $i < $offset + $limit; ++$i) {
            if (isset($billIds[$i])) {
                $ids[] = $billIds[$i];
            }
        }

        $result = [];
        foreach ($ids as $id) {
            $bill = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->find($id);

            /** @var Lease $lease */
            $lease = $bill->getLease();
            /** @var Product $product */
            $product = $lease->getProduct();
            $room = $product->getRoom();
            $building = $room->getBuilding();

            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($lease->getLesseeCustomer());

            $attachment = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
                ->findAttachmentsByRoom($room->getId(), 1);

            $roomAttachment = [];
            if (!empty($attachment)) {
                $roomAttachment['content'] = $attachment[0]['content'];
                $roomAttachment['preview'] = $attachment[0]['preview'];
            }

            $payChannel = '';
            if ($bill->getPayChannel()) {
                if (LeaseBill::CHANNEL_SALES_OFFLINE == $bill->getPayChannel()) {
                    $receivable = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Finance\FinanceReceivables')
                        ->findOneBy([
                            'orderNumber' => $bill->getSerialNumber(),
                        ]);
                    if ($receivable) {
                        $payChannel = $receivableTypes[$receivable->getPayChannel()];
                    }
                } else {
                    $payChannel = '创合钱包支付';
                }
            }

            $result[] = [
                'id' => $id,
                'serial_number' => $bill->getSerialNumber(),
                'send_date' => $bill->getSendDate(),
                'name' => $bill->getName(),
                'room_name' => $room->getName(),
                'building_name' => $building->getName(),
                'start_date' => $bill->getStartDate(),
                'end_date' => $bill->getEndDate(),
                'amount' => (float) $bill->getAmount(),
                'revised_amount' => (float) $bill->getRevisedAmount(),
                'status' => $billStatus[$bill->getStatus()],
                'pay_channel' => $payChannel,
                'customer' => array(
                    'id' => $lease->getLesseeCustomer(),
                    'name' => $customer ? $customer->getName() : '',
                    'avatar' => $customer ? $customer->getAvatar() : '',
                ),
                'room_attachment' => $roomAttachment,
            ];
        }

        return $result;
    }

    /**
     * Get bill info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/bills/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getBillByIdAction(
        Request $request,
        $id
    ) {
        // check user permission

        $bill = $this->getDoctrine()->getRepository("SandboxApiBundle:Lease\LeaseBill")->find($id);
        $this->throwNotFoundIfNull($bill, CustomErrorMessagesConstants::ERROR_BILL_NOT_FOUND_MESSAGE);

        /** @var Lease $lease */
        $lease = $bill->getLease();
        /** @var Product $product */
        $product = $lease->getProduct();
        $rentSet = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductRentSet')
            ->findOneBy(array('product' => $product));

        $product->setRentSet($rentSet);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['lease_bill']));
        $view->setData($bill);

        return $view;
    }

    /**
     * Update Bill.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/bills/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchBillAction(
        Request $request,
        $id
    ) {
        $bill = $this->getDoctrine()->getRepository("SandboxApiBundle:Lease\LeaseBill")->find($id);
        $this->throwNotFoundIfNull($bill, self::NOT_FOUND_MESSAGE);

        $adminId = $this->getAdminId();

        $oldStatus = $bill->getStatus();

        $status = array(
            LeaseBill::STATUS_PENDING,
            LeaseBill::STATUS_UNPAID,
        );

        if (!in_array($oldStatus, $status)) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_CODE,
                CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_MESSAGE
            );
        }

        $billJson = $this->container->get('serializer')->serialize($bill, 'json');
        $patch = new Patch($billJson, $request->getContent());
        $billJson = $patch->apply();
        $form = $this->createForm(new LeaseBillPatchType(), $bill);
        $form->submit(json_decode($billJson, true));

        if (LeaseBill::STATUS_UNPAID != $bill->getStatus()) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_PAYLOAD_FORMAT_NOT_CORRECT_CODE,
                CustomErrorMessagesConstants::ERROR_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE
            );
        }

        if (is_null($bill->getRevisedAmount())) {
            $bill->setRevisedAmount($bill->getAmount());
        }
        $bill->setReviser($adminId);
        $bill->setSendDate(new \DateTime());
        $bill->setSender($adminId);
        $bill->setSalesInvoice(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($bill);
        $em->flush();

        if (LeaseBill::STATUS_PENDING == $oldStatus &&
            LeaseBill::STATUS_UNPAID == $bill->getStatus()
        ) {
            $this->pushBillMessage($bill);

            $logMessage = '推送账单';
            $this->get('sandbox_api.admin_status_log')->autoLog(
                $adminId,
                $bill->getStatus(),
                $logMessage,
                AdminStatusLog::OBJECT_LEASE_BILL,
                $id
            );
        }

        return new View();
    }
}
