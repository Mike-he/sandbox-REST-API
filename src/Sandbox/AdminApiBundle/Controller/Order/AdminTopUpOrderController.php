<?php

namespace Sandbox\AdminApiBundle\Controller\Order;

use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Rest controller for Admin TopUpOrders.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminTopUpOrderController extends PaymentController
{
    /**
     * Get all orders for current user.
     *
     * @Get("/topup/orders")
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
     *    name="channel",
     *    default=null,
     *    nullable=true,
     *    array=true,
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
     *    name="pay_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment start. Must be YYYY-mm-dd"
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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getUserTopUpOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminId = $this->getAdminId();

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        $channel = $paramFetcher->get('channel');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $payDate = $paramFetcher->get('pay_date');
        $payStart = $paramFetcher->get('pay_start');
        $payEnd = $paramFetcher->get('pay_end');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\TopUpOrder')
            ->getTopUpOrdersForAdmin(
                $channel,
                $payDate,
                $payStart,
                $payEnd,
                $keyword,
                $keywordSearch,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\TopUpOrder')
            ->countTopUpOrdersForAdmin(
                $channel,
                $payDate,
                $payStart,
                $payEnd,
                $keyword,
                $keywordSearch
            );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_order']));
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
     * Get all top orders To Export.
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
     * @Annotations\QueryParam(
     *    name="channel",
     *    default=null,
     *    nullable=true,
     *    array=true,
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
     *    name="pay_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment start. Must be YYYY-mm-dd"
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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Get("/topup/orders/export")
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getExcelTopUpOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //authenticate with web browser cookie
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER],
            ],
            AdminPermission::OP_LEVEL_VIEW,
            AdminPermission::PERMISSION_PLATFORM_OFFICIAL
        );

        $language = $paramFetcher->get('language');
        $channel = $paramFetcher->get('channel');
        $payDate = $paramFetcher->get('pay_date');
        $payStart = $paramFetcher->get('pay_start');
        $payEnd = $paramFetcher->get('pay_end');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\TopUpOrder')
            ->getTopUpOrdersToExport(
                $channel,
                $payDate,
                $payStart,
                $payEnd,
                $keyword,
                $keywordSearch
            );

        return $this->getTopOrderExport($orders, $language);
    }

    /**
     * @Get("/topup/orders/{orderNumber}")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getOneTopUpOrderByOrderNumberAction(
        Request $request,
        $orderNumber
    ) {
        $adminId = $this->getAdminId();

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\TopUpOrder')
            ->findOneBy(
                [
                    'orderNumber' => $orderNumber,
                ]
            );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $view = new View($order);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_order']));

        return $view;
    }

    /**
     * @Get("/topup/orders/{id}")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getOneTopUpOrderByIdAction(
        Request $request,
        $id
    ) {
        $adminId = $this->getAdminId();

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\TopUpOrder')
            ->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $view = new View($order);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_order']));

        return $view;
    }

    /**
     * @param array  $orders
     * @param string $language
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws \PHPExcel_Exception
     */
    private function getTopOrderExport(
        $orders,
        $language
    ) {
        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Sandbox Orders');
        $excelBody = array();

        $payments = $this->getDoctrine()->getRepository('SandboxApiBundle:Payment\Payment')->findAll();
        $payChannel = array();
        foreach ($payments as $payment) {
            $payChannel[$payment->getChannel()] = $payment->getName();
        }

        // set excel body
        foreach ($orders as $order) {
            $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView')->find($order->getUserId());

            // set excel body
            $body = array(
                'payment_date' => $order->getPaymentDate()->format('Y-m-d H:i:s'),
                'order_number' => $order->getOrderNumber(),
                'source' => $order->isRefundToAccount() ? '退款到余额' : '充值',
                'pay_channel' => $payChannel[$order->getPayChannel()],
                'price' => $order->getPrice(),
                'refund_order_number' => $order->getRefundNumber(),
                'username' => $this->filterEmoji($user->getName()),
                'account' => $user->getPhone() ? $user->getPhone() : $user->getEmail(),
            );

            $excelBody[] = $body;
        }

        $headers = [
            '充值时间',
            '充值订单号',
            '充值来源',
            '支付渠道',
            '充值金额',
            '退款订单号',
            '用户昵称',
            '用户账号',
        ];

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($excelBody, ' ', 'A2');

        $phpExcelObject->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);

        //set column dimension
        for ($col = ord('a'); $col <= ord('h'); ++$col) {
            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension(chr($col))->setAutoSize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('充值导表');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        $date = new \DateTime('now');
        $stringDate = $date->format('Y-m-d H:i:s');

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'topup_orders_'.$stringDate.'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}
