<?php

namespace Sandbox\ApiBundle\Controller\Order;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;

/**
 * Order Controller
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class OrderController extends SandboxRestController
{
    /**
     * @Get("/orders")
     *
     * @param  Request $request
     * @return View
     */
    public function getAllOrdersAction(
        Request $request
    ) {
        $orders = $this->getRepo('Product\ProductOrder')->findAll();

        return new View($orders);
    }

    /**
     * @Get("/orders/{id}")
     *
     * @param  Request $request
     * @return View
     */
    public function getOneOrderAction(
        Request $request,
        $id
    ) {
        $order = $this->getRepo('Product\ProductOrder')->find($id);

        return new View($order);
    }
}
