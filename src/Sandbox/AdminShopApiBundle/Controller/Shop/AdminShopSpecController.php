<?php

namespace Sandbox\AdminShopApiBundle\Controller\Shop;

use Sandbox\ApiBundle\Entity\Shop\Shop;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermission;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermissionMap;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminType;
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
use Sandbox\AdminShopApiBundle\Data\Shop\ShopMenuData;
use Sandbox\AdminShopApiBundle\Data\Shop\ShopSpecItemData;
use Sandbox\ApiBundle\Form\Shop\ShopSpecItemPostType;
use Sandbox\ApiBundle\Form\Shop\ShopMenuType;
use Sandbox\ApiBundle\Form\Shop\ShopSpecItemModifyType;
use Symfony\Component\HttpFoundation\Response;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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
class AdminShopSpecController extends SpecController
{
    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"GET"})
     * @Route("/shops/{id}/specs/dropdown")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getShopSpecDropDownAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminSpecPermission(
            ShopAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                ShopAdminPermission::KEY_SHOP_SPEC,
            ),
            $id
        );

        $shop = $this->findEntityById($id, 'Shop\Shop');
        $specs = $this->getRepo('Shop\ShopSpec')->findBy(
            [
                'shopId' => $shop->getId(),
                'invisible' => false,
            ],
            ['id' => 'ASC']
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($specs);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Method({"GET"})
     * @Route("/shops/{id}/specs")
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
     * @return View
     *
     * @throws \Exception
     */
    public function getShopSpecAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkAdminSpecPermission(
            ShopAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                ShopAdminPermission::KEY_SHOP_SPEC,
                ShopAdminPermission::KEY_SHOP_PRODUCT,
            ),
            $id
        );

        $this->findEntityById($id, 'Shop\Shop');
        $specs = $this->getRepo('Shop\ShopSpec')->getSpecsByShop($id);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $specs = $this->get('serializer')->serialize(
            $specs,
            'json',
            SerializationContext::create()->setGroups(['admin_shop'])
        );
        $specs = json_decode($specs, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $specs,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
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
        // check user permission
        $this->checkAdminSpecPermission(
            ShopAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                ShopAdminPermission::KEY_SHOP_SPEC,
            ),
            $shopId
        );

        $this->findEntityById($shopId, 'Shop\Shop');

        $spec = $this->getRepo('Shop\ShopSpec')->findOneBy(
            [
                'id' => $id,
                'invisible' => false,
                'auto' => false,
            ]
        );

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
        // check user permission
        $this->checkAdminSpecPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_SHOP_SPEC,
            ),
            $shopId
        );

        $this->findEntityById($shopId, 'Shop\Shop');

        $spec = $this->findEntityById($id, 'Shop\ShopSpec');

        $spec->setInvisible(true);

        $em = $this->getDoctrine()->getManager();
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
        // check user permission
        $this->checkAdminSpecPermission(
            ShopAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                ShopAdminPermission::KEY_SHOP_SPEC,
            ),
            $id
        );

        $shop = $this->findEntityById($id, 'Shop\Shop');

        if (!$shop->isActive()) {
            return $this->customErrorView(
                400,
                Shop::SHOP_INACTIVE_CODE,
                Shop::SHOP_INACTIVE_MESSAGE
            );
        }

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
        // check user permission
        $this->checkAdminSpecPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_SHOP_SPEC,
            ),
            $shopId
        );

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

        // check conflict shop spec
        $this->findConflictShopSpec($spec);

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

            // check spec item conflict
            $this->findConflictShopSpecItem($specItem);
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

            // check spec item conflict
            $this->findConflictShopSpecItem($specItem);
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

        $spec->setShop($shop);

        $em = $this->getDoctrine()->getManager();
        $em->persist($spec);

        // check conflict shop spec
        $this->findConflictShopSpec($spec);

        foreach ($items as $item) {
            $specItem = new ShopSpecItem();

            $form = $this->createForm(new ShopSpecItemPostType(), $specItem);
            $form->submit($item, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $specItem->setSpec($spec);

            $em->persist($specItem);

            // check spec item conflict
            $this->findConflictShopSpecItem($specItem);
        }

        $em->flush();

        return new View();
    }

    /**
     * @param $spec
     */
    private function findConflictShopSpec(
        $spec
    ) {
        $sameSpec = $this->getRepo('Shop\ShopSpec')->findOneBy(
            [
                'shop' => $spec->getShop(),
                'name' => $spec->getName(),
            ]
        );

        if (!is_null($sameSpec)) {
            throw new ConflictHttpException(ShopSpec::SHOP_SPEC_CONFLICT_MESSAGE);
        }
    }

    /**
     * @param $shop
     */
    private function findConflictShopSpecItem(
        $item
    ) {
        $sameItem = $this->getRepo('Shop\ShopSpecItem')->findOneBy(
            [
                'spec' => $item->getSpec(),
                'name' => $item->getName(),
            ]
        );

        if (!is_null($sameItem)) {
            throw new ConflictHttpException(ShopSpecItem::SHOP_SPEC_ITEM_CONFLICT_MESSAGE);
        }
    }

    /**
     * @param $opLevel
     * @param $permissions
     * @param $shopId
     */
    private function checkAdminSpecPermission(
        $opLevel,
        $permissions,
        $shopId = null
    ) {
        $this->throwAccessDeniedIfShopAdminNotAllowed(
            $this->getAdminId(),
            ShopAdminType::KEY_PLATFORM,
            $permissions,
            $opLevel,
            $shopId
        );
    }
}
