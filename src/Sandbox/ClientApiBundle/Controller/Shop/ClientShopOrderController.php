<?php

namespace Sandbox\ClientApiBundle\Controller\Shop;

use Sandbox\AdminShopApiBundle\Controller\ShopRestController;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Entity\Shop\ShopOrderProduct;
use Sandbox\ApiBundle\Entity\Shop\ShopOrderProductSpec;
use Sandbox\ApiBundle\Entity\Shop\ShopOrderProductSpecItem;
use Sandbox\ApiBundle\Form\Shop\ShopOrderProductSpecItemType;
use Sandbox\ApiBundle\Form\Shop\ShopOrderProductSpecType;
use Sandbox\ApiBundle\Form\Shop\ShopOrderProductType;
use Sandbox\ApiBundle\Form\Shop\ShopOrderType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Client ShopOrder Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xue <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientShopOrderController extends ShopRestController
{
    /**
     * @param Request $request
     * @param int     $shopId
     * @param int     $id
     *
     * @Method({"GET"})
     * @Route("/shops/{shopId}/orders/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getShopOrderByIdAction(
        Request $request,
        $shopId,
        $id
    ) {
        $userId = $this->getUserId();
        $order = $this->getRepo('Shop\ShopOrder')->findOneBy(
            [
                'userId' => $userId,
                'id' => $id,
                'shopId' => $shopId,
            ]
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_order']));
        $view->setData($order);

        return $view;
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Method({"POST"})
     * @Route("/shops/{id}/orders")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postShopOrderAction(
        Request $request,
        $id
    ) {
        $shop = $this->findEntityById($id, 'Shop\Shop');

        $order = new ShopOrder();
        $form = $this->createForm(new ShopOrderType(), $order);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $orderNumber = $this->getOrderNumber(ShopOrder::SHOP_ORDER_LETTER_HEAD);
        $userId = $this->getUserId();
        $order->setUserId($userId);
        $order->setShop($shop);
        $order->setOrderNumber($orderNumber);
        $em->persist($order);

        $calculatedPrice = 0;
        $calculatedPrice = $this->handleShopOrderProductPost(
            $order,
            $shop,
            $em,
            $calculatedPrice
        );

        if ($order->getPrice() != $calculatedPrice) {
            return $this->customErrorView(
                400,
                self::DISCOUNT_PRICE_MISMATCH_CODE,
                self::DISCOUNT_PRICE_MISMATCH_MESSAGE
            );
        }

        $em->flush();

        return new View(['id' => $order->getId()]);
    }

    /**
     * @param ShopOrder     $order
     * @param Shop          $shop
     * @param EntityManager $em
     * @param float         $calculatedPrice
     *
     * @return int|void
     */
    private function handleShopOrderProductPost(
        $order,
        $shop,
        $em,
        $calculatedPrice
    ) {
        $productData = $order->getProducts();

        if (is_null($productData)) {
            return;
        }

        foreach ($productData as $data) {
            $product = new ShopOrderProduct();
            $form = $this->createForm(new ShopOrderProductType(), $product);
            $form->submit($data, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $shopProduct = $this->getRepo('Shop\ShopProduct')->getShopProductByShopId(
                $shop->getId(),
                $product->getProductId(),
                true
            );
            $this->throwNotFoundIfNull($shopProduct, self::NOT_FOUND_MESSAGE);

            $info = json_encode($shopProduct->jsonSerialize());

            $product->setOrder($order);
            $product->setProduct($shopProduct);
            $product->setShopProductInfo($info);
            $em->persist($product);

            $calculatedPrice += $this->handleShopOrderProductSpecPost(
                $product,
                $em,
                $calculatedPrice
            );
        }

        return $calculatedPrice;
    }

    /**
     * @param ShopOrderProduct $product
     * @param EntityManager    $em
     * @param float            $calculatedPrice
     *
     * @return int|void
     */
    private function handleShopOrderProductSpecPost(
        $product,
        $em,
        $calculatedPrice
    ) {
        $specData = $product->getSpecs();

        if (is_null($specData)) {
            return;
        }

        $this->compareSpecs($product, $specData);

        foreach ($specData as $data) {
            $spec = new ShopOrderProductSpec();
            $form = $this->createForm(new ShopOrderProductSpecType(), $spec);
            $form->submit($data, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $shopProductSpec = $this->findEntityById($spec->getSpecId(), 'Shop\ShopProductSpec');

            $multiple = $shopProductSpec->getShopSpec()->getMultiple();
            if (!$multiple) {
                $this->checkItemCount($spec->getItems());
            }

            $info = json_encode($shopProductSpec->jsonSerialize());

            $spec->setProduct($product);
            $spec->setSpec($shopProductSpec);
            $spec->setShopProductSpecInfo($info);
            $em->persist($spec);

            $calculatedPrice = $this->handleShopOrderProductSpecItemPost(
                $spec,
                $em
            );
        }

        return $calculatedPrice;
    }

    /**
     * @param ShopOrderProduct $product
     * @param array            $specData
     */
    private function compareSpecs(
        $product,
        $specData
    ) {
        // find required specs
        $requiredSpecs = $this->getRepo('Shop\ShopProductSpec')->findRequiredSpecsByProduct($product->getProductId());
        $requiredArray = [];
        foreach ($requiredSpecs as $requiredSpec) {
            array_push($requiredArray, $requiredSpec->getId());
        }

        // find given specs
        $givenArray = [];
        foreach ($specData as $data) {
            $spec = new ShopOrderProductSpec();
            $form = $this->createForm(new ShopOrderProductSpecType(), $spec);
            $form->submit($data, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            array_push($givenArray, $spec->getSpecId());
        }

        // compare required and given specs
        $comparison = array_diff($requiredArray, $givenArray);
        if (!empty($comparison)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
    }

    /**
     * @param ShopOrderProductSpec $spec
     * @param $em
     */
    private function handleShopOrderProductSpecItemPost(
        $spec,
        $em
    ) {
        $itemData = $spec->getItems();

        if (is_null($itemData)) {
            return;
        }

        $calculatedPrice = 0;
        foreach ($itemData as $data) {
            $item = new ShopOrderProductSpecItem();
            $form = $this->createForm(new ShopOrderProductSpecItemType(), $item);
            $form->submit($data, true);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $shopProductSpecItem = $this->findEntityById($item->getItemId(), 'Shop\ShopProductSpecItem');

            // check inventory
            if ($item->getAmount() > $shopProductSpecItem->getInventory()) {
                // TODO: throw custom exception
                throw new ConflictHttpException();
            }

            $info = json_encode($shopProductSpecItem->jsonSerialize());

            $item->setSpec($spec);
            $item->setItem($shopProductSpecItem);
            $item->setShopProductSpecItemInfo($info);
            $em->persist($item);

            $calculatedPrice += $shopProductSpecItem->getPrice() * $item->getAmount();
        }

        return $calculatedPrice;
    }

    /**
     * @param $itemData
     */
    private function checkItemCount(
        $itemData
    ) {
        // count items
        if (count($itemData) > 1) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
    }
}
