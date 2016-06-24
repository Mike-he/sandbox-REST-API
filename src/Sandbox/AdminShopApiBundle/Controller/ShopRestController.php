<?php

namespace Sandbox\AdminShopApiBundle\Controller;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesUser;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermissionMap;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminType;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use Sandbox\ApiBundle\Entity\Shop\ShopOrderProduct;
use Sandbox\ApiBundle\Entity\Shop\ShopOrderProductSpec;
use Sandbox\ApiBundle\Entity\Shop\ShopOrderProductSpecItem;
use Sandbox\ApiBundle\Form\Shop\ShopOrderProductSpecItemType;
use Sandbox\ApiBundle\Form\Shop\ShopOrderProductSpecType;
use Sandbox\ApiBundle\Form\Shop\ShopOrderProductType;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Sandbox\ApiBundle\Entity\Shop\ShopAdmin;
use FOS\RestBundle\View\View;

class ShopRestController extends PaymentController
{
    const SHOP_PERMISSION_PREFIX = 'shop.shop';

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
     * @param $em
     * @param $userId
     * @param $shop
     */
    protected function setShopUser(
        $em,
        $userId,
        $shop
    ) {
        // check shop user record
        $companyId = $shop->getBuilding()->getCompanyId();

        $shopUser = $this->getRepo('SalesAdmin\SalesUser')->findOneBy(array(
            'userId' => $userId,
            'shopId' => $shop->getId(),
        ));

        if (is_null($shopUser)) {
            $shopUser = new SalesUser();

            $shopUser->setUserId($userId);
            $shopUser->setCompanyId($companyId);
            $shopUser->setBuildingId($shop->getBuildingId());
        }

        $shopUser->setIsShopOrdered(true);
        $shopUser->setShopId($shop->getId());
        $shopUser->setModificationDate(new \DateTime('now'));

        $em->persist($shopUser);
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
        $shop,
        $priceData
    ) {
        $productData = $order->getProducts();

        if (is_null($productData)) {
            return;
        }

        $calculatedPrice = 0;
        $inventoryError = [];

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

            $inventoryError = $this->handleShopOrderProductSpecPost(
                $em,
                $product,
                $priceData,
                $shopProduct,
                $inventoryError
            );

            $calculatedPrice = $calculatedPrice + $priceData->getSpecPrice();
        }

        $priceData->setProductPrice($calculatedPrice);

        return $inventoryError;
    }

    /**
     * @param ShopOrderProduct $product
     * @param EntityManager    $em
     *
     * @return int|void
     */
    private function handleShopOrderProductSpecPost(
        $em,
        $product,
        $priceData,
        $shopProduct,
        $inventoryError
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

            $inventoryError = $this->handleShopOrderProductSpecItemPost(
                $em,
                $spec,
                $priceData,
                $shopProduct,
                $inventoryError
            );

            $calculatedPrice = $calculatedPrice + $priceData->getItemPrice();
        }

        $priceData->setSpecPrice($calculatedPrice);

        return $inventoryError;
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
        $spec,
        $priceData,
        $shopProduct,
        $inventoryError
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
            $amount = $item->getAmount();

            if (!is_null($inventory)) {
                if ($amount > $inventory) {
                    $shopProductName = $shopProduct->getName();
                    $shopSpecName = $shopProductSpecItem->getShopSpecItem()->getSpec()->getName();
                    $shopSpecItemName = $shopProductSpecItem->getShopSpecItem()->getName();

                    $productArray = [
                        'product_name' => $shopProductName,
                        'spec_name' => $shopSpecName,
                        'item_name' => $shopSpecItemName,
                    ];

                    array_push($inventoryError, $productArray);
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

            if (is_null($amount) || empty($amount)) {
                $amount = 1;
            }

            $itemPrice = $price * $amount;
            $calculatedPrice = $calculatedPrice + $itemPrice;
        }

        $priceData->setItemPrice($calculatedPrice);

        return $inventoryError;
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

            $filters = array(
                'adminId' => $adminId,
                'permissionId' => $permission->getId(),
            );

            $key = $permission->getKey();
            $keyArray = explode(self::SHOP_PERMISSION_PREFIX, $key);
            if (count($keyArray) > 1 && !is_null($shopId)) {
                // judge by global permission and building permission
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
        $auth = $this->getSandboxAuthorization(self::SANDBOX_ADMIN_LOGIN_HEADER);

        $admin = $this->getRepo('Shop\ShopAdmin')->findOneBy(array(
            'username' => $auth->getUsername(),
            'password' => $auth->getPassword(),
        ));

        if (is_null($admin)) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        return $admin;
    }

    //--------------------throw customer http error --------------------//
    /**
     * Custom error view for shop order.
     *
     * @param $statusCode
     * @param $errorCode
     * @param $errorMessage
     * @param $errorArray
     *
     * @return View
     */
    protected function customShopOrderErrorView(
        $statusCode,
        $errorCode,
        $errorMessage,
        $errorArray
    ) {
        $translated = $this->get('translator')->trans($errorMessage);

        $view = new View();
        $view->setStatusCode($statusCode);
        $view->setData(array(
            'code' => $errorCode,
            'message' => $translated,
            'product_info' => $errorArray,
        ));
        $view->getData();

        return $view;
    }
}
