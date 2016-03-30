<?php

namespace Sandbox\ClientApiBundle\Controller\Shop;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Shop\ShopController;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * Client Shop Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientShopController extends ShopController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="Filter by building"
     * )
     *
     * @Method({"GET"})
     * @Route("/shops")
     *
     * @return View
     */
    public function getShopsByBuildingAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $buildingId = $paramFetcher->get('building');

        $shops = $this->getRepo('Shop\Shop')->getShopByBuilding(
            $buildingId,
            true,
            true
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_shop']));
        $view->setData($shops);

        return $view;
    }

    /**
     * Get shop by Id.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/shops/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getShopByIdAction(
        Request $request,
        $id
    ) {
        $shop = $this->getRepo('Shop\Shop')->getShopById(
            $id,
            true,
            true
        );
        $this->throwNotFoundIfNull($shop, self::NOT_FOUND_MESSAGE);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_shop']));
        $view->setData($shop);

        return $view;
    }
}
