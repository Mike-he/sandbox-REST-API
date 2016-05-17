<?php

namespace Sandbox\ClientApiBundle\Controller\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Error\Error;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Event\Event;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Class ClientEventOrderController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientEventOrderController extends PaymentController
{
    const EVENT_NOT_AVAILABLE_CODE = 400031;
    const EVENT_NOT_AVAILABLE_MESSAGE = 'Event Is Not Available';
    const EVENT_REGISTRATION_NOT_AVAILABLE_CODE = 400032;
    const EVENT_REGISTRATION_NOT_AVAILABLE_MESSAGE = 'Event Registration Is Not Available';
    const WRONG_EVENT_ORDER_STATUS_CODE = 400033;
    const WRONG_EVENT_ORDER_STATUS_MESSAGE = 'Wrong Order Status';
    const EVENT_ORDER_EXIST_CODE = 400034;
    const EVENT_ORDER_EXIST_MESSAGE = 'Event Order Already Exists';

    const PAYMENT_SUBJECT = 'SANDBOX3-活动报名支付';
    const PAYMENT_BODY = 'EVENT ORDER PAYMENT';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Offset of page"
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
        $userId = $this->getUserId();
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $orders = $this->getRepo('Event\EventOrder')->findBy(
            [
                'userId' => $userId,
                'status' => EventOrder::STATUS_COMPLETED,
            ],
            ['modificationDate' => 'DESC'],
            $limit,
            $offset
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_event']));
        $view->setData($orders);

        return $view;
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
    public function getOneEventOrderAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        $orders = $this->getRepo('Event\EventOrder')->findOneBy(array(
            'id' => $id,
            'userId' => $userId,
        ));

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_event']));
        $view->setData($orders);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="event order status"
     * )
     *
     * @Route("/events/{id}/order")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getEventOrderByEventAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $status = $paramFetcher->get('status');
        $userId = $this->getUserId();

        $order = $this->getRepo('Event\EventOrder')->getLastEventOrder(
            $id,
            $userId,
            $status
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_event']));
        $view->setData($order);

        return $view;
    }

    /**
     * @param Request $request
     * @param         $id
     *
     * @Route("/events/{id}/orders")
     * @Method({"POST"})
     *
     * @return View
     */
    public function createEventOrderAction(
        Request $request,
        $id
    ) {
        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime();

        $userId = $this->getUserId();

        $order = new EventOrder();

        // check if event exists
        $event = $this->getRepo('Event\Event')->find($id);
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        // check event
        $error = new Error();
        $this->checkIfAvailable(
            $userId,
            $event,
            $now,
            $error
        );

        if (!is_null($error->getCode())) {
            return $this->customErrorView(
                400,
                $error->getCode(),
                $error->getMessage()
            );
        }

        // generate order number
        $orderNumber = $this->getOrderNumber(EventOrder::LETTER_HEAD);

        // set order
        $order->setEvent($event);
        $order->setUserId($userId);
        $order->setPrice($event->getPrice());
        $order->setStatus(EventOrder::STATUS_UNPAID);
        $order->setOrderNumber($orderNumber);

        $em->persist($order);
        $em->flush();

        $view = new View();
        $view->setData(array(
                'order_id' => $order->getId(),
            ));

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/events/orders/{id}/remaining")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getEventOrderRemainingTimeAction(
        Request $request,
        $id
    ) {
        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime();

        // get event order
        $order = $this->getRepo('Event\EventOrder')->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $status = $order->getStatus();
        $minutes = 0;
        $seconds = 0;

        if ($status == 'unpaid') {
            $creationDate = $order->getCreationDate();
            $remainingTime = $now->diff($creationDate);
            $minutes = $remainingTime->i;
            $seconds = $remainingTime->s;
            $minutes = 4 - $minutes;
            $seconds = 59 - $seconds;
            if ($minutes < 0) {
                $minutes = 0;
                $seconds = 0;
                $order->setStatus(EventOrder::STATUS_CANCELLED);
                $order->setCancelledDate($now);
                $order->setModificationDate($now);

                // delete event registration
                $eventRegistration = $this->getRepo('Event\EventRegistration')->findOneBy(array(
                    'eventId' => $order->getEventId(),
                    'userId' => $order->getUserId(),
                ));
                if (!is_null($eventRegistration)) {
                    $em->remove($eventRegistration);
                }

                $em->flush();
            }
        }

        $view = new View();
        $view->setData(
            [
                'remainingMinutes' => $minutes,
                'remainingSeconds' => $seconds,
            ]
        );

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/events/orders/{id}/pay")
     * @Method({"POST"})
     *
     * @return View
     */
    public function payEventOrderAction(
        Request $request,
        $id
    ) {
        // get event order
        $order = $this->getRepo('Event\EventOrder')->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        // check if request user is the same as order user
        if ($this->getUserId() != $order->getUserId()) {
            return $this->customErrorView(
                400,
                self::WRONG_EVENT_ORDER_STATUS_CODE,
                self::WRONG_EVENT_ORDER_STATUS_MESSAGE
            );
        }

        $requestContent = json_decode($request->getContent(), true);
        $channel = $requestContent['channel'];

        if (
            $channel !== self::PAYMENT_CHANNEL_ALIPAY_WAP &&
            $channel !== self::PAYMENT_CHANNEL_UPACP &&
            $channel !== self::PAYMENT_CHANNEL_UPACP_WAP &&
            $channel !== self::PAYMENT_CHANNEL_ACCOUNT &&
            $channel !== self::PAYMENT_CHANNEL_WECHAT &&
            $channel !== self::PAYMENT_CHANNEL_ALIPAY
        ) {
            return $this->customErrorView(
                400,
                self::WRONG_CHANNEL_CODE,
                self::WRONG_CHANNEL_MESSAGE
            );
        }

        if ($channel === self::PAYMENT_CHANNEL_ACCOUNT) {
            return $this->payByAccount(
                $order,
                $channel
            );
        }

        $orderNumber = $order->getOrderNumber();
        $charge = $this->payForOrder(
            $orderNumber,
            $order->getPrice(),
            $channel,
            self::PAYMENT_SUBJECT,
            self::PAYMENT_BODY
        );
        $charge = json_decode($charge, true);

        return new View($charge);
    }

    /**
     * @param EventOrder $order
     * @param            $channel
     *
     * @return View
     */
    private function payByAccount(
        $order,
        $channel
    ) {
        $price = $order->getPrice();
        $orderNumber = $order->getOrderNumber();
        $balance = $this->postBalanceChange(
            $order->getUserId(),
            (-1) * $price,
            $orderNumber,
            self::PAYMENT_CHANNEL_ACCOUNT,
            $price
        );
        if (is_null($balance)) {
            return $this->customErrorView(
                400,
                self::INSUFFICIENT_FUNDS_CODE,
                self::INSUFFICIENT_FUNDS_MESSAGE
            );
        }

        $order->setStatus(self::STATUS_PAID);
        $order->setPaymentDate(new \DateTime());
        $order->setPayChannel($channel);
        $order->setModificationDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $view = new View();

        return $view->setData(
            array(
                'balance' => $balance,
                'channel' => self::PAYMENT_CHANNEL_ACCOUNT,
            )
        );
    }

    /**
     * @param       $userId
     * @param Event $event
     * @param       $now
     * @param Error $error
     */
    private function checkIfAvailable(
        $userId,
        $event,
        $now,
        $error
    ) {
        $registrationStart = $event->getRegistrationStartDate();
        $registrationEnd = $event->getRegistrationEndDate();

        if (
            $now < $registrationStart ||
            $now > $registrationEnd ||
            !$event->isCharge() ||
            is_null($event->getPrice()) ||
            $event->isDeleted() == true ||
            $event->isVisible() == false
        ) {
            $error->setCode(self::EVENT_NOT_AVAILABLE_CODE);
            $error->setMessage(self::EVENT_NOT_AVAILABLE_MESSAGE);
        }

        // check event registration
        $eventRegistration = $this->getRepo('Event\EventRegistration')->findOneBy(array(
            'eventId' => $event->getId(),
            'userId' => $userId,
        ));

        if (is_null($eventRegistration)) {
            $error->setCode(self::EVENT_REGISTRATION_NOT_AVAILABLE_CODE);
            $error->setMessage(self::EVENT_REGISTRATION_NOT_AVAILABLE_MESSAGE);
        }

        // check event order exists
        $order = $this->getRepo('Event\EventOrder')->getLastEventOrder(
            $event->getId(),
            $userId
        );

        if (!is_null($order) && $order->getStatus() != EventOrder::STATUS_CANCELLED) {
            $error->setCode(self::EVENT_ORDER_EXIST_CODE);
            $error->setMessage(self::EVENT_ORDER_EXIST_MESSAGE);
        }
    }
}
