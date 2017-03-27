<?php

namespace Sandbox\AdminShopApiBundle\Controller\Shop;

use Sandbox\AdminShopApiBundle\Data\Shop\ShopMenuPosition;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Shop\ShopMenu;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Shop\ShopMenuController;
use Sandbox\AdminShopApiBundle\Data\Shop\ShopMenuData;
use Sandbox\AdminShopApiBundle\Data\Shop\ShopMenuItem;
use Sandbox\ApiBundle\Form\Shop\ShopMenuType;
use Sandbox\ApiBundle\Form\Shop\ShopMenuAddType;
use Sandbox\ApiBundle\Form\Shop\ShopMenuModifyType;
use Sandbox\ApiBundle\Form\Shop\ShopMenuRemoveType;
use Sandbox\ApiBundle\Form\Shop\ShopMenuPositionType;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Admin Shop Menu Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminShopMenuController extends ShopMenuController
{
    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"GET"})
     * @Route("/shops/{id}/menus")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getShopMenuByShopAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_PRODUCT,
                    'shop_id' => $id,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
                    'shop_id' => $id,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $this->findEntityById($id, 'Shop\Shop');

        $menus = $this->getRepo('Shop\ShopMenu')->getShopMenuByShop($id);
        $menus = $this->get('serializer')->serialize(
            $menus,
            'json',
            SerializationContext::create()->setGroups(['admin_shop'])
        );
        $menus = json_decode($menus, true);

        // get product count for each menu item
        $menuArray = [];
        foreach ($menus as $menu) {
            $count = $this->getRepo('Shop\ShopProduct')->countShopProductByMenu($menu['id']);
            $menu['count'] = $count;
            array_push($menuArray, $menu);
        }

        return new View($menuArray);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"POST"})
     * @Route("/shops/{id}/menus")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postShopMenuAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_PRODUCT,
                    'shop_id' => $id,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $shop = $this->findShopById($id);
        $this->throwNotFoundIfNull($shop, self::NOT_FOUND_MESSAGE);

        $menuData = new ShopMenuData();

        $form = $this->createForm(new ShopMenuType(), $menuData);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();

        $addData = $menuData->getAdd();
        $modifyData = $menuData->getModify();
        $removeData = $menuData->getRemove();

        // add shop menu
        $this->addShopMenu(
            $addData,
            $shop,
            $em
        );

        // modify shop menu
        $this->modifyShopMenu(
            $modifyData,
            $id
        );

        // remove shop menu
        $this->removeShopMenu(
            $removeData,
            $id
        );

        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param $shopId
     * @param $id
     *
     * @Method({"POST"})
     * @Route("/shops/{shopId}/menus/{id}/position")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function changePositionAction(
        Request $request,
        $shopId,
        $id
    ) {
        $menu = $this->getRepo('Shop\ShopMenu')->findOneBy(
            [
                'shopId' => $shopId,
                'id' => $id,
                'invisible' => false,
            ]
        );
        $this->throwNotFoundIfNull($menu, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_PRODUCT,
                    'shop_id' => $menu->getShopId(),
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        $position = new ShopMenuPosition();

        $form = $this->createForm(new ShopMenuPositionType(), $position);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $action = $position->getAction();

        if (empty($action) || is_null($action)) {
            return new View();
        }

        $this->setMenuPosition(
            $menu,
            $action
        );

        return new View();
    }

    /**
     * @param $menu
     * @param $position
     */
    private function setMenuPosition(
        $menu,
        $action
    ) {
        if ($action == ShopMenuPosition::ACTION_TOP) {
            $menu->setSortTime(round(microtime(true) * 1000));
        } elseif ($action == ShopMenuPosition::ACTION_UP || $action == ShopMenuPosition::ACTION_DOWN) {
            $swapItem = $this->getRepo('Shop\ShopMenu')->findSwapShopMenu(
                $menu,
                $action
            );

            if (empty($swapItem)) {
                return;
            }

            // swap
            $itemSortTime = $menu->getSortTime();
            $menu->setSortTime($swapItem->getSortTime());
            $swapItem->setSortTime($itemSortTime);
        }

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @param $addData
     * @param $shop
     * @param $em
     */
    private function addShopMenu(
        $addData,
        $shop,
        $em
    ) {
        if (is_null($addData) || empty($addData)) {
            return;
        }

        foreach ($addData as $item) {
            $menu = new ShopMenu();

            $form = $this->createForm(new ShopMenuAddType(), $menu);
            $form->submit($item, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $menu->setShop($shop);

            $em->persist($menu);

            // check menu conflict
            $this->findConflictShopMenu(
                $shop->getId(),
                $menu->getName()
            );
        }
    }

    /**
     * @param $modifyData
     * @param $shopId
     */
    private function modifyShopMenu(
        $modifyData,
        $shopId
    ) {
        if (is_null($modifyData) || empty($modifyData)) {
            return;
        }

        foreach ($modifyData as $item) {
            $menuItem = new ShopMenuItem();

            $form = $this->createForm(new ShopMenuModifyType(), $menuItem);
            $form->submit($item, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            // check if menu exists
            $menu = $this->getRepo('Shop\ShopMenu')->findOneBy(
                [
                    'id' => $menuItem->getId(),
                    'shopId' => $shopId,
                ]
            );

            // check menu belongs to current shop
            if (is_null($menu)) {
                continue;
            }

            // check menu conflict
            if ($menu->getName() != $menuItem->getName()) {
                $this->findConflictShopMenu(
                    $shopId,
                    $menuItem->getName()
                );
            }

            $menu->setName($menuItem->getName());
        }
    }

    /**
     * @param $removeData
     * @param $shopId
     * @param $em
     */
    private function removeShopMenu(
        $removeData,
        $shopId
    ) {
        if (is_null($removeData) || empty($removeData)) {
            return;
        }

        foreach ($removeData as $item) {
            $menuItem = new ShopMenuItem();

            $form = $this->createForm(new ShopMenuRemoveType(), $menuItem);
            $form->submit($item, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            // check if menu exists
            $menu = $this->getRepo('Shop\ShopMenu')->findOneBy(
                [
                    'id' => $menuItem->getId(),
                    'shopId' => $shopId,
                ]
            );

            // check menu belongs to current shop
            if (is_null($menu)) {
                continue;
            }

            $menu->setInvisible(true);

            $products = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Shop\ShopProduct')
                ->findByMenu($menu);

            foreach ($products as $product) {
                $product->setInvisible(true);
                $product->setOnline(false);
                $product->setModificationDate(new \DateTime());
            }
        }
    }

    /**
     * @param $menu
     *
     * @throws ConflictHttpException
     */
    private function findConflictShopMenu(
        $shopId,
        $menuName
    ) {
        $sameMenu = $this->getRepo('Shop\ShopMenu')->findOneBy(
            [
                'shopId' => $shopId,
                'name' => $menuName,
                'invisible' => false,
            ]
        );

        if (!is_null($sameMenu)) {
            throw new ConflictHttpException(ShopMenu::SHOP_MENU_CONFLICT_MESSAGE);
        }
    }
}
