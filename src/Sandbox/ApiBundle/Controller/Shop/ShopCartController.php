<?php

namespace Sandbox\ApiBundle\Controller\Shop;

use Sandbox\AdminShopApiBundle\Controller\ShopRestController;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use Sandbox\ApiBundle\Entity\Shop\ShopCart;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

/**
 * Shop Cart Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ShopCartController extends ShopRestController
{
    /**
     * Post shopping cart.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/shops/{id}/cart")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postShopCartAction(
        Request $request,
        $id
    ) {
        $shop = $this->getRepo('Shop\Shop')->getShopById(
            $id,
            true,
            true
        );
        $this->throwNotFoundIfNull($shop, self::NOT_FOUND_MESSAGE);

        // check shop is closed
        if ($shop->isClose()) {
            return $this->customErrorView(
                400,
                Shop::CLOSED_CODE,
                Shop::CLOSED_MESSAGE
            );
        }

        // check shop opening hours
        $now = new \DateTime();
        if ($now < $shop->getStartHour() || $now >= $shop->getEndHour()) {
            return $this->customErrorView(
                400,
                Shop::CLOSED_CODE,
                Shop::CLOSED_MESSAGE
            );
        }

        $content = $request->getContent();

        $cart = new ShopCart();
        $cart->setShopId($id);
        $cart->setCartInfo($content);

        $em = $this->getDoctrine()->getManager();
        $em->persist($cart);
        $em->flush();

        $view = new View();
        $view->setData(['id' => $cart->getId()]);

        return $view;
    }

    /**
     * Get shopping cart.
     *
     * @param Request $request
     * @param int     $shopId
     * @param int     $id
     *
     * @Route("/shops/{shopId}/cart/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getShopCartInfoByIdAction(
        Request $request,
        $shopId,
        $id
    ) {
        $cart = $this->getRepo('Shop\ShopCart')->findOneBy(
            [
                'id' => $id,
                'shopId' => $shopId,
            ]
        );
        $this->throwNotFoundIfNull($cart, self::NOT_FOUND_MESSAGE);

        $cartInfo = json_decode($cart->getCartInfo(), true);

        return new View($cartInfo);
    }
}
