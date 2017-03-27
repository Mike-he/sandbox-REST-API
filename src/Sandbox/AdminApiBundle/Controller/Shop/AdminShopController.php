<?php

namespace Sandbox\AdminApiBundle\Controller\Shop;

use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Form\Shop\ShopPatchActiveType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Shop\ShopController;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use Symfony\Component\HttpFoundation\Response;
use Rs\Json\Patch;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;

/**
 * Admin Shop Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminShopController extends ShopController
{
    /**
     * patch shop status.
     *
     * @param Request $request
     * @param $id
     *
     * @Method({"PATCH"})
     * @Route("/shops/{id}")
     *
     * @return Response
     */
    public function patchShopAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminShopPermission(AdminPermission::OP_LEVEL_EDIT);

        $shop = $this->findShopById($id);

        // bind data
        $shopJson = $this->get('serializer')->serialize($shop, 'json');
        $patch = new Patch($shopJson, $request->getContent());
        $shopJson = $patch->apply();

        $this->patchShop(
            $shop,
            $shopJson,
            new ShopPatchActiveType()
        );

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="company",
     *     array=false,
     *     default=null,
     *     nullable=false,
     *     strict=true,
     *     description="company id"
     * )
     *
     * @Annotations\QueryParam(
     *     name="close",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true,
     *     description="shop close status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many admins to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Route("/sales/shops")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getShopsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminShopPermission(AdminPermission::OP_LEVEL_VIEW);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $companyId = $paramFetcher->get('company');
        $close = $paramFetcher->get('close');

        $shops = $this->getRepo('Shop\Shop')->getShopsByCompany(
            $companyId,
            $close
        );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $shops,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"GET"})
     * @Route("/shops/{id}")
     *
     * @return View
     */
    public function getShopByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminShopPermission(AdminPermission::OP_LEVEL_VIEW);

        $shop = $this->getRepo('Shop\Shop')->getShopById($id);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($shop);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by building"
     * )
     *
     * @Method({"GET"})
     * @Route("/shops")
     *
     * @return View
     */
    public function getShopByBuildingAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        $buildingId = $paramFetcher->get('building');
        $shops = $this->getRepo('Shop\Shop')->getShopByBuilding($buildingId);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($shops);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="Filter by city"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Method({"GET"})
     * @Route("/buildings")
     *
     * @return View
     */
    public function getBuildingByCityAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminShopPermission(AdminPermission::OP_LEVEL_VIEW);

        $cityId = $paramFetcher->get('city');
        $buildings = $this->getRepo('Room\RoomBuilding')->findByCityId($cityId);

        $buildings = $this->get('serializer')->serialize(
            $buildings,
            'json',
            SerializationContext::create()->setGroups(['admin_shop'])
        );
        $buildings = json_decode($buildings, true);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $buildings,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param Shop   $shop
     * @param string $shopJson
     * @param string $type
     */
    private function patchShop(
        $shop,
        $shopJson,
        $type
    ) {
        $form = $this->createForm($type, $shop);
        $form->submit(json_decode($shopJson, true));

        if (!$shop->isActive()) {
            $shop->setOnline(false);
            $shop->setClose(true);

            // set shop products offline
            $this->getRepo('Shop\ShopProduct')->setShopProductsOfflineByShopId(
                $shop->getId()
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminShopPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SHOP_ORDER],
            ],
            $opLevel
        );
    }
}
