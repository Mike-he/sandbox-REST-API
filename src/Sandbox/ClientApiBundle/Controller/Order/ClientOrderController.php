<?php

namespace Sandbox\ClientApiBundle\Controller\Order;

use Sandbox\ApiBundle\Controller\Order\OrderController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;

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
    public function getUserOrdersAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
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
}
