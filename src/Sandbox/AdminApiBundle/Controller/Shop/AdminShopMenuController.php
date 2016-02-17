<?php

namespace Sandbox\AdminApiBundle\Controller\Shop;

use Sandbox\AdminApiBundle\Data\Shop\ShopMenuPosition;
use Sandbox\ApiBundle\Entity\Shop\ShopMenu;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Shop\ShopMenuController;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use Sandbox\ApiBundle\Form\Shop\ShopMenuPostType;
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

        $content = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();

        // add shop menu
        $this->addShopMenu(
            $content,
            $shop,
            $em
        );

        // modify shop menu
        $this->modifyShopMenu(
            $content,
            $id
        );

        // remove shop menu
        $this->removeShopMenu(
            $content,
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
     * @Route("/shops/menus/{id}")
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
        $content,
        $shop,
        $em
    ) {
        if (!isset($content['add']) || empty($content['add'])) {
            return;
        }

        foreach ($content['add'] as $item) {
            $menu = new ShopMenu();
            $form = $this->createForm(new ShopMenuPostType(), $menu);
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
        $content,
        $shopId
    ) {
        if (!isset($content['modify']) || empty($content['modify'])) {
            return;
        }

        foreach ($content['modify'] as $item) {
            // check if menu exists
            $menu = $this->getRepo('Shop\ShopMenu')->find($item['id']);
            $this->throwNotFoundIfNull($menu, self::NOT_FOUND_MESSAGE);

            // check menu belongs to current shop
            if ($shopId != $menu->getShopId()) {
                continue;
            }

            $menu->setName($item['name']);
        }
    }

    /**
     * @param $content
     * @param $shopId
     * @param $em
     */
    private function removeShopMenu(
        $content,
        $shopId,
        $em
    ) {
        if (!isset($content['remove']) || empty($content['remove'])) {
            return;
        }

        foreach ($content['remove'] as $item) {
            // check if menu exists
            $menu = $this->getRepo('Shop\ShopMenu')->find($item['id']);
            $this->throwNotFoundIfNull($menu, self::NOT_FOUND_MESSAGE);

            // check menu belongs to current shop
            if ($shopId != $menu->getShopId()) {
                continue;
            }

            $em->remove($menu);
        }
    }
}
