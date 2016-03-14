<?php

namespace Sandbox\AdminShopApiBundle\Controller;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermissionMap;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminType;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use Sandbox\ApiBundle\Entity\Shop\ShopOrderProduct;
use Sandbox\ApiBundle\Entity\Shop\ShopOrderProductSpec;
use Sandbox\ApiBundle\Entity\Shop\ShopOrderProductSpecItem;
use Sandbox\ApiBundle\Entity\Shop\ShopProductSpecItem;
use Sandbox\ApiBundle\Form\Shop\ShopOrderProductSpecItemType;
use Sandbox\ApiBundle\Form\Shop\ShopOrderProductSpecType;
use Sandbox\ApiBundle\Form\Shop\ShopOrderProductType;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Sandbox\ApiBundle\Entity\Shop\ShopAdmin;

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
     *
     * @return int|void
     */
    protected function handleShopOrderProductPost(
        $em,
        $order,
        $shop
    ) {
        $productData = $order->getProducts();

        if (is_null($productData)) {
            return;
        }

        $calculatedPrice = 0;
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

            $attachmentArray = [];
            $attachments = $shopProduct->getProductAttachments();
            foreach ($attachments as $attachment) {
                array_push($attachmentArray, $attachment->jsonSerialize());
            }

            $productInfo = $shopProduct->jsonSerialize();
            $productInfo['attachments'] = $attachmentArray;

            $info = json_encode($productInfo);

            $product->setOrder($order);
            $product->setProduct($shopProduct);
            $product->setShopProductInfo($info);

            $em->persist($product);

            $specPrice = $this->handleShopOrderProductSpecPost(
                $em,
                $product
            );

            $calculatedPrice = $calculatedPrice + $specPrice;
        }

        return $calculatedPrice;
    }

    /**
     * @param ShopOrderProduct $product
     * @param EntityManager    $em
     *
     * @return int|void
     */
    private function handleShopOrderProductSpecPost(
        $em,
        $product
    ) {
        $specData = $product->getSpecs();

        if (is_null($specData)) {
            return;
        }

        $this->compareSpecs($product, $specData);

        $calculatedPrice = 0;
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

            $specItemPrice = $this->handleShopOrderProductSpecItemPost(
                $em,
                $spec
            );

            $calculatedPrice = $calculatedPrice + $specItemPrice;
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

            $itemPrice = $price * $item->getAmount();
            $calculatedPrice = $calculatedPrice + $itemPrice;
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

    //-------------------- check sales admin permission --------------------//

    /**
     * Check sales admin's permission, is allowed to operate.
     *
     * @param int          $adminId
     * @param string       $typeKey
     * @param string|array $permissionKeys
     * @param int          $opLevel
     * @param int          $shopId
     *
     * @throws AccessDeniedHttpException
     */
    protected function throwAccessDeniedIfShopAdminNotAllowed(
        $adminId,
        $typeKey,
        $permissionKeys = null,
        $opLevel = ShopAdminPermissionMap::OP_LEVEL_VIEW,
        $shopId = null
    ) {
        $myPermission = null;

        // get admin
        $admin = $this->getRepo('Shop\ShopAdmin')->find($adminId);
        $type = $admin->getType();

        // first check if user is super admin, no need to check others
        if (ShopAdminType::KEY_SUPER === $type->getKey()) {
            return;
        }

        // if admin type doesn't match, then throw exception
        if ($typeKey != $type->getKey()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // check permission key array
        if (is_null($permissionKeys) || empty($permissionKeys) || !is_array($permissionKeys)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        foreach ($permissionKeys as $permissionKey) {
            $permission = $this->getRepo('Shop\ShopAdminPermission')->findOneByKey($permissionKey);
            if (is_null($permission)) {
                continue;
            }

            // judge by global permission and building permission
            $filters = array(
                'adminId' => $adminId,
                'permissionId' => $permission->getId(),
            );
            if (!is_null($shopId)) {
                $filters['shopId'] = $shopId;
            }

            // check user's permission
            $myPermission = $this->getRepo('Shop\ShopAdminPermissionMap')
                ->findOneBy($filters);
            if (!is_null($myPermission) && $myPermission->getOpLevel() >= $opLevel) {
                return;
            }
        }

        throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
    }

    /**
     * @return ShopAdmin
     *
     * @throws UnauthorizedHttpException
     */
    protected function checkShopAdminLoginSecurity()
    {
        $auth = $this->getSandboxAuthorization();

        $admin = $this->getRepo('Shop\ShopAdmin')->findOneBy(array(
            'username' => $auth->getUsername(),
            'password' => $auth->getPassword(),
        ));

        if (is_null($admin)) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        return $admin;
    }
}
