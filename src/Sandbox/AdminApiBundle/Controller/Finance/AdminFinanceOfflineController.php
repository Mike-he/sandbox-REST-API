<?php

namespace Sandbox\AdminApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Offline\OfflineTransfer;
use Sandbox\ApiBundle\Entity\Order\OrderCount;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Order\TopUpOrder;
use Sandbox\ApiBundle\Form\Offline\OfflineTransferPatch;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;

class AdminFinanceOfflineController extends SandboxRestController
{
    /**
     * Get offline Bills lists.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="status",
     *    default=null,
     *    nullable=true,
     *    description="status"
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
     *    name="amount_start",
     *    default=null,
     *    nullable=true,
     *    description="amount start query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="amount_end",
     *    default=null,
     *    nullable=true,
     *    description="amount end query"
     * )
     *
     * @Route("/finance/offline/bills")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBillsListsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminFinanceOfflinePermission(AdminPermission::OP_LEVEL_VIEW);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $status = $paramFetcher->get('status');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $sendStart = $paramFetcher->get('send_start');
        $sendEnd = $paramFetcher->get('send_end');
        $amountStart = $paramFetcher->get('amount_start');
        $amountEnd = $paramFetcher->get('amount_end');

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBillsByCompany(
                null,
                LeaseBill::CHANNEL_OFFLINE,
                $status,
                $keyword,
                $keywordSearch,
                $sendStart,
                $sendEnd,
                $amountStart,
                $amountEnd
            );

        $bills = $this->get('serializer')->serialize(
            $bills,
            'json',
            SerializationContext::create()->setGroups(['lease_bill'])
        );
        $bills = json_decode($bills, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $bills,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * get Offline Finance Order.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
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
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Order Status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pay_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment start. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="pay_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment end. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="amount_start",
     *    default=null,
     *    nullable=true,
     *    description="amount start query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="amount_end",
     *    default=null,
     *    nullable=true,
     *    description="amount end query"
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
     * @Route("/finance/offline/orders")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getOrdersForFinanceAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminFinanceOfflinePermission(AdminPermission::OP_LEVEL_VIEW);

        //filters
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $payStart = $paramFetcher->get('pay_start');
        $payEnd = $paramFetcher->get('pay_end');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $status = $paramFetcher->get('status');
        $amountStart = $paramFetcher->get('amount_start');
        $amountEnd = $paramFetcher->get('amount_end');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersForFinance(
                ProductOrder::CHANNEL_OFFLINE,
                $payStart,
                $payEnd,
                $keyword,
                $keywordSearch,
                $status,
                $amountStart,
                $amountEnd,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countOrdersForFinance(
                ProductOrder::CHANNEL_OFFLINE,
                $payStart,
                $payEnd,
                $keyword,
                $keywordSearch,
                $status,
                $amountStart,
                $amountEnd
            );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_detail']));
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $orders,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * Get offline Bills lists.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="type",
     *    nullable=false,
     *    strict=true,
     *    description="type"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="status",
     *    default=null,
     *    nullable=true,
     *    description="status"
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
     *    name="amount_start",
     *    default=null,
     *    nullable=true,
     *    description="amount start query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="amount_end",
     *    default=null,
     *    nullable=true,
     *    description="amount end query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pay_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment start. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="pay_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment end. Must be YYYY-mm-dd"
     * )
     *
     * @Route("/finance/offline")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getOfflineListsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminFinanceOfflinePermission(AdminPermission::OP_LEVEL_VIEW);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $type = $paramFetcher->get('type');
        $status = $paramFetcher->get('status');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $amountStart = $paramFetcher->get('amount_start');
        $amountEnd = $paramFetcher->get('amount_end');
        $payStart = $paramFetcher->get('pay_start');
        $payEnd = $paramFetcher->get('pay_end');

        $transfers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Offline\OfflineTransfer')
            ->getOfflineTransferForAdmin(
                $type,
                $status,
                $keyword,
                $keywordSearch,
                $amountStart,
                $amountEnd,
                $payStart,
                $payEnd
            );

        $data = array();
        foreach ($transfers as $transfer) {
            $transferDetail = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Offline\OfflineTransfer')
                ->findOneBy(
                    array('orderNumber' => $transfer['orderNumber']),
                    array('id' => 'DESC')
                );

            $data[] = $transferDetail;
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $data,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/finance/offline/transfer/{id}")
     * @Method({"PATCH"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function patchTransferStatusAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminFinanceOfflinePermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        $transfer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Offline\OfflineTransfer')
            ->find($id);
        $this->throwNotFoundIfNull($transfer, self::NOT_FOUND_MESSAGE);

        $oldStatus = $transfer->getTransferStatus();

        // bind data
        $transferJson = $this->container->get('serializer')->serialize($transfer, 'json');
        $patch = new Patch($transferJson, $request->getContent());
        $transferJson = $patch->apply();

        $form = $this->createForm(new OfflineTransferPatch(), $transfer);
        $form->submit(json_decode($transferJson, true));

        $status = $transfer->getTransferStatus();
        $now = new \DateTime();

        switch ($status) {
            case OfflineTransfer::STATUS_PAID:
                if ($oldStatus != OfflineTransfer::STATUS_PENDING) {
                    return $this->customErrorView(
                        400,
                        CustomErrorMessagesConstants::ERROR_TRANSFER_STATUS_CODE,
                        CustomErrorMessagesConstants::ERROR_TRANSFER_STATUS_MESSAGE
                    );
                }

                // closed old transfer
                $oldTransfers = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Offline\OfflineTransfer')
                    ->findBy(array('orderNumber' => $transfer->getOrderNumber()));

                foreach ($oldTransfers as $oldTransfer) {
                    if ($oldTransfer->getId() == $transfer->getId()) {
                        continue;
                    }
                    $oldTransfer->setTransferStatus(OfflineTransfer::STATUS_CLOSED);
                }

                if ($transfer->getType() == OfflineTransfer::TYPE_TOPUP) {
                    $channel = ProductOrder::CHANNEL_OFFLINE;
                    $price = $transfer->getPrice();
                    $orderNumber = $transfer->getOrderNumber();
                    $userId = $transfer->getUserId();
                    $this->setTopUpOrder(
                        $em,
                        $userId,
                        $price,
                        $orderNumber,
                        $channel
                    );

                    $ordercount = $this->updateOrderCount();

                    $balance = $this->postBalanceChange(
                        $userId,
                        $price,
                        $orderNumber,
                        $channel,
                        $price
                    );

                    $amount = $this->postConsumeBalance(
                        $userId,
                        $price,
                        $orderNumber
                    );
                }

                break;
        }

        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/offline/transfer/detail")
     * @Method({"GET"})
     *
     * @Annotations\QueryParam(
     *    name="order_number",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getTransferAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminFinanceOfflinePermission(AdminPermission::OP_LEVEL_VIEW);

        $orderNumber = $paramFetcher->get('order_number');

        $transfers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Offline\OfflineTransfer')
            ->findBy(
                array('orderNumber' => $orderNumber),
                array('id' => 'DESC')
            );

        return new View($transfers);
    }

    /**
     * @param $em
     * @param $userId
     * @param $price
     * @param $orderNumber
     * @param $channel
     * @param bool $refundToAccount
     * @param null $refundNumber
     */
    private function setTopUpOrder(
        $em,
        $userId,
        $price,
        $orderNumber,
        $channel,
        $refundToAccount = false,
        $refundNumber = null
    ) {
        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\TopUpOrder')
            ->findOneByOrderNumber($orderNumber);

        if (is_null($order)) {
            $order = new TopUpOrder();
            $order->setUserId($userId);
            $order->setOrderNumber($orderNumber);
            $order->setPrice($price);
            $order->setRefundToAccount($refundToAccount);
            $order->setRefundNumber($refundNumber);
        }

        $order->setPayChannel($channel);

        $em->persist($order);
    }

    private function updateOrderCount()
    {
        $now = new \DateTime();
        $now->setTime(00, 00, 00);

        $em = $this->getDoctrine()->getManager();

        $counter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\OrderCount')
            ->findOneBy(['orderDate' => $now]);

        if (is_null($counter)) {
            $count = 1;
            $counter = new OrderCount();
            $counter->setOrderDate($now);
        } else {
            $count = $counter->getCount() + 1;
        }
        $counter->setCount($count);

        $em->persist($counter);
    }

    /**
     * @param $opLevel
     */
    private function checkAdminFinanceOfflinePermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_TRANSFER_CONFIRM],
            ],
            $opLevel
        );
    }
}
