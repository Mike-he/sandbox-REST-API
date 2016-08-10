<?php

namespace Sandbox\AdminApiBundle\Controller\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\AdminApiBundle\Controller\Order\AdminOrderController;
use Sandbox\ApiBundle\Constants\EventOrderExport;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class AdminEventOrderController extends AdminOrderController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @Annotations\QueryParam(
     *    name="flag",
     *    default="event",
     *    requirements="(event|event_registration)",
     *    nullable=true,
     *    description="search flag"
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
     *    name="query",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Route("/events/orders")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getEventOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminEventOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_VIEW);

        $cityId = $paramFetcher->get('city');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $flag = $paramFetcher->get('flag');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $search = $paramFetcher->get('query');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;

        $orders = $this->getRepo('Event\EventOrder')->getEventOrdersForAdmin(
            $city,
            $flag,
            $startDate,
            $endDate,
            $search
        );

        // set event dates
        foreach ($orders as $order) {
            $event = $order->getEvent();
            $dates = $this->getRepo('Event\EventDate')->findByEvent($event);
            $event->setDates($dates);
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $orders,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     *    name="flag",
     *    default="event",
     *    requirements="(event|event_registration)",
     *    nullable=true,
     *    description="search flag"
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
     *    name="query",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Route("/events/orders/export")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getExcelEventOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //authenticate with web browser cookie
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();

        // check user permission
        $this->checkAdminEventOrderPermission($adminId, AdminPermissionMap::OP_LEVEL_VIEW);

        $language = $paramFetcher->get('language');
        $cityId = $paramFetcher->get('city');
        $flag = $paramFetcher->get('flag');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $search = $paramFetcher->get('query');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;

        $orders = $this->getRepo('Event\EventOrder')->getEventOrdersForAdmin(
            $city,
            $flag,
            $startDate,
            $endDate,
            $search
        );

        return $this->getEventOrderExport($orders, $language);
    }

    /**
     * @param $orders
     * @param $language
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws \PHPExcel_Exception
     */
    private function getEventOrderExport(
        $orders,
        $language
    ) {
        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Sandbox Orders');
        $excelBody = array();

        // set excel body
        foreach ($orders as $order) {
            // get event
            $event = $order->getEvent();

            // get order number
            $orderNumber = $order->getOrderNumber();

            // get event name
            $eventName = $event->getName();

            // get user
            $userId = $order->getUserId();
            $user = $this->getRepo('User\User')->find($userId);
            $userPhone = $user->getPhone();
            $userEmail = $user->getEmail();

            // get pay amount
            $payAmount = $order->getPrice();

            // get payment date
            $paymentDate = $order->getPaymentDate();

            // get status
            $statusKey = $order->getStatus();
            $status = $this->get('translator')->trans(
                EventOrderExport::TRANS_EVENT_ORDER_STATUS.$statusKey,
                array(),
                null,
                $language
            );

            $paymentChannel = $order->getPayChannel();
            if (!is_null($paymentChannel) && !empty($paymentChannel)) {
                $paymentChannel = $this->get('translator')->trans(
                    EventOrderExport::TRANS_EVENT_ORDER_CHANNEL.$paymentChannel,
                    array(),
                    null,
                    $language
                );
            }

            // set excel body
            $body = array(
                EventOrderExport::ORDER_NUMBER => $orderNumber,
                EventOrderExport::EVENT_NAME => $eventName,
                EventOrderExport::USER_ID => $userId,
                EventOrderExport::PAY_AMOUNT => $payAmount,
                EventOrderExport::PAYMENT_DATE => $paymentDate,
                EventOrderExport::ORDER_STATUS => $status,
                EventOrderExport::USER_PHONE => $userPhone,
                EventOrderExport::USER_EMAIL => $userEmail,
                EventOrderExport::PAYMENT_CHANNEL => $paymentChannel,
            );

            $excelBody[] = $body;
        }

        // set excel headers
        $headers = array(
            $this->get('translator')->trans(EventOrderExport::TRANS_EVENT_ORDER_HEADER_ORDER_NO, array(), null, $language),
            $this->get('translator')->trans(EventOrderExport::TRANS_EVENT_ORDER_HEADER_EVENT_NAME, array(), null, $language),
            $this->get('translator')->trans(EventOrderExport::TRANS_EVENT_ORDER_HEADER_USER_ID, array(), null, $language),
            $this->get('translator')->trans(EventOrderExport::TRANS_EVENT_ORDER_HEADER_PAY_AMOUNT, array(), null, $language),
            $this->get('translator')->trans(EventOrderExport::TRANS_EVENT_ORDER_HEADER_PAYMENT_DATE, array(), null, $language),
            $this->get('translator')->trans(EventOrderExport::TRANS_EVENT_ORDER_HEADER_ORDER_STATUS, array(), null, $language),
            $this->get('translator')->trans(EventOrderExport::TRANS_EVENT_ORDER_HEADER_USER_PHONE, array(), null, $language),
            $this->get('translator')->trans(EventOrderExport::TRANS_EVENT_ORDER_HEADER_USER_EMAIL, array(), null, $language),
            $this->get('translator')->trans(EventOrderExport::TRANS_EVENT_ORDER_HEADER_PAYMENT_CHANNEL, array(), null, $language),
        );

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($excelBody, ' ', 'A2');

        $phpExcelObject->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);

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
     * @param Request $request
     * @param $id
     *
     * @Route("/events/orders/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getEventOrderByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminEventOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_VIEW);

        $order = $this->getRepo('Event\EventOrder')->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $event = $order->getEvent();
        $dates = $this->getRepo('Event\EventDate')->findByEvent($event);
        $event->setDates($dates);

        $view = new View($order);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['main'])
        );

        return $view;
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     * @param int $adminId
     */
    private function checkAdminEventOrderPermission(
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
