<?php

namespace Sandbox\ClientApiBundle\Controller\Shop;

use Sandbox\ApiBundle\Controller\Shop\ShopMenuController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Shop\Shop;

/**
 * Client Shop Menu Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xue <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientShopMenuController extends ShopMenuController
{
    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"GET"})
     * @Route("/shops/{id}/menus")
     *
     * @return View
     */
    public function getShopsByBuildingAction(
        Request $request,
        $id
    ) {
        $shops = $this->getRepo('Shop\ShopMenu')->getShopMenuByShop($id);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($shops);

        return $view;
    }
}
