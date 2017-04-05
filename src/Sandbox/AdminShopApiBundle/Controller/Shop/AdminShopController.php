<?php

namespace Sandbox\AdminShopApiBundle\Controller\Shop;

use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Shop\ShopController;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use Sandbox\ApiBundle\Entity\Shop\ShopAttachment;
use Sandbox\ApiBundle\Form\Shop\ShopPostType;
use Sandbox\ApiBundle\Form\Shop\ShopPutType;
use Sandbox\ApiBundle\Form\Shop\ShopPatchOnlineType;
use Sandbox\ApiBundle\Form\Shop\ShopPatchCloseType;
use Sandbox\ApiBundle\Form\Shop\ShopAttachmentPostType;
use Symfony\Component\HttpFoundation\Response;
use Rs\Json\Patch;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Entity\Shop\ShopSpec;
use Sandbox\ApiBundle\Entity\Shop\ShopSpecItem;
use Sandbox\ApiBundle\Form\Shop\ShopSpecPostType;
use Sandbox\ApiBundle\Form\Shop\ShopSpecItemPostType;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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
     * @param Request $request
     *
     * @Method({"POST"})
     * @Route("/shops")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postShopAction(
        Request $request
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_PLATFORM_SHOP,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $shop = new Shop();

        $form = $this->createForm(new ShopPostType(), $shop);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleShopPost(
            $shop
        );
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"PUT"})
     * @Route("/shops/{id}")
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function putShopAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_SHOP,
                    'shop_id' => $id,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_PLATFORM_SHOP,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $shop = $this->findShopById($id);
        $shopName = $shop->getName();

        $form = $this->createForm(
            new ShopPutType(),
            $shop,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleShopPut(
            $shop,
            $shopName
        );
    }

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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_SHOP,
                    'shop_id' => $id,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_PLATFORM_SHOP,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $shop = $this->findShopById($id);

        if (!$shop->isActive()) {
            return $this->customErrorView(
                400,
                Shop::SHOP_INACTIVE_CODE,
                Shop::SHOP_INACTIVE_MESSAGE
            );
        }

        $type = null;
        $contentJson = $request->getContent();
        $content = json_decode($contentJson, true)[0];

        switch ($content['path']) {
            case Shop::PATH_CLOSE:
                $type = new ShopPatchCloseType();
                break;
            case Shop::PATH_ONLINE:
                $type = new ShopPatchOnlineType();
                break;
        }

        if (is_null($type)) {
            return;
        }

        // bind data
        $shopJson = $this->get('serializer')->serialize($shop, 'json');
        $patch = new Patch($shopJson, $contentJson);
        $shopJson = $patch->apply();

        $this->patchShop(
            $shop,
            $shopJson,
            $type
        );

        return new View();
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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_SHOP,
                    'shop_id' => $id,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
                    'shop_id' => $id,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_PLATFORM_SHOP,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

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
     *  @Annotations\QueryParam(
     *    name="permission",
     *    array=true,
     *    default="shop.shop.shop",
     *    nullable=true,
     *    strict=true,
     *    description="Filter by permission"
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
        $adminId = $this->getAdminId();

        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_PLATFORM_ADMIN,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_PLATFORM_SHOP,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_SHOP,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_PRODUCT,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_ADMIN,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_ORDER,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $permissions = $paramFetcher->get('permission');

        $shopIds = $this->getMyShopIds(
            $adminId,
            $permissions,
            AdminPermission::OP_LEVEL_VIEW
        );

        $buildingId = $paramFetcher->get('building');
        $shops = $this->getRepo('Shop\Shop')->getShopByBuilding(
            $buildingId,
            false,
            false,
            $shopIds
        );

        foreach ($shops as $shop) {
            $menus = $this->getRepo('Shop\ShopMenu')->findBy(
                [
                    'invisible' => false,
                    'shop' => $shop,
                ]
            );

            $shop->setMenuCount(count($menus));
            $onlineCount = 0;
            $offlineCount = 0;

            foreach ($menus as $menu) {
                $onlineProducts = $this->getRepo('Shop\ShopProduct')->findBy(
                    [
                        'menu' => $menu,
                        'online' => true,
                        'invisible' => false,
                    ]
                );

                $offlineProducts = $this->getRepo('Shop\ShopProduct')->findBy(
                    [
                        'menu' => $menu,
                        'invisible' => false,
                        'online' => false,
                    ]
                );

                $onlineCount += count($onlineProducts);
                $offlineCount += count($offlineProducts);
            }

            $shop->setOnlineProductCount($onlineCount);
            $shop->setOfflineProductCount($offlineCount);
        }

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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_SHOP,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_PLATFORM_SHOP,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

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
     * @param $shop
     * @param $form
     *
     * @return View
     */
    private function handleShopPost(
        $shop
    ) {
        // check building
        $building = $this->getRepo('Room\RoomBuilding')->find($shop->getBuildingId());
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        // check for shop with same name
        $this->findConflictShop($shop);

        $em = $this->getDoctrine()->getManager();

        $shopAttachments = $shop->getAttachments();
        $startString = $shop->getStart();
        $endString = $shop->getEnd();

        // set startHour and endHour
        $this->setHours(
            $shop,
            $startString,
            $endString
        );

        // add building
        $shop->setBuilding($building);

        // add shop attachments
        $this->addShopAttachments(
            $shop,
            $shopAttachments,
            $em
        );

//        $this->createAutoSpec($shop, $em);

        $em->persist($shop);
        $em->flush();

        $view = new View();
        $view->setData(['id' => $shop->getId()]);

        return $view;
    }

//    /**
//     * @param $shop
//     * @param $em
//     */
//    private function createAutoSpec(
//        $shop,
//        $em
//    ) {
//        $content = [
//            'name' => ShopSpec::AUTO_SPEC_NAME,
//            'inventory' => true,
//            'items' => [
//                'name' => ShopSpecItem::AUTO_SPEC_ITEM_NAME,
//            ],
//        ];

//        $spec = new ShopSpec();

//        $form = $this->createForm(new ShopSpecPostType(), $spec);
//        $form->submit($content, true);

//        if (!$form->isValid()) {
//            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
//        }

//        $this->createAutoSpecItem($spec, $em);
//        $spec->setShop($shop);
//        $spec->setAuto(true);

//        $em->persist($spec);
//    }

//    /**
//     * @param $spec
//     * @param $em
//     */
//    private function createAutoSpecItem(
//        $spec,
//        $em
//    ) {
//        $specItem = new ShopSpecItem();

//        $form = $this->createForm(new ShopSpecItemPostType(), $specItem);
//        $form->submit($spec->getItems(), true);

//        if (!$form->isValid()) {
//            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
//        }

//        $specItem->setSpec($spec);

//        $em->persist($specItem);
//    }

    /**
     * @param $shop
     *
     * @return Response
     */
    private function handleShopPut(
        $shop,
        $shopName
    ) {
        // check for shop with same name
        if ($shopName != $shop->getName()) {
            $this->findConflictShop($shop);
        }

        $em = $this->getDoctrine()->getManager();

        $shopAttachments = $shop->getAttachments();
        $startString = $shop->getStart();
        $endString = $shop->getEnd();

        // set startHour and endHour
        $this->setHours(
            $shop,
            $startString,
            $endString
        );

        // delete shop attachments
        $this->deleteShopAttachments(
            $shop,
            $em
        );

        // add shop attachments
        $this->addShopAttachments(
            $shop,
            $shopAttachments,
            $em
        );

        $shop->setModificationDate(new \DateTime());

        $em->flush();

        return new View();
    }

    private function deleteShopAttachments(
        $shop,
        $em
    ) {
        $shopAttachments = $this->getRepo('Shop\ShopAttachment')->findByShop($shop);

        if (is_null($shopAttachments) || empty($shopAttachments)) {
            return;
        }

        foreach ($shopAttachments as $shopAttachment) {
            $em->remove($shopAttachment);
        }
    }

    /**
     * @param $shop
     * @param $form
     */
    private function setHours(
        $shop,
        $startString,
        $endString
    ) {
        if (
            is_null($startString) ||
            empty($startString) ||
            is_null($endString) ||
            empty($endString)
        ) {
            return;
        }

        $start = \DateTime::createFromFormat(
            'H:i:s',
            $startString
        );

        $end = \DateTime::createFromFormat(
            'H:i:s',
            $endString
        );

        $shop->setStartHour($start);
        $shop->setEndHour($end);
    }

    /**
     * @param $shop
     * @param $shopAttachments
     * @param $em
     */
    private function addShopAttachments(
        $shop,
        $shopAttachments,
        $em
    ) {
        if (is_null($shopAttachments) || empty($shopAttachments)) {
            return;
        }

        foreach ($shopAttachments as $attachment) {
            $shopAttachment = new ShopAttachment();

            $form = $this->createForm(new ShopAttachmentPostType(), $shopAttachment);
            $form->submit($attachment, true);

            $shopAttachment->setShop($shop);

            $em->persist($shopAttachment);
        }
    }

    /**
     * @param Shop   $shop
     * @param string $shopJson
     * @param string $type
     *
     * @throws Patch\FailedTestException
     */
    private function patchShop(
        $shop,
        $shopJson,
        $type
    ) {
        $form = $this->createForm($type, $shop);
        $form->submit(json_decode($shopJson, true));

        if (!$shop->isOnline()) {
            $shop->setClose(true);

            // set shop products offline
            $this->getRepo('Shop\ShopProduct')->setShopProductsOfflineByShopId(
                $shop->getId()
            );
        } else {
            // set shop products online
            $this->getRepo('Shop\ShopProduct')->setShopProductsOnlineByShopId(
                $shop->getId()
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @param $shop
     */
    private function findConflictShop(
        $shop
    ) {
        $sameShop = $this->getRepo('Shop\Shop')->findOneBy(
            [
                'buildingId' => $shop->getBuildingId(),
                'name' => $shop->getName(),
            ]
        );

        if (!is_null($sameShop)) {
            throw new ConflictHttpException(Shop::SHOP_CONFLICT_MESSAGE);
        }
    }
}
