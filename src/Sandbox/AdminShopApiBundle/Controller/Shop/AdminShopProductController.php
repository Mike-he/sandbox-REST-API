<?php

namespace Sandbox\AdminShopApiBundle\Controller\Shop;

use Rs\Json\Patch;
use Sandbox\AdminShopApiBundle\Data\Shop\ShopMenuPosition;
use Sandbox\ApiBundle\Controller\Shop\ShopProductController;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermission;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermissionMap;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminType;
use Sandbox\ApiBundle\Entity\Shop\ShopProduct;
use Sandbox\ApiBundle\Entity\Shop\ShopProductAttachment;
use Sandbox\ApiBundle\Entity\Shop\ShopProductSpec;
use Sandbox\ApiBundle\Entity\Shop\ShopProductSpecItem;
use Sandbox\ApiBundle\Form\Shop\ShopMenuPositionType;
use Sandbox\ApiBundle\Form\Shop\ShopProductAttachmentPostType;
use Sandbox\ApiBundle\Form\Shop\ShopProductPatchOnlineType;
use Sandbox\ApiBundle\Form\Shop\ShopProductPostType;
use Sandbox\ApiBundle\Form\Shop\ShopProductSpecInventoryPutType;
use Sandbox\ApiBundle\Form\Shop\ShopProductSpecItemInventoryPutType;
use Sandbox\ApiBundle\Form\Shop\ShopProductSpecItemPostType;
use Sandbox\ApiBundle\Form\Shop\ShopProductSpecPostType;
use Sandbox\AdminShopApiBundle\Data\Shop\ShopProductSpecData;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\View\View;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Admin ShopProduct Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xue <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminShopProductController extends ShopProductController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Method({"GET"})
     * @Route("/shops/{id}/products")
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
     * @Annotations\QueryParam(
     *    name="search",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="search product by name or Id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="menu",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="menu Id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="online",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    requirements="(0|1)",
     *    description="product online status true/false"
     * )
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getShopProductsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkAdminProductPermission(
            ShopAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                ShopAdminPermission::KEY_SHOP_PRODUCT,
                ShopAdminPermission::KEY_SHOP_KITCHEN,
            ),
            $id
        );

        $this->findEntityById($id, 'Shop\Shop');
        $search = $paramFetcher->get('search');
        $menuId = $paramFetcher->get('menu');
        $online = $paramFetcher->get('online');

        $products = $this->getRepo('Shop\ShopProduct')->getShopProductsByShopId(
            $id,
            $menuId,
            $online,
            $search
         );

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $products = $this->get('serializer')->serialize(
            $products,
            'json',
            SerializationContext::create()->setGroups(['product_view'])
        );
        $products = json_decode($products, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $products,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"DELETE"})
     * @Route("/shops/{shopId}/products/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteShopProductAction(
        $shopId,
        $id
    ) {
        // check user permission
        $this->checkAdminProductPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_SHOP_PRODUCT,
            ),
            $shopId
        );

        $this->findEntityById($shopId, 'Shop\Shop');

        $product = $this->getRepo('Shop\ShopProduct')->getShopProductByShopId($shopId, $id);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();

        $product->setInvisible(true);
        $product->setOnline(false);
        $product->setModificationDate(new \DateTime());

        $em->flush();

        return new View();
    }

    /**
     * patch shop product online status.
     *
     * @param Request $request
     * @param $shopId
     * @param $id
     *
     * @Method({"PATCH"})
     * @Route("/shops/{shopId}/products/{id}")
     *
     * @return View
     */
    public function patchShopProductOnlineAction(
        Request $request,
        $shopId,
        $id
    ) {
        // check user permission
        $this->checkAdminProductPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_SHOP_PRODUCT,
                ShopAdminPermission::KEY_SHOP_KITCHEN,
            ),
            $shopId
        );

        $shop = $this->findEntityById($shopId, 'Shop\Shop');

        $product = $this->getRepo('Shop\ShopProduct')->getShopProductByShopId($shopId, $id);

        $productJson = $this->get('serializer')->serialize($product, 'json');
        $patch = new Patch($productJson, $request->getContent());
        $productJson = $patch->apply();

        $form = $this->createForm(new ShopProductPatchOnlineType(), $product);
        $form->submit(json_decode($productJson, true));

        $product->setModificationDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"GET"})
     * @Route("/shops/{shopId}/products/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getShopProductByIdAction(
        $shopId,
        $id
    ) {
        // check user permission
        $this->checkAdminProductPermission(
            ShopAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                ShopAdminPermission::KEY_SHOP_PRODUCT,
                ShopAdminPermission::KEY_SHOP_KITCHEN,
            ),
            $shopId
        );

        $this->findEntityById($shopId, 'Shop\Shop');

        $product = $this->getRepo('Shop\ShopProduct')->getShopProductByShopId($shopId, $id);

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['product_view'])
        );
        $view->setData($product);

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"POST"})
     * @Route("/shops/{id}/products")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postShopProductAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminProductPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_SHOP_PRODUCT,
            ),
            $id
        );

        $this->findEntityById($id, 'Shop\Shop');

        $product = new ShopProduct();

        $form = $this->createForm(new ShopProductPostType(), $product);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleShopProductPost(
            $product
        );
    }

    /**
     * @param Request $request
     * @param $shopId
     * @param $id
     *
     * @Method({"PUT"})
     * @Route("/shops/{shopId}/products/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putShopProductAction(
        Request $request,
        $shopId,
        $id
    ) {
        // check user permission
        $this->checkAdminProductPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_SHOP_PRODUCT,
            ),
            $shopId
        );

        $this->findEntityById($shopId, 'Shop\Shop');

        $product = $this->getRepo('Shop\ShopProduct')->getShopProductByShopId($shopId, $id);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        $oldMenuId = $product->getMenuId();
        $oldName = $product->getName();

        $form = $this->createForm(
            new ShopProductPostType(),
            $product,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleShopProductPut(
            $product,
            $shopId,
            $oldMenuId,
            $oldName
        );
    }

    /**
     * @param Request $request
     * @param $shopId
     * @param $id
     *
     * @Method({"PUT"})
     * @Route("/shops/{shopId}/products/{id}/specItems")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putShopProductSpecItemsAction(
        Request $request,
        $shopId,
        $id
    ) {
        // check user permission
        $this->checkAdminProductPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_SHOP_PRODUCT,
                ShopAdminPermission::KEY_SHOP_KITCHEN,
            ),
            $shopId
        );

        $this->findEntityById($shopId, 'Shop\Shop');

        $product = $this->getRepo('Shop\ShopProduct')->getShopProductByShopId($shopId, $id);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        $data = new ShopProductSpecData();

        $form = $this->createForm(
            new ShopProductSpecInventoryPutType(),
            $data,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleShopProductSpecInventoryPut(
            $data->getItems(),
            $id
        );
    }

    /**
     * @param Request $request
     * @param $shopId
     * @param $id
     *
     * @Method({"POST"})
     * @Route("/shops/{shopId}/menus/{menuId}/products/{id}/position")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function changeProductPositionAction(
        Request $request,
        $shopId,
        $menuId,
        $id
    ) {
        // check user permission
        $this->checkAdminProductPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_SHOP_PRODUCT,
            ),
            $shopId
        );

        $this->findEntityById($shopId, 'Shop\Shop');

        $product = $this->getRepo('Shop\ShopProduct')->getShopProductByShopId($shopId, $id);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

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

        $this->setProductPosition(
            $product,
            $action
        );

        return new View();
    }

    /**
     * @param $product
     * @param $action
     */
    private function setProductPosition(
        $product,
        $action
    ) {
        if ($action == ShopMenuPosition::ACTION_TOP) {
            $product->setSortTime(round(microtime(true) * 1000));
        } elseif ($action == ShopMenuPosition::ACTION_UP || $action == ShopMenuPosition::ACTION_DOWN) {
            $swapProduct = $this->getRepo('Shop\ShopProduct')->findSwapShopProduct(
                $product,
                $action
            );

            if (is_null($swapProduct)) {
                return;
            }

            $productSortTime = $product->getSortTime();
            $product->setSortTime($swapProduct->getSortTime());
            $swapProduct->setSortTime($productSortTime);
        }

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @param $items
     *
     * @return View|void
     */
    private function handleShopProductSpecInventoryPut(
        $items,
        $productId
    ) {
        if (is_null($items)) {
            return;
        }

        foreach ($items as $item) {
            $existItem = $this->getRepo('Shop\ShopProductSpecItem')->getItemsByProduct(
                $productId,
                $item['id']
            );

            if (is_null($existItem)) {
                continue;
            }

            $form = $this->createForm(new ShopProductSpecItemInventoryPutType(), $existItem);
            $form->submit($item, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param $product
     *
     * @return View
     */
    private function handleShopProductPut(
        $product,
        $shopId,
        $oldMenuId,
        $oldName
    ) {
        // check if menu is within shop
        $menu = $this->getRepo('Shop\ShopMenu')->findOneBy(
            [
                'id' => $product->getMenuId(),
                'shopId' => $shopId,
            ]
        );
        $this->throwNotFoundIfNull($menu, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();
        $product->setMenu($menu);

        // check conflict shop product
        if ($oldMenuId != $menu->getId() || $oldName != $product->getName()) {
            $this->findConflictShopProduct($product);
        }

        // remove attachments
        $this->removeShopProductAttachments($product, $em);

        // add attachments
        $attachments = $product->getAttachments();
        $this->addShopProductAttachments(
            $product,
            $attachments,
            $em
        );

        // remove specs
        $this->removeShopProductSpecs($product, $em);

        // add specs
        $this->addShopProductSpecs(
            $product,
            $product->getSpecs(),
            $em
        );

        $em->flush();

        return new View();
    }

    /**
     * @param $specs
     * @param $em
     */
    private function removeShopProductSpecs(
        $product,
        $em
    ) {
        $specs = $this->getRepo('Shop\ShopProductSpec')->findByProduct($product);

        foreach ($specs as $spec) {
            $em->remove($spec);
        }

        $em->flush();
    }

    /**
     * @param $product
     *
     * @return View
     */
    private function handleShopProductPost(
        $product
    ) {
        $menu = $this->findEntityById($product->getMenuId(), 'Shop\ShopMenu');
        $attachments = $product->getAttachments();
        $specs = $product->getSpecs();

        // add attachments
        $em = $this->getDoctrine()->getManager();
        $product->setMenu($menu);
        $em->persist($product);

        // check conflict shop product
        $this->findConflictShopProduct($product);

        $this->addShopProductAttachments(
            $product,
            $attachments,
            $em
        );

        // add specs
        $this->addShopProductSpecs(
            $product,
            $specs,
            $em
        );

        $em->flush();

        $view = new View();
        $view->setData(['id' => $product->getId()]);

        return $view;
    }

    /**
     * @param $product
     * @param $specs
     * @param $em
     */
    private function addShopProductSpecs(
        $product,
        $specs,
        $em
    ) {
        if (is_null($specs)) {
            return;
        }

        $duplicateSpecArray = [];
        foreach ($specs as $spec) {
            $productSpec = new ShopProductSpec();

            $form = $this->createForm(new ShopProductSpecPostType(), $productSpec);
            $form->submit($spec, true);

            $shopSpec = $this->findEntityById($productSpec->getShopSpecId(), 'Shop\ShopSpec');

            $items = $productSpec->getItems();
            $this->addShopProductSpecItems(
                $productSpec,
                $items,
                $em
            );

            $productSpec->setProduct($product);
            $productSpec->setShopSpec($shopSpec);

            $em->persist($productSpec);

            array_push($duplicateSpecArray, $productSpec->getShopSpecId());
        }

        $this->checkDuplicateInArray($duplicateSpecArray);
    }

    /**
     * @param $duplicateSpecArray
     */
    private function checkDuplicateInArray(
        $duplicateSpecArray
    ) {
        if (array_unique($duplicateSpecArray) != $duplicateSpecArray) {
            throw new ConflictHttpException(ShopProductSpec::SHOP_PRODUCT_SPEC_CONFLICT_MESSAGE);
        }
    }

    /**
     * @param $productSpec
     * @param $items
     * @param $em
     */
    private function addShopProductSpecItems(
        $productSpec,
        $items,
        $em
    ) {
        if (is_null($items)) {
            return;
        }

        foreach ($items as $item) {
            $productSpecItem = new ShopProductSpecItem();

            $form = $this->createForm(new ShopProductSpecItemPostType(), $productSpecItem);
            $form->submit($item, true);

            $shopSpecItem = $this->findEntityById($productSpecItem->getShopSpecItemId(), 'Shop\ShopSpecItem');

            $productSpecItem->setProductSpec($productSpec);
            $productSpecItem->setShopSpecItem($shopSpecItem);

            $em->persist($productSpecItem);
        }
    }

    /**
     * @param $product
     * @param $attachments
     * @param $em
     */
    private function addShopProductAttachments(
        $product,
        $attachments,
        $em
    ) {
        foreach ($attachments as $attachment) {
            $productAttachment = new ShopProductAttachment();

            $form = $this->createForm(new ShopProductAttachmentPostType(), $productAttachment);
            $form->submit($attachment, true);

            $productAttachment->setProduct($product);

            $em->persist($productAttachment);
        }
    }

    /**
     * @param $product
     * @param $em
     */
    private function removeShopProductAttachments(
        $product,
        $em
    ) {
        $attachments = $this->getRepo('Shop\ShopProductAttachment')->findByProduct($product);

        foreach ($attachments as $attachment) {
            $em->remove($attachment);
        }
    }

    /**
     * @param $product
     */
    private function findConflictShopProduct(
        $product
    ) {
        $sameProduct = $this->getRepo('Shop\ShopProduct')->findOneBy(
            [
                'menu' => $product->getMenu(),
                'name' => $product->getName(),
            ]
        );

        if (!is_null($sameProduct)) {
            throw new ConflictHttpException(ShopProduct::SHOP_PRODUCT_CONFLICT_MESSAGE);
        }
    }

    /**
     * @param $opLevel
     * @param $permissions
     * @param $shopId
     */
    private function checkAdminProductPermission(
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
