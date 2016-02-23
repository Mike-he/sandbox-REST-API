<?php

namespace Sandbox\ShopApiBundle\Controller\Shop;

use Sandbox\ApiBundle\Entity\Shop\ShopSpec;
use Sandbox\ApiBundle\Entity\Shop\ShopSpecItem;
use Sandbox\ApiBundle\Form\Shop\ShopSpecPostType;
use Sandbox\ApiBundle\Form\Shop\ShopSpecPutType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Shop\SpecController;
use Sandbox\ShopApiBundle\Data\Shop\ShopMenuData;
use Sandbox\ShopApiBundle\Data\Shop\ShopSpecItemData;
use Sandbox\ApiBundle\Form\Shop\ShopSpecItemPostType;
use Sandbox\ApiBundle\Form\Shop\ShopMenuType;
use Sandbox\ApiBundle\Form\Shop\ShopSpecItemModifyType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin Spec Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xue <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminSpecController extends SpecController
{
    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"GET"})
     * @Route("/shops/{id}/specs")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getSpecByShopAction(
        Request $request,
        $id
    ) {
        $shop = $this->findEntityById($id, 'Shop\Shop');
        $specs = $this->getRepo('Shop\ShopSpec')->findBy(
            ['shopId' => $shop->getId()],
            ['id' => 'ASC']
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($specs);

        return $view;
    }

    /**
     * @param Request $request
     * @param $shopId
     * @param $id
     *
     * @Method({"GET"})
     * @Route("/shops/{shopId}/specs/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getSpecByIdAction(
        Request $request,
        $shopId,
        $id
    ) {
        $this->findEntityById($shopId, 'Shop\Shop');
        $spec = $this->getRepo('Shop\ShopSpec')->find($id);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($spec);

        return $view;
    }

    /**
     * @param Request $request
     * @param $shopId
     * @param $id
     *
     * @Method({"DELETE"})
     * @Route("/shops/{shopId}/specs/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteSpecAction(
        Request $request,
        $shopId,
        $id
    ) {
        $this->findEntityById($shopId, 'Shop\Shop');
        $spec = $this->findEntityById($id, 'Shop\ShopSpec');

        $em = $this->getDoctrine()->getManager();
        $em->remove($spec);
        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"POST"})
     * @Route("/shops/{id}/specs")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postSpecAction(
        Request $request,
        $id
    ) {
        $shop = $this->getRepo('Shop\Shop')->findOneBy(
            [
                'id' => $id,
                'active' => true,
            ]
        );
        $this->throwNotFoundIfNull($shop, self::NOT_FOUND_MESSAGE);

        $spec = new ShopSpec();
        $form = $this->createForm(new ShopSpecPostType(), $spec);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleSpecPost(
            $shop,
            $spec
        );
    }

    /**
     * @param Request $request
     * @param $shopId
     * @param $id
     *
     * @Method({"PUT"})
     * @Route("/shops/{shopId}/specs/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putSpecAction(
        Request $request,
        $shopId,
        $id
    ) {
        $this->findEntityById($shopId, 'Shop\Shop');
        $spec = $this->findEntityById($id, 'Shop\ShopSpec');

        $form = $this->createForm(
            new ShopSpecPutType(),
            $spec,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $this->handleSpecItemPut(
            $spec,
            $id,
            $em
        );
        $em->flush();

        return new View();
    }

    /**
     * @param $spec
     * @param $id
     * @param $em
     */
    private function handleSpecItemPut(
        $spec,
        $id,
        $em
    ) {
        $items = $spec->getItems();
        if (is_null($items) || empty($items)) {
            return;
        }

        $menuData = new ShopMenuData();
        $form = $this->createForm(new ShopMenuType(), $menuData);
        $form->submit($items, true);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $addData = $menuData->getAdd();
        $modifyData = $menuData->getModify();
        $removeData = $menuData->getRemove();

        // add spec items
        $this->addSpecItem(
            $addData,
            $spec,
            $em
        );

        // modify spec items
        $this->modifySpecItem(
            $modifyData,
            $id
        );

        // remove spec items
        $this->removeSpecItem(
            $removeData,
            $id,
            $em
        );
    }

    /**
     * @param $addData
     * @param $spec
     * @param $em
     */
    private function addSpecItem(
        $addData,
        $spec,
        $em
    ) {
        if (is_null($addData)) {
            return;
        }

        foreach ($addData as $item) {
            $specItem = new ShopSpecItem();
            $form = $this->createForm(new ShopSpecItemPostType(), $specItem);
            $form->submit($item, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $specItem->setSpec($spec);
            $em->persist($specItem);
        }
    }

    /**
     * @param $modifyData
     * @param $specId
     */
    private function modifySpecItem(
        $modifyData,
        $specId
    ) {
        if (is_null($modifyData)) {
            return;
        }

        foreach ($modifyData as $item) {
            $specData = new ShopSpecItemData();
            $form = $this->createForm(new ShopSpecItemModifyType(), $specData);
            $form->submit($item, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            // check if item exists
            $specItem = $this->getRepo('Shop\ShopSpecItem')->findOneBy(
                [
                    'id' => $specData->getId(),
                    'specId' => $specId,
                ]
            );

            // check menu belongs to current spec
            if (is_null($specItem)) {
                continue;
            }

            $specItem->setName($specData->getName());
            $specItem->setInventory($specData->getInventory());
        }
    }

    /**
     * @param $removeData
     * @param $shopId
     * @param $em
     */
    private function removeSpecItem(
        $removeData,
        $specId,
        $em
    ) {
        if (is_null($removeData)) {
            return;
        }

        foreach ($removeData as $item) {
            $specData = new ShopSpecItemData();
            $form = $this->createForm(new ShopSpecItemModifyType(), $specData);
            $form->submit($item, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            // check if item exists
            $specItem = $this->getRepo('Shop\ShopSpecItem')->findOneBy(
                [
                    'id' => $specData->getId(),
                    'specId' => $specId,
                ]
            );

            // check menu belongs to current spec
            if (is_null($specItem)) {
                continue;
            }

            $em->remove($specItem);
        }
    }

    /**
     * @param $shop
     * @param $spec
     *
     * @return Response
     */
    private function handleSpecPost(
        $shop,
        $spec
    ) {
        $items = $spec->getItems();

        if (is_null($items)) {
            return;
        }

        $em = $this->getDoctrine()->getManager();
        foreach ($items as $item) {
            $specItem = new ShopSpecItem();
            $form = $this->createForm(new ShopSpecItemPostType(), $specItem);
            $form->submit($item, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $specItem->setSpec($spec);
            $em->persist($specItem);
        }

        $spec->setShop($shop);
        $em->persist($spec);
        $em->flush();

        return new View();
    }
}
