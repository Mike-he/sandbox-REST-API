<?php

namespace Sandbox\AdminApiBundle\Controller\Order;

use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\Order\OrderController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Product\Product;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin order controller.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminOrderController extends OrderController
{
    /**
     * @Route("/orders/{id}/sync")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function syncAccessByOrderAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_VIEW);

        // check if order exists
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }

        // check if order expired
        $now = new \DateTime();
        if ($order->getEndDate() <= $now) {
            return $this->customErrorView(
                400,
                self::WRONG_ORDER_STATUS_CODE,
                self::WRONG_ORDER_STATUS_MESSAGE
            );
        }

        $base = $order->getProduct()->getRoom()->getBuilding()->getServer();
        $this->syncAccessByOrder($base, $order);

        return new Response();
    }

    /**
     * Order.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="(office|meeting|flexible|fixed)",
     *    strict=true,
     *    description="Filter by room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by city id"
     * )
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
     *    name="user",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by user id"
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
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="channel",
     *    default=null,
     *    nullable=true,
     *    description="payment channel"
     * )
     *
     * @Annotations\QueryParam(
     *    name="payStart",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment start. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="payEnd",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment end. Must be YYYY-mm-dd"
     * )
     *
     * @Route("/orders")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminId = $this->getAdminId();
        $userId = $paramFetcher->get('user');

        // check user permission
        if (!is_null($userId) || !empty($userId)) {
            $this->throwAccessDeniedIfAdminNotAllowed(
                $adminId,
                AdminType::KEY_PLATFORM,
                array(
                    AdminPermission::KEY_PLATFORM_ORDER,
                    AdminPermission::KEY_PLATFORM_USER,
                ),
                AdminPermissionMap::OP_LEVEL_VIEW
            );
        } else {
            $this->checkAdminOrderPermission($adminId, AdminPermissionMap::OP_LEVEL_VIEW);
        }

        //filters
        $channel = $paramFetcher->get('channel');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $type = $paramFetcher->get('type');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $payStart = $paramFetcher->get('payStart');
        $payEnd = $paramFetcher->get('payEnd');

        //search by name and number
        $search = $paramFetcher->get('query');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        $query = $this->getRepo('Order\ProductOrder')->getOrdersForAdmin(
            $channel,
            $type,
            $city,
            $building,
            $userId,
            $startDate,
            $endDate,
            $payStart,
            $payEnd,
            $search
        );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $query,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Export orders to excel.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="(office|meeting|flexible|fixed)",
     *    strict=true,
     *    description="Filter by room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by city id"
     * )
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
     *    name="user",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by user id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="channel",
     *    default=null,
     *    nullable=true,
     *    description="payment channel"
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
     * @Annotations\QueryParam(
     *    name="payStart",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment start. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="payEnd",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment end. Must be YYYY-mm-dd"
     * )
     *
     * @Route("/orders/export")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getExcelOrders(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //authenticate with web browser cookie
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();

        // check user permission
        $this->checkAdminOrderPermission($adminId, AdminPermissionMap::OP_LEVEL_VIEW);

        $language = $paramFetcher->get('language');
        $channel = $paramFetcher->get('channel');
        $type = $paramFetcher->get('type');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $userId = $paramFetcher->get('user');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $payStart = $paramFetcher->get('payStart');
        $payEnd = $paramFetcher->get('payEnd');
        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Sandbox Orders');

        //get array of orders
        $orders = $this->getRepo('Order\ProductOrder')->getOrdersToExport(
            $channel,
            $type,
            $city,
            $building,
            $userId,
            $startDate,
            $endDate,
            $payStart,
            $payEnd
        );

        $excelBody = array();

        // set excel body
        foreach ($orders as $order) {
            $productInfo = json_decode($order->getProductInfo(), true);

            // set product name
            $productName = $productInfo['room']['city']['name'].
                $productInfo['room']['building']['name'].
                $productInfo['room']['number'];

            // set product type
            $productTypeKey = $productInfo['room']['type'];
            $productType = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_TYPE.$productTypeKey,
                array(),
                null,
                $language
            );

            // set unit price
            $unitPriceKey = $productInfo['unit_price'];
            $unitPrice = $this->get('translator')->trans(
                ProductOrderExport::TRANS_ROOM_UNIT.$unitPriceKey,
                array(),
                null,
                $language
            );

            // set status
            $statusKey = $order->getStatus();
            $status = $this->get('translator')->trans(
                ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$statusKey,
                array(),
                null,
                $language
            );

            // set leasing name
            $leasingTime = $order->getStartDate()->format('Y-m-d H:i:s')
                .' - '
                .$order->getEndDate()->format('Y-m-d H:i:s');

            $userId = $order->getUserId();
            $user = $this->getRepo('User\User')->find($userId);

            $paymentChannel = $order->getPayChannel();
            if (!is_null($paymentChannel) && !empty($paymentChannel)) {
                $paymentChannel = $this->get('translator')->trans(
                    ProductOrderExport::TRANS_PRODUCT_ORDER_CHANNEL.$order->getPayChannel(),
                    array(),
                    null,
                    $language
                );
            }

            // set excel body
            $body = array(
                ProductOrderExport::ORDER_NUMBER => $order->getOrderNumber(),
                ProductOrderExport::PRODUCT_NAME => $productName,
                ProductOrderExport::ROOM_TYPE => $productType,
                ProductOrderExport::USER_ID => $userId,
                ProductOrderExport::BASE_PRICE => $productInfo['base_price'],
                ProductOrderExport::UNIT_PRICE => $unitPrice,
                ProductOrderExport::AMOUNT => $order->getPrice(),
                ProductOrderExport::DISCOUNT_PRICE => $order->getDiscountPrice(),
                ProductOrderExport::LEASING_TIME => $leasingTime,
                ProductOrderExport::ORDER_TIME => $order->getCreationDate()->format('Y-m-d H:i:s'),
                ProductOrderExport::PAYMENT_TIME => $order->getPaymentDate()->format('Y-m-d H:i:s'),
                ProductOrderExport::ORDER_STATUS => $status,
                ProductOrderExport::USER_PHONE => $user->getPhone(),
                ProductOrderExport::USER_EMAIL => $user->getEmail(),
                ProductOrderExport::PAYMENT_CHANNEL => $paymentChannel,
            );

            $excelBody[] = $body;
        }

        $headers = [
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_NO, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_PRODUCT_NAME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ROOM_TYPE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_USER_ID, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_BASE_PRICE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_UNIT_PRICE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_AMOUNT, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_DISCOUNT_PRICE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_LEASING_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_PAYMENT_TIME, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_ORDER_STATUS, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_USER_PHONE, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_USER_EMAIL, array(), null, $language),
            $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_HEADER_PAYMENT_CHANNEL, array(), null, $language),
        ];

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($excelBody, ' ', 'A2');

        $phpExcelObject->getActiveSheet()->getStyle('A1:L1')->getFont()->setBold(true);

        //set column dimension
        for ($col = ord('a'); $col <= ord('o'); ++$col) {
            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension(chr($col))->setAutoSize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('Orders');

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
            'orders_'.$stringDate.'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * Get member order renter info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/orders/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getOrderByIdAction(
        Request $request,
        $id
    ) {
        $adminId = $this->getAdminId();

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            AdminType::KEY_PLATFORM,
            array(
                AdminPermission::KEY_PLATFORM_ORDER,
                AdminPermission::KEY_PLATFORM_USER,
            ),
            AdminPermissionMap::OP_LEVEL_VIEW
        );

        $order = $this->getRepo('Order\ProductOrder')->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['admin_detail'])
        );
        $view->setData($order);

        return $view;
    }

    /**
     * authenticate with web browser cookie.
     */
    private function authenticateAdminCookie()
    {
        $cookie_name = 'sandbox_admin_token';
        if (!isset($_COOKIE[$cookie_name])) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $token = $_COOKIE[$cookie_name];
        $adminToken = $this->getRepo('Admin\AdminToken')->findOneByToken($token);
        if (is_null($adminToken)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        return $adminToken->getAdmin();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     * @param int $adminId
     */
    private function checkAdminOrderPermission(
        $adminId,
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_ORDER,
            $opLevel
        );
    }
}
