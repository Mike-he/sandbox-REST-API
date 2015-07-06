<?php

namespace Sandbox\ClientApiBundle\Controller\Product;

use Sandbox\ApiBundle\Controller\Product\ProductController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;

/**
 * Rest controller for Client Product.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientProductController extends ProductController
{
    /**
     * @Get("/products/{id}")
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function getOneProductAction(
        Request $request,
        $id
    ) {
        return $this->getOneProduct($id);
    }

        /**
         * @Get("/products")
         *
         * @Annotations\QueryParam(
         *    name="type",
         *    default=null,
         *    nullable=true,
         *    description="
         *        type of room
         *    "
         * )
         *
         * @Annotations\QueryParam(
         *    name="building",
         *    default=null,
         *    nullable=true,
         *    description="
         *        building id
         *    "
         * )
         *
         * @Annotations\QueryParam(
         *    name="start_time",
         *    default=null,
         *    nullable=true,
         *    description="
         *        rent time
         *    "
         * )
         *
         * @Annotations\QueryParam(
         *    name="time_unit",
         *    default=null,
         *    nullable=true,
         *    description="
         *        month|day|hour
         *    "
         * )
         *
         * @Annotations\QueryParam(
         *    name="rent_period",
         *    default=null,
         *    nullable=true,
         *    description="
         *        rent period
         *    "
         * )
         *
         * @Annotations\QueryParam(
         *    name="allowed_people",
         *    default=null,
         *    nullable=true,
         *    description="
         *        maximum allowed people
         *    "
         * )
         *
         * @param Request $request
         * @param ParamFetcherInterface $paramFetcher
         */
        public function getProductsAction(
            Request $request,
            ParamFetcherInterface $paramFetcher
        ) {
            $roomType = $paramFetcher->get('type');
            $buildingId = $paramFetcher->get('building');
            $timeUnit = $paramFetcher->get('time_unit');
            $rentPeriod = $paramFetcher->get('rent_period');
            $allowedPeople = $paramFetcher->get('allowed_people');
            $startTime = $paramFetcher->get('start_time');
            $startTime = new \DateTime($startTime);
            if (!is_null($startTime)) {
                $endTime = clone $startTime;
                $endTime->modify('+'.$rentPeriod.$timeUnit);
            }
            $userId = $this->getUserId();

            $productIds = $this->getRepo('Product\Product')->getProductsForClient(
                $roomType,
                $buildingId,
                $startTime,
                $timeUnit,
                $endTime,
                $allowedPeople,
                $userId
            );

            $products = [];
            foreach ($productIds as $productId) {
                $product = $this->getRepo('Product\Product')->find($productId);
                array_push($products, $product);
            }

            $view = new View();
            $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
            $view->setData($products);

            return $view;
        }
}
