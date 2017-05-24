<?php

namespace Sandbox\ClientApiBundle\Controller\Product;

use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\Product\ProductController;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomTypeTags;
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
     * @Annotations\QueryParam(
     *    name="exclude_company_id",
     *    array=true,
     *    nullable=true,
     *    default=null,
     *    description="exclude_company_id"
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
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        // get params
        $cityId = $paramFetcher->get('city');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $excludeIds = $paramFetcher->get('exclude_company_id');

        // get city
        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;

        // find recommend products
        $products = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->getProductsRecommend(
                $userId,
                $city,
                $excludeIds,
                $limit,
                $offset,
                true
        );
//        $recommendCount = count($products);

//        // get total of recommend products
//        $recommendTotal = (int) $this->getRepo('Product\Product')->getProductsRecommendCount(
//            $userId, $city, true
//        );

//        // add up products that are not recommend
//        if ($limit > $recommendCount) {
//            $offset = $offset - $recommendTotal;
//            if ($offset < 0) {
//                $offset = 0;
//            }

//            $limit = $limit - $recommendCount;

//            $notRecommends = $this->getRepo('Product\Product')->getProductsRecommend(
//                $userId, $city, $limit, $offset, false
//            );

//            $products = array_merge($products, $notRecommends);
//        }

        foreach ($products as $product) {
            $room = $product->getRoom();
            $type = $room->getType();
            $typeTag = $room->getTypeTag();
            if (!is_null($typeTag)) {
                $typeTagDescription = $this->get('translator')->trans(RoomTypeTags::TRANS_PREFIX.$typeTag);
                $room->setTypeTagDescription($typeTagDescription);
            }

            $productLeasingSets = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
                ->findBy(array('product' => $product));

            $basePrice = [];
            foreach ($productLeasingSets as $productLeasingSet) {
                $unitPrice = $this->get('translator')
                    ->trans(ProductOrderExport::TRANS_ROOM_UNIT.$productLeasingSet->getUnitPrice());
                $productLeasingSet->setUnitPrice($unitPrice);

                $basePrice[$unitPrice] = $productLeasingSet->getBasePrice();
            }
            $product->setLeasingSets($productLeasingSets);

            if ($type == Room::TYPE_DESK && $typeTag == Room::TAG_DEDICATED_DESK) {
                $price = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomFixed')
                    ->getFixedSeats($room);
                if (!is_null($price)) {
                    $product->setBasePrice($price);
                    $product->setUnitPrice($unitPrice);
                }
            } else {
                $pos = array_search(min($basePrice), $basePrice);
                $product->setBasePrice($basePrice[$pos]);
                $product->setUnitPrice($pos);
            }

            $productRentSet = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\ProductRentSet')
                ->findOneBy(array('product' => $product));

            $product->setRentSet($productRentSet);
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($products);

        return $view;
    }
}
