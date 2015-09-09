<?php

namespace Sandbox\AdminApiBundle\Controller\Order;

use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
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
     * Order.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
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

        // check user permission
        $this->checkAdminOrderPermission($adminId, AdminPermissionMap::OP_LEVEL_VIEW);

        //filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $type = $paramFetcher->get('type');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $userId = $paramFetcher->get('user');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');

        //search by name and number
        $search = $paramFetcher->get('query');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        $query = $this->getRepo('Order\ProductOrder')->getOrdersForAdmin(
            $type,
            $city,
            $building,
            $userId,
            $startDate,
            $endDate,
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
     * @param Request $request
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

        $type = $paramFetcher->get('type');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $userId = $paramFetcher->get('user');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Sandbox Orders');

        //get array of orders
        $orders = $this->getRepo('Order\ProductOrder')->getOrdersToExport(
            $type,
            $city,
            $building,
            $userId,
            $startDate,
            $endDate
        );

        $headers = [
            'Product name', //ÉÌÆ·Ãû³Æ
            'Product type', //ÉÌÆ·ÀàÐÍ
            'Employee ID', //¹¤Î»ºÅ
            'Unit price', //µ¥¼Û
//            'Ê¹ÓÃÕÛ¿Û', //Discount
//            'Ë°', //VAT
            'Amount', //×Ü¼Û
            'Leasing time', //×âÁÞÊ±¼ä
            'Order time', //¶©µ¥´´½¨Ê±¼ä
            'Payment complete time', //¶©µ¥¸¶¿îÊ±¼ä
            'Order status', //¶©µ¥×´Ì¬
            'User name', //×âÁÞÈËÐÕÃû
            'User mobile', //×âÁÞÈËÊÖ»ú
            'User email', //×âÁÞÈËÓÊÏä
        ];

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($orders, ' ', 'A2');

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
        $this->checkAdminOrderPermission($adminId, AdminPermissionMap::OP_LEVEL_VIEW);

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
     * Get order by id.
     *
     * @param Request $request
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/orders/{id}/renter")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getOrderMemberRenterInfoAction(
        Request $request,
        $id
    ) {
        $adminId = $this->getAdminId();

        // check user permission
        $this->checkAdminOrderPermission($adminId, AdminPermissionMap::OP_LEVEL_VIEW);

        $order = $this->getRepo('Order\ProductOrder')->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($order->getUserId());
        $this->throwNotFoundIfNull($userProfile, self::NOT_FOUND_MESSAGE);
        $name = $userProfile->getName();

        $user = $this->getRepo('User\User')->find($order->getUserId());
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);
        $email = $user->getEmail();
        $phone = $user->getPhone();

        $view = new View();
        $view->setData([
            'user_id' => $order->getUserId(),
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ]);

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
     * @param Integer $opLevel
     * @param Integer $adminId
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
