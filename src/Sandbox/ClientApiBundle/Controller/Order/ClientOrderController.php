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

            return new View($orders);
        }
        $orders = $this->getRepo('Order\ProductOrder')->findBy(
            ['userId' => $userId]
        );

        return new View($orders);
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
        $order->setStatus('unpaid');
        $order->setCreationDate(new \DateTime());
        $order->setModificationDate(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        $channel = $form['channel']->getData();
        if ($channel !== 'alipay_wap' && $channel !== 'upacp_wap') {
            throw new BadRequestHttpException(self::WRONG_CHANNEL);
        }

        $map = $this->createOrderMap($order);

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
        if ($channel !== 'alipay_wap' && $channel !== 'upacp_wap') {
            throw new BadRequestHttpException(self::WRONG_CHANNEL);
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
        $map = $this->getRepo('Order\OrderMap')->findOneBy(
            [
                'type' => 'product',
                'orderId' => $id,
            ]
        );
        $chargeId = $map->getChargeId();
        $refund = $this->refundForOrder($chargeId);
        $refund = json_decode($refund, true);
        if (is_null($refund)) {
            throw new BadRequestHttpException(self::NO_PAYMENT);
        }

        return new View($refund);
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
            $people = new InvitedPeople();
            $people->setOrderId($order);
            $people->setUserId($user['user_id']);
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
        $userId = $request->get('user_id');
        $people = $this->getRepo('Order\InvitedPeople')->findOneBy(
            [
                'userId' => $userId,
                'orderId' => $id,
            ]
        );

        $em = $this->getDoctrine()->getManager();
        $em->remove($people);
        $em->flush();
    }
}
