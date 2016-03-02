<?php

namespace Sandbox\AdminShopApiBundle\Controller\Shop;

use Sandbox\ApiBundle\Entity\Shop\Shop;
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
        $this->findEntityById($shopId, 'Shop\Shop');
        $spec = $this->findEntityById($id, 'Shop\ShopSpec');

        $em = $this->getDoctrine()->getManager();
        $spec->setInvisible(true);
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
