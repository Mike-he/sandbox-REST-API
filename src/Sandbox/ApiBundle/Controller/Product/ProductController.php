<?php

namespace Sandbox\ApiBundle\Controller\Product;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;

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
    const PRODUCT_NOT_FOUND_CODE = 400012;
    const PRODUCT_NOT_FOUND_MESSAGE = 'Product Not Found';

    /**
     * @return View
     */
    public function getAllProductsAction()
    {
        $products = $this->getRepo('Product\Product')->findAll();

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($products);

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function getOneProduct(
        $id
    ) {
        $product = $this->getRepo('Product\Product')->find($id);
        if (is_null($product)) {
            return $this->customErrorView(
                400,
                self::PRODUCT_NOT_FOUND_CODE,
                self::PRODUCT_NOT_FOUND_MESSAGE
            );
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($product);

        return $view;
    }
}
