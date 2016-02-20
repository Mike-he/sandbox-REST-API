<?php

namespace Sandbox\ShopApiBundle\Controller\Shop;

use Sandbox\ShopApiBundle\Data\Shop\ShopMenuPosition;
use Sandbox\ApiBundle\Entity\Shop\ShopMenu;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Shop\ShopMenuController;
use Sandbox\ShopApiBundle\Data\Shop\ShopMenuData;
use Sandbox\ShopApiBundle\Data\Shop\ShopMenuItem;
use Sandbox\ApiBundle\Form\Shop\ShopMenuType;
use Sandbox\ApiBundle\Form\Shop\ShopMenuAddType;
use Sandbox\ApiBundle\Form\Shop\ShopMenuModifyType;
use Sandbox\ApiBundle\Form\Shop\ShopMenuRemoveType;
use Sandbox\ApiBundle\Form\Shop\ShopMenuPositionType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin Shop Menu Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xue <leox@gobeta.com.cn>
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
        $shop = $this->findShopById($id);
        $menu = $this->getRepo('Shop\ShopMenu')->findBy(
            ['shopId' => $shop->getId()],
            ['sortTime' => 'DESC']
        );

        //TODO: GET PRODUCT COUNT

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($menu);

        return $view;
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
            $id,
            $em
        );

        $em->flush();

        return new Response();
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"POST"})
     * @Route("/shops/menus/{id}/position")
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function changePositionAction(
        Request $request,
        $id
    ) {
        $menu = $this->findShopMenuById($id);

        $position = new ShopMenuPosition();
        $form = $this->createForm(new ShopMenuPositionType(), $position);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $action = $position->getAction();
        if (empty($action) || is_null($action)) {
            return new Response();
        }

        $this->setMenuPosition(
            $menu,
            $action
        );

        return new Response();
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
            $swapItemArray = $this->getRepo('Shop\ShopMenu')->findSwapShopMenu(
                $menu,
                $action
            );

            if (empty($swapItemArray)) {
                return;
            }

            // swap
            $swapItem = $swapItemArray[0];
            $itemSortTime = $menu->getSortTime();
            $menu->setSortTime($swapItem->getSortTime());
            $swapItem->setSortTime($itemSortTime);
        }

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @param $content
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
        }
    }

    /**
     * @param $content
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

            $menu->setName($menuItem->getName());
        }
    }

    /**
     * @param $content
     * @param $shopId
     * @param $em
     */
    private function removeShopMenu(
        $removeData,
        $shopId,
        $em
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

            $em->remove($menu);
        }
    }
}
