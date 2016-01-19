<?php

namespace Sandbox\ApiBundle\Controller\Order;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

/**
 * Order Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class OrderController extends PaymentController
{
    /**
     * @param Request $request
     *
     * @return View
     */
    public function getAllOrders()
    {
        $orders = $this->getRepo('Order\ProductOrder')->findAll();

        return $orders;
    }

    /**
     * @param $id
     *
     * @return View
     */
    public function getOneOrder(
        $id
    ) {
        $order = $this->getRepo('Order\ProductOrder')->find($id);

        return $order;
    }
}
