<?php

namespace Sandbox\AdminShopApiBundle\Controller;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use Sandbox\ApiBundle\Entity\Shop\ShopOrderProduct;
use Sandbox\ApiBundle\Entity\Shop\ShopOrderProductSpec;
use Sandbox\ApiBundle\Entity\Shop\ShopOrderProductSpecItem;
use Sandbox\ApiBundle\Entity\Shop\ShopProductSpecItem;
use Sandbox\ApiBundle\Form\Shop\ShopOrderProductSpecItemType;
use Sandbox\ApiBundle\Form\Shop\ShopOrderProductSpecType;
use Sandbox\ApiBundle\Form\Shop\ShopOrderProductType;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ShopRestController extends PaymentController
{
    //-------------------- Repo --------------------//

    /**
     * @param $id
     * @param $path
     *
     * @return object $entity
     */
    public function findEntityById(
        $id,
        $path
    ) {
        $entity = $this->getRepo($path)->find($id);
        $this->throwNotFoundIfNull($entity, self::NOT_FOUND_MESSAGE);

        return $entity;
    }

    /**
     * @param ShopOrder     $order
     * @param Shop          $shop
     * @param EntityManager $em
     * @param float         $calculatedPrice
     *
     * @return int|void
     */
    protected function handleShopOrderProductPost(
        $em,
        $order,
        $shop,
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
                $em,
                $product,
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
        $em,
        $product,
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
                $em,
                $spec
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
        $em,
        $spec
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
            $inventory = $shopProductSpecItem->getInventory();

            if (!is_null($inventory)) {
                $amount = $item->getAmount();
                if ($amount > $inventory) {
                    // TODO: throw custom exception
                    throw new ConflictHttpException(ShopProductSpecItem::INSUFFICIENT_INVENTORY);
                }

                $shopProductSpecItem->setInventory($inventory - $amount);
            }

            $info = json_encode($shopProductSpecItem->jsonSerialize());

            $item->setSpec($spec);
            $item->setItem($shopProductSpecItem);
            $item->setShopProductSpecItemInfo($info);

            $em->persist($item);

            $price = $shopProductSpecItem->getPrice();

            if (is_null($price)) {
                continue;
            }

            $calculatedPrice += $price * $item->getAmount();
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
