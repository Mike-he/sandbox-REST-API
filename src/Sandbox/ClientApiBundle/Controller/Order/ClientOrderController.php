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
    const BAD_REQUEST = 'BAD REQUEST FOR CREATING ORDER FORM';
    const PRICE_MISMATCH = 'PRICE DOES NOT MATCH';
    const TIME_UNIT_MISMATCH = 'TIME UNIT DOES NOT MATCH';
    const WRONG_PAYMENT_STATUS = 'WRONG STATUS';
    const NO_PAYMENT = 'Payment does not exist';
    const USER_EXIST = 'This user already exist';
    const ORDER_NOT_FOUND = 'Can not find order';
    const USER_NOT_FOUND = 'Can not find user in current order';
    const INSUFFICIENT_FUNDS_CODE = 400001;
    const INSUFFICIENT_FUNDS_MESSAGE = 'Insufficient funds in account balance - 余额不足';
    const SYSTEM_ERROR_CODE = 500001;
    const SYSTEM_ERROR_MESSAGE = 'System error - 系统出错';

    /**
     * @Get("/orders/{id}")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getOneOrderAction(
        Request $request,
        $id
    ) {
        $order = $order = $this->getRepo('Order\ProductOrder')->find($id);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($order);

        return $view;
    }

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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getUserOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserid();
        $status = $paramFetcher->get('status');

        if (!is_null($status)) {
            $orders = $this->getRepo('Order\ProductOrder')->findBy(array(
                'userId' => $userId,
                'status' => $status,
            ));
        } else {
            $orders = $this->getRepo('Order\ProductOrder')->findBy(
                ['userId' => $userId]
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
        $userId = $this->getUserid();
        $order = new ProductOrder();

        $form = $this->createForm(new OrderType(), $order);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_REQUEST);
        }
        $product = $this->getRepo('Product\Product')->find($order->getProductId());
        $unit = $product->getUnitPrice();
        $period = $form['rent_period']->getData();
        $timeUnit = $form['time_unit']->getData();

        if ($unit !== $timeUnit) {
            throw new BadRequestHttpException(self::TIME_UNIT_MISMATCH);
        }

        $startDate = new \DateTime($order->getStartDate());
        $endDate = clone $startDate;
        $endDate->modify('+'.$period.$unit);
        $basePrice = $product->getBasePrice();
        $calculatedPrice = $basePrice * $period;

        if ($order->getPrice() !== $calculatedPrice) {
            throw new BadRequestHttpException(self::PRICE_MISMATCH);
        }

        $order->setProduct($product);
        $order->setStartDate($startDate);
        $order->setEndDate($endDate);
        $order->setUserId($userId);
        $order->setLocation('下订单时所在地址');
        $order->setStatus('unpaid');
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        $channel = $form['channel']->getData();
        if ($channel !== 'alipay_wap' && $channel !== 'upacp_wap' && $channel !== 'account') {
            throw new BadRequestHttpException(self::WRONG_CHANNEL);
        }

        $map = $this->createOrderMap($order);

        if ($channel === 'account') {
            $this->payByAccount($order);
        }

        $charge = $this->payForOrder($map->getId(), $order, $channel);
        $charge = json_decode($charge, true);
        $chargeId = $charge['id'];

        $this->setChargeForProductOrder($chargeId, $order->getId());

        $view = new View();
        $view->setData(
            array(
                'id' => $order->getId(),
                'charge_info' => $charge,
            )
        );

        return $view;
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
            throw new BadRequestHttpException(self::WRONG_PAYMENT_STATUS);
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
            throw new BadRequestHttpException(self::WRONG_PAYMENT_STATUS);
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
     * @Post("/orders/{id}/people/add")
     *
     * @param Request $request
     * @param $id
     */
    public function addPeopleAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
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
                throw new BadRequestHttpException(self::USER_EXIST);
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
     * @Delete("/orders/{id}/people/delete")
     *
     * @param Request $request
     * @param $id
     */
    public function deletePeopleAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);
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
            if (is_null($checkUser)) {
                throw new BadRequestHttpException(self::USER_NOT_FOUND);
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
            throw new BadRequestHttpException(self::ORDER_NOT_FOUND);
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
}
