<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Order;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Constants\EventOrderExport;
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
     *    name="event_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="event_end",
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
     * @Route("/events")
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

        $payStart = $paramFetcher->get('pay_start');
        $payEnd = $paramFetcher->get('pay_end');

        $createStart = $paramFetcher->get('create_start');
        if ($createStart) {
            $createStart = new \DateTime($createStart);
        }

        $createEnd = $paramFetcher->get('create_end');
        if ($createEnd) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);
        }
        
        $eventStart = $paramFetcher->get('event_start');
        $eventEnd = $paramFetcher->get('event_end');

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getEventOrdersForPropertyClient(
                $createStart,
                $createEnd,
                $companyId,
                $channel,
                $status,
                $eventStatus,
                $keyword,
                $keywordSearch,
                $payStart,
                $payEnd,
                $eventStart,
                $eventEnd,
                $limit,
                $offset
            );

        // set event dates
        $orderLists = [];
        foreach ($orders as $order) {
            $orderLists[] = $this->handleOrderData($order);
        }

        $view = new View();
        $view->setData($orderLists);

        return $view;
    }

    /**
     * @param EventOrder $order
     *
     * @return array
     */
    private function handleOrderData(
        $order
    ) {
        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($order->getCustomerId());

        /** @var Event $event */
        $event = $order->getEvent();

        $eventAttachment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventAttachment')
            ->findOneBy(array('eventId' => $event->getId()));

        $status = $this->get('translator')->trans(EventOrderExport::TRANS_EVENT_ORDER_STATUS.$order->getStatus());

        $result = array(
            'id' => $order->getId(),
            'order_number' => $order->getOrderNumber(),
            'creation_date' => $order->getCreationDate(),
            'status' => $status,
            'event_name' => $event->getname(),
            'event_start_date' => $event->getEventStartDate(),
            'event_end_date' => $event->getEventEndDate(),
            'event_status' => $event->getStatus(),
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
     * @Route("/events/{id}")
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
