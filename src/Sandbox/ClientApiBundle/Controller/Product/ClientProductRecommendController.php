<?php

namespace Sandbox\ClientApiBundle\Controller\Product;

use Sandbox\ApiBundle\Controller\Product\ProductController;
use Sandbox\ApiBundle\Entity\Room\Room;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;

/**
 * Rest controller for Client Product Recommend.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientProductRecommendController extends ProductController
{
    /**
     * @Get("/products/recommend")
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    default=null,
     *    nullable=true,
     *    description="city id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default=10,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for the page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default=0,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="start of the page"
     * )
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getProductsRecommendAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $cityId = $paramFetcher->get('city');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get city
        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;

        // find recommend products
        $products = $this->getRepo('Product\Product')->getProductsRecommend(
            $city, $limit, $offset, true
        );
        $recommendCount = count($products);

        // get total of recommend products
        $recommendTotal = (int) $this->getRepo('Product\Product')->getProductsRecommendCount(true);

        // add up products that are not recommend
        if ($limit > $recommendCount) {
            $offset = $offset - $recommendTotal;
            if ($offset < 0) {
                $offset = 0;
            }

            $limit = $limit - $recommendCount;

            $notRecommends = $this->getRepo('Product\Product')->getProductsRecommend(
                $city, $limit, $offset, false
            );

            $products = array_merge($products, $notRecommends);
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($products);

        return $view;
    }
}