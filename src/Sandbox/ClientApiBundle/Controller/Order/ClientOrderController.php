<?php

namespace Sandbox\ClientApiBundle\Controller\Order;

use Sandbox\ApiBundle\Controller\Order\OrderController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Sandbox\ApiBundle\Entity\Product\ProductOrder;
use Symfony\Component\Validator\Constraints\DateTime;
use Sandbox\ApiBundle\Form\Order\OrderType;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Rest controller for Client Orders
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class ClientOrderController extends OrderController
{
    const BAD_REQUEST = 'BAD REQUEST FOR CREATING ORDER FORM';
    const PRICE_MISMATCH = 'PRICE DOES NOT MATCH';
    const TIME_UNIT_MISMATCH = 'TIME UNIT DOES NOT MATCH';

    /**
     * Get all orders for current user
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
     * @param  Request               $request
     * @param  ParamFetcherInterface $paramFetcher
     * @return View
     */
    public function getUserOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserid();
        $status = $paramFetcher->get('status');

        if (!is_null($status)) {
            $orders = $this->getRepo('Product\ProductOrder')->findBy(array(
                'userId' => $userId,
                'status' => $status,
            ));

            return new View($orders);
        }
        $orders = $this->getRepo('Product\ProductOrder')->findBy(['userId' => $userId]);

        return new View($orders);
    }

    /**
     * Create orders
     *
     * @Post("/orders")
     *
     *
     *
     * @param  Request $request
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

        //TODO Payment

        $view = new View();
        $view->setData(array('id' => $order->getId()));

        return $view;
    }
}
