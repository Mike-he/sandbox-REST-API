<?php

namespace Sandbox\ApiBundle\Controller\Product;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;

/**
 * Product Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ProductController extends SandboxRestController
{
    /**
     * @Get("/products")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getAllProductsAction(
        Request $request
    ) {
        $products = $this->getRepo('Product\Product')->findAll();

        return new View($products);
    }

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
        $product = $this->getRepo('Product\Product')->find($id);

        return new View($product);
    }
}
