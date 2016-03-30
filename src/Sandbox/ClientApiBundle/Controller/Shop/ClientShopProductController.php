<?php

namespace Sandbox\ClientApiBundle\Controller\Shop;

use Sandbox\ApiBundle\Controller\Shop\ShopProductController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * Client ShopProduct Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientShopProductController extends ShopProductController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @Method({"GET"})
     * @Route("/shops/{shopId}/menus/{id}/products")
     *
     * @return View
     */
    public function getShopProductsByMenuAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $products = $this->getRepo('Shop\ShopProduct')->getShopProductsByMenu(
            $id,
            $limit,
            $offset
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['product_view']));
        $view->setData($products);

        return $view;
    }

    /**
     * @param Request $request
     * @param int     $shopId
     * @param int     $menuId
     * @param int     $id
     *
     *
     * @Method({"GET"})
     * @Route("/shops/{shopId}/menus/{menuId}/products/{id}")
     *
     * @return View
     */
    public function getShopProductByIdAction(
        Request $request,
        $shopId,
        $menuId,
        $id
    ) {
        $product = $this->getRepo('Shop\ShopProduct')->getShopProductByShopId(
            $shopId,
            $id,
            true
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['product_view']));
        $view->setData($product);

        return $view;
    }
}
