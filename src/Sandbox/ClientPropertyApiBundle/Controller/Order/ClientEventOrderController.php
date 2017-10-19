<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Order;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

class ClientEventOrderController extends SalesRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
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
     *    name="channel",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="payment channel"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Order Status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="event_status",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Event Status"
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
     * @Annotations\QueryParam(
     *    name="create_date_range",
     *    default=null,
     *    nullable=true,
     *    description="create_date_range"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="create_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
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
     *
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
     * @Route("/events/orders")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getEventOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        $channel = $paramFetcher->get('channel');
        $status = $paramFetcher->get('status');
        $eventStatus = $paramFetcher->get('event_status');

        $payDate = $paramFetcher->get('pay_date');
        $payStart = $paramFetcher->get('pay_start');
        $payEnd = $paramFetcher->get('pay_end');

        $createDateRange = $paramFetcher->get('create_date_range');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');


        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $sortColumn = 'payment_date';
        $direction = 'DESC';

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getEventOrdersForSalesAdmin(
                null,
                $channel,
                $status,
                $eventStatus,
                $keyword,
                $keywordSearch,
                $payDate,
                $payStart,
                $payEnd,
                $createDateRange,
                $createStart,
                $createEnd,
                $companyId,
                null,
                $limit,
                $offset,
                $sortColumn,
                $direction
            );

        $orderStatus = [
            EventOrder::STATUS_UNPAID => '未付款',
            EventOrder::STATUS_PAID => '已付款',
            EventOrder::STATUS_COMPLETED => '已完成',
            EventOrder::STATUS_CANCELLED => '已取消',
        ];

        // set event dates
        $orderLists = [];
        foreach ($orders as $order) {
            $orderLists[] = $this->handleOrderData($order, $orderStatus);
        }

        $view = new View();
        $view->setData($orderLists);

        return $view;
    }

    /**
     * @param EventOrder $order
     * @param  $status
     *
     * @return array
     */
    private function handleOrderData(
        $order,
        $status
    ) {
        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($order->getCustomerId());

        /** @var Event $event */
        $event = $order->getEvent();

        $eventAttachment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventAttachment')
            ->findOneBy(array('eventId' => $event->getId()));

        $result = array(
            'id' => $order->getId(),
            'order_number' => $order->getOrderNumber(),
            'creation_date' => $order->getCreationDate(),
            'status' => $status[$order->getStatus()],
            'event_name' => $event->getname(),
            'event_start_date' => $event->getEventStartDate(),
            'event_end_date' => $event->getEventEndDate(),
            'address' => $event->getAddress(),
            'price' => (float) $order->getPrice(),
            'pay_channel' => $order->getPayChannel() ? '创合钱包支付' : '',
            'customer' => array(
                'id' => $order->getCustomerId(),
                'name' => $customer ? $customer->getName() : '',
                'avatar' => $customer ? $customer->getAvatar() : '',
            ),
            'attachment' => $eventAttachment ? $eventAttachment->getContent() : '',
        );

        return $result;
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
        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        /** @var Event $event */
        $event = $order->getEvent();
        $dates = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventDate')
            ->findByEvent($event);
        $event->setDates($dates);

        $attachments = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventAttachment')
            ->findBy(array(
                'event' => $event,
            ));
        $event->setAttachments($attachments);

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($order->getUserId());

        $profile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserProfile')
            ->findOneBy(['user' => $user]);

        $userInfo = [
            'name' => $profile ? $profile->getName() : '',
            'email' => $user ? $user->getEmail() : '',
            'phone_code' => $user ? $user->getPhoneCode() : '',
            'phone' => $user ? $user->getPhone() : '',
            'card_no' => $user ? $user->getCardNo() : '',
        ];

        $order->setUser($userInfo);

        $view = new View($order);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups([
                'client_event',
                'admin_event',
            ]));

        return $view;
    }
}
