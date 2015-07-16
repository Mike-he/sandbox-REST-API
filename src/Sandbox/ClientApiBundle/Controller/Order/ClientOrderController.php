<?php

namespace Sandbox\ClientApiBundle\Controller\Order;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Order\InvitedPeople;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Form\Order\OrderType;
use Sandbox\ApiBundle\Entity\Order\OrderMap;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;

/**
 * Rest controller for Client Orders.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientOrderController extends PaymentController
{
    const NO_PAYMENT = 'Payment does not exist';
    const INSUFFICIENT_FUNDS_CODE = 400001;
    const INSUFFICIENT_FUNDS_MESSAGE = 'Insufficient funds in account balance - 余额不足';
    const SYSTEM_ERROR_CODE = 500001;
    const SYSTEM_ERROR_MESSAGE = 'System error - 系统出错';
    const PAYMENT_SUBJECT = 'ROOM';
    const PAYMENT_BODY = 'ROOM ORDER';
    const PRODUCT_ORDER_LETTER_HEAD = 'P';
    const INVALID_FORM_CODE = 400002;
    const INVALID_FORM_MESSAGE = 'Invalid Form';
    const PRODUCT_NOT_FOUND_CODE = 400003;
    const PRODUCT_NOT_FOUND_MESSAGE = 'Product Does Not Exist';
    const ORDER_CONFLICT_CODE = 400004;
    const ORDER_CONFLICT_MESSAGE = 'Order Conflict';
    const PRICE_MISMATCH_CODE = 400005;
    const PRICE_MISMATCH_MESSAGE = 'PRICE DOES NOT MATCH';
    const WRONG_PAYMENT_STATUS_CODE = 400006;
    const WRONG_PAYMENT_STATUS_MESSAGE = 'WRONG STATUS';
    const ORDER_NOT_FOUND_CODE = 400007;
    const ORDER_NOT_FOUND_MESSAGE = 'Can not find order';
    const USER_NOT_FOUND_CODE = 400008;
    const USER_NOT_FOUND_MESSAGE = 'Can not find user in current order';
    const USER_EXIST_CODE = 400009;
    const USER_EXIST_MESSAGE = 'This user already exist';

    /**
     * Get all orders for current user.
     *
     * @Get("/orders/my")
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    default=null,
     *    nullable=true,
     *    description="
     *        maximum allowed people
     *    "
     * )
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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getUserOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $status = $paramFetcher->get('status');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        if (!is_null($status)) {
            $orders = $this->getRepo('Order\ProductOrder')->findBy(
                [
                    'userId' => $userId,
                    'status' => $status,
                ],
                ['creationDate' => 'DESC'],
                $limit,
                $offset
            );
        } else {
            $orders = $this->getRepo('Order\ProductOrder')->findBy(
                ['userId' => $userId],
                ['creationDate' => 'DESC'],
                $limit,
                $offset
            );
        }
        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($orders);

        return $view;
    }

    /**
     * Create orders.
     *
     * @Post("/orders")
     *
     * @param Request $request
     *
     * @return View
     */
    public function createOrdersAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $order = new ProductOrder();

        $form = $this->createForm(new OrderType(), $order);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return $this->customErrorView(
                400,
                self::INVALID_FORM_CODE,
                self::INVALID_FORM_MESSAGE
            );
        }
        $productId = $order->getProductId();
        $product = $this->getRepo('Product\Product')->find($productId);
        if (is_null($product)) {
            return $this->customErrorView(
                400,
                self::PRODUCT_NOT_FOUND_CODE,
                self::PRODUCT_NOT_FOUND_MESSAGE
            );
        }

        $period = $form['rent_period']->getData();
        $timeUnit = $form['time_unit']->getData();
        $datePeriod = $period;
        if ($timeUnit === 'hour') {
            $datePeriod = $period * 60;
            $timeUnit = 'min';
        }

        $startDate = new \DateTime($order->getStartDate());
        $endDate = clone $startDate;
        $endDate->modify('+'.$datePeriod.$timeUnit);
        $basePrice = $product->getBasePrice();

        $checkOrder = $this->getRepo('Order\ProductOrder')->checkProductForClient(
            $productId,
            $startDate,
            $endDate
        );

        if (!empty($checkOrder) && !is_null($checkOrder)) {
            return $this->customErrorView(
                400,
                self::ORDER_CONFLICT_CODE,
                self::ORDER_CONFLICT_MESSAGE
            );
        }

        $calculatedPrice = $basePrice * $period;

        if ($order->getPrice() !== $calculatedPrice) {
            return $this->customErrorView(
                400,
                self::PRICE_MISMATCH_CODE,
                self::PRICE_MISMATCH_MESSAGE
            );
        }

        $orderNumber = $this->getOrderNumber(self::PRODUCT_ORDER_LETTER_HEAD);

        $order->setOrderNumber($orderNumber);
        $order->setProduct($product);
        $order->setStartDate($startDate);
        $order->setEndDate($endDate);
        $order->setUserId($userId);
        $order->setLocation('location');
        $order->setStatus('unpaid');
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        $channel = $form['channel']->getData();
        if ($channel !== 'alipay_wap' && $channel !== 'upacp_wap' && $channel !== 'account') {
            throw new BadRequestHttpException(self::WRONG_CHANNEL);
        }
        if ($channel === 'account') {
            $this->payByAccount($order);
        }

        $this->createOrderMap($order);
        $charge = $this->payForOrder(
            $orderNumber,
            $order->getPrice(),
            $channel,
            self::PAYMENT_SUBJECT,
            self::PAYMENT_BODY
        );
        $charge = json_decode($charge, true);
        $chargeId = $charge['id'];

        $this->setChargeForProductOrder($chargeId, $order->getId());

        return new View($charge);
    }

    private function payByAccount($order)
    {
        //TODO Call CRM API to get current balance
        $balance = 500;
        if ($order->getPrice() > $balance) {
            return $this->customErrorView(
                400,
                self::INSUFFICIENT_FUNDS_CODE,
                self::INSUFFICIENT_FUNDS_MESSAGE
            );
        }
        //TODO Call CRM API to subtract price from current balance
        $newBalance = $balance - $order->getPrice();
        //TODO Call CRM API to get current balance AGAIN
        $updatedbalance = $newBalance;
        if ($newBalance !== $updatedbalance) {
            return $this->customErrorView(
                500,
                self::SYSTEM_ERROR_CODE,
                self::SYSTEM_ERROR_MESSAGE
            );
        }

        $order->setStatus(self::STATUS_PAID);
        $order->setPaymentDate(new \DateTime());
        $order->setModificationDate(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        $view = new View();
        $view->setData(
            array(
                'id' => $order->getId(),
            )
        );

        return $view;
    }

    /**
     * @param $chargeId
     * @param $orderId
     */
    private function setChargeForProductOrder($chargeId, $orderId)
    {
        $map = $this->getRepo('Order\OrderMap')->findOneBy(
            [
                'type' => 'product',
                'orderId' => $orderId,
            ]
        );
        $map->setChargeId($chargeId);
        $em = $this->getDoctrine()->getManager();
        $em->persist($map);
        $em->flush();
    }

    /**
     * @param $order
     *
     * @return OrderMap
     */
    private function createOrderMap($order)
    {
        $map = new OrderMap();
        $map->setType('product');
        $map->setOrderId($order->getId());
        $em = $this->getDoctrine()->getManager();
        $em->persist($map);
        $em->flush();

        return $map;
    }

    /**
     * @Post("/orders/{id}/pay")
     *
     * @param Request $request
     * @param $id
     */
    public function payAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if ($order->getStatus() !== 'unpaid') {
            return $this->customErrorView(
                400,
                self::WRONG_PAYMENT_STATUS_CODE,
                self::WRONG_PAYMENT_STATUS_MESSAGE
            );
        }

        $channel = $request->get('channel');
        if ($channel !== 'alipay_wap' && $channel !== 'upacp_wap' && $channel !== 'account') {
            throw new BadRequestHttpException(self::WRONG_CHANNEL);
        }

        if ($channel === 'account') {
            $this->payByAccount($order);
        }

        $map = $this->getRepo('Order\OrderMap')->findOneBy(
            [
                'type' => 'product',
                'orderId' => $order->getId(),
            ]
        );
        $chargeId = $map->getChargeId();
        $charge = $this->getChargeDetail($chargeId);
        $charge = json_decode($charge, true);

        return new View($charge);
    }

    /**
     * @Post("/orders/{id}/refund")
     *
     * @param Request $request
     * @param $id
     */
    public function refundAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if ($order->getStatus() !== 'paid') {
            return $this->customErrorView(
                400,
                self::WRONG_PAYMENT_STATUS_CODE,
                self::WRONG_PAYMENT_STATUS_MESSAGE
            );
        }
        $price = $order->getPrice();
        //TODO: CALL CRM API to get current balance $balance
        $balance = 500;
        //TODO: CALL CRM API to request add $price to balance
        $newBalance = $balance + $price;
        //TODO: CALL CRM API to get updated balance $updatedBalance
        $updatedBalance = $newBalance;
        if ($updatedBalance !== $newBalance) {
            return $this->customErrorView(
                500,
                self::SYSTEM_ERROR_CODE,
                self::SYSTEM_ERROR_MESSAGE
            );
        }

        $order->setStatus('cancelled');
        $order->setCancelledDate(new \DateTime());
        $order->setModificationDate(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        $people = $this->getRepo('Order\InvitedPeople')->findBy(
            [
                'orderId' => $id,
            ]
        );
        if (empty($people)) {
            return;
        }
        foreach ($people as $user) {
            $userId = $user->getUserId();
            //TODO: Remove access for every user
        }
    }

    /**
     * @Post("/orders/{id}/people")
     *
     * @param Request $request
     * @param $id
     */
    public function addPeopleAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }
        $status = $order->getStatus();
        $endDate = $order->getEndDate();
        $now = new \DateTime();
        if ($status !== 'paid' && $status !== 'completed' && $now > $endDate) {
            throw new BadRequestHttpException(self::WRONG_PAYMENT_STATUS);
        }
        $users = json_decode($request->getContent(), true);
        foreach ($users as $user) {
            $checkUser = $this->getRepo('Order\InvitedPeople')->findOneBy(
                [
                    'orderId' => $id,
                    'userId' => $user['user_id'],
                ]
            );
            if (!is_null($checkUser)) {
                return $this->customErrorView(
                    400,
                    self::USER_EXIST_CODE,
                    self::USER_EXIST_MESSAGE
                );
            }
            $people = new InvitedPeople();
            $people->setOrderId($order);
            $people->setUserId($user['user_id']);
            $people->setCreationDate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($people);
            $em->flush();
        }
    }

    /**
     * @Delete("/orders/{id}/people")
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    default="",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Offset of page"
     * )
     *
     * @param Request $request
     * @param $id
     * @param ParamFetcherInterface $paramFetcher
     */
    public function deletePeopleAction(
        Request $request,
        $id,
        ParamFetcherInterface $paramFetcher
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }
        $status = $order->getStatus();
        $endDate = $order->getEndDate();
        $now = new \DateTime();
        if ($status !== 'paid' && $status !== 'completed' && $now > $endDate) {
            throw new BadRequestHttpException(self::WRONG_PAYMENT_STATUS);
        }
        $userIds = $paramFetcher->get('id');

        if (empty($userIds)) {
            return $this->customErrorView(
                400,
                self::USER_NOT_FOUND_CODE,
                self::USER_NOT_FOUND_MESSAGE
            );
        }

        foreach ($userIds as $userId) {
            $checkUser = $this->getRepo('Order\InvitedPeople')->findOneBy(
                [
                    'orderId' => $id,
                    'userId' => $userId,
                ]
            );
            if (is_null($checkUser)) {
                return $this->customErrorView(
                    400,
                    self::USER_NOT_FOUND_CODE,
                    self::USER_NOT_FOUND_MESSAGE
                );
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($checkUser);
            $em->flush();
        }
    }

    /**
     * @Get("/orders/{id}/invited")
     *
     * @param Request $request
     * @param $id
     */
    public function getInvitedPeopleAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }
        $people = $this->getRepo('Order\InvitedPeople')->findBy(
            ['orderId' => $id]
        );

        $users = [];
        foreach ($people as $person) {
            $userId = $person->getUserId();
            $user = $this->getRepo('User\UserProfile')->findOneBy(['userId' => $userId]);
            array_push($users, $user);
        }

        return new View($users);
    }

    /**
     * @Post("/orders/{id}/person/appoint")
     *
     * @param Request $request
     * @param $id
     */
    public function appointPersonAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }
        $status = $order->getStatus();
        $endDate = $order->getEndDate();
        $now = new \DateTime();
        if ($status !== 'paid' && $status !== 'completed' && $now > $endDate) {
            throw new BadRequestHttpException(self::WRONG_PAYMENT_STATUS);
        }
        $user = $request->get('user_id');

        if (is_null($user)) {
            $order->setAppointed(null);
            $order->setModificationDate(new \DateTime());
        } else {
            $order->setAppointed($user);
            $order->setModificationDate(new \DateTime());
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();
    }

    /**
     * @Get("/orders/{id}")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getOneOrderAction(
        Request $request,
        $id
    ) {
        $order = $order = $this->getRepo('Order\ProductOrder')->find($id);
        if (is_null($order)) {
            return $this->customErrorView(
                400,
                self::ORDER_NOT_FOUND_CODE,
                self::ORDER_NOT_FOUND_MESSAGE
            );
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($order);

        return $view;
    }
}
