<?php

namespace Sandbox\ApiBundle\Controller\Product;

use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomTypeTags;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Traits\CurlUtil;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Product Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ProductController extends SalesRestController
{
    use CurlUtil;

    const PRODUCT_NOT_FOUND_CODE = 400012;
    const PRODUCT_NOT_FOUND_MESSAGE = 'Product Not Found';

    /**
     * @return View
     */
    public function getAllProductsAction()
    {
        $products = $this->getRepo('Product\Product')->findAll();

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($products);

        return $view;
    }

    /**
     * @param $id
     *
     * @return View
     */
    public function getOneProduct(
        $id
    ) {
        $product = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->find($id);
        if (is_null($product)) {
            return $this->customErrorView(
                400,
                self::PRODUCT_NOT_FOUND_CODE,
                self::PRODUCT_NOT_FOUND_MESSAGE
            );
        }
        $room = $product->getRoom();
        $type = $room->getType();
        $typeTag = $room->getTypeTag();
        if (!is_null($typeTag)) {
            $typeTagDescription = $this->get('translator')->trans(RoomTypeTags::TRANS_PREFIX.$typeTag);
            $room->setTypeTagDescription($typeTagDescription);
        }

        $productLeasingSets = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
            ->findBy(array('product' => $product));

        $basePrice = [];
        if (!empty($productLeasingSets)) {
            foreach ($productLeasingSets as $productLeasingSet) {
                $unitType = $productLeasingSet->getUnitPrice();
                $unitPrice = $this->get('translator')
                    ->trans(ProductOrderExport::TRANS_ROOM_UNIT.$unitType);
                $productLeasingSet->setUnitPrice($unitPrice);
                $productLeasingSet->setUnitType($unitType);

                $basePrice[$unitPrice] = $productLeasingSet->getBasePrice();
            }
            $product->setLeasingSets($productLeasingSets);

            if ($type == Room::TYPE_DESK && $typeTag == Room::TAG_DEDICATED_DESK) {
                $price = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomFixed')
                    ->getFixedSeats($room);
                if (!is_null($price)) {
                    $product->setBasePrice($price);
                    $product->setUnitPrice($unitPrice);
                }
            } else {
                $pos = array_search(min($basePrice), $basePrice);
                $product->setBasePrice($basePrice[$pos]);
                $product->setUnitPrice($pos);
            }
        }

        $productRentSet = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductRentSet')
            ->findOneBy(array('product' => $product));

        $product->setRentSet($productRentSet);

        $building = $room->getBuilding();
        $removeDates = $building->getRemoveDatesInfo();
        if (!is_null($removeDates) && !empty($removeDates)) {
            $building->setRemoveDates(json_decode($removeDates, true));
        }

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));
        $view->setData($product);

        return $view;
    }

    /**
     * @param int    $roomNumber
     * @param int    $buildingId
     * @param array  $ids
     * @param string $type
     *
     * @throws BadRequestHttpException
     */
    protected function postPriceRule(
        $roomNumber,
        $buildingId,
        $ids,
        $type
    ) {
        if (empty($ids)) {
            return;
        }

        // get auth
        $headers = array_change_key_case($_SERVER, CASE_LOWER);
        $auth = $headers['http_authorization'];

        $globals = $this->container->get('twig')->getGlobals();

        $typeUrl = null;

        switch ($type) {
            case 'include':
                $typeUrl = $globals['crm_api_admin_price_rule_include'];
                break;
            case 'exclude':
                $typeUrl = $globals['crm_api_admin_price_rule_exclude'];
                break;
            default:
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].$typeUrl;
        $apiUrl = preg_replace('/{buildingId}.*?/', "$buildingId", $apiUrl);
        $apiUrl = preg_replace('/{roomNo}.*?/', "$roomNumber", $apiUrl);
        // init curl
        $ch = curl_init($apiUrl);

        $this->callAPI(
            $ch,
            'POST',
            array('Authorization: '.$auth),
            json_encode($ids)
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode != self::HTTP_STATUS_OK_NO_CONTENT) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
    }

    /**
     * @param int    $roomNumber
     * @param int    $buildingId
     * @param array  $ids
     * @param string $type
     *
     * @throws BadRequestHttpException
     */
    protected function postSalesPriceRule(
        $roomNumber,
        $buildingId,
        $ids,
        $type
    ) {
        if (empty($ids)) {
            return;
        }

        // get auth
        $headers = array_change_key_case($_SERVER, CASE_LOWER);
        $auth = $headers['http_authorization'];

        $globals = $this->container->get('twig')->getGlobals();

        $typeUrl = null;

        switch ($type) {
            case 'include':
                $typeUrl = $globals['crm_api_sales_admin_price_rule_include'];
                break;
            case 'exclude':
                $typeUrl = $globals['crm_api_sales_admin_price_rule_exclude'];
                break;
            default:
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].$typeUrl;
        $apiUrl = preg_replace('/{buildingId}.*?/', "$buildingId", $apiUrl);
        $apiUrl = preg_replace('/{roomNo}.*?/', "$roomNumber", $apiUrl);
        // init curl
        $ch = curl_init($apiUrl);

        $this->callAPI(
            $ch,
            'POST',
            array('Authorization: '.$auth),
            json_encode($ids)
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode != self::HTTP_STATUS_OK_NO_CONTENT) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        curl_close($ch);
    }

    /**
     * @param Product $product
     */
    protected function generateProductInfo(
        $product
    ) {
        $room = $product->getRoom();
        $type = $room->getType();
        $tag = $room->getTypeTag();

        $typeDescription = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$type);
        $room->setTypeDescription($typeDescription);

        $typeTag = $room->getTypeTag();
        if (!is_null($typeTag)) {
            $typeTagDescription = $this->get('translator')->trans(RoomTypeTags::TRANS_PREFIX.$typeTag);
            $room->setTypeTagDescription($typeTagDescription);
        }

        $productLeasingSets = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
            ->findBy(array('product' => $product));

        $basePrice = [];
        if (!empty($productLeasingSets)) {
            foreach ($productLeasingSets as $productLeasingSet) {
                $unitPrice = $this->get('translator')
                    ->trans(ProductOrderExport::TRANS_ROOM_UNIT.$productLeasingSet->getUnitPrice());
                $productLeasingSet->setUnitPrice($unitPrice);

                $basePrice[$unitPrice] = $productLeasingSet->getBasePrice();
            }
            $product->setLeasingSets($productLeasingSets);

            if ($type == Room::TYPE_DESK && $tag == Room::TAG_DEDICATED_DESK) {
                $price = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomFixed')
                    ->getFixedSeats($room);
                if (!is_null($price)) {
                    $product->setBasePrice($price);
                    $product->setUnitPrice($unitPrice);
                }
            } else {
                $pos = array_search(min($basePrice), $basePrice);
                $product->setBasePrice($basePrice[$pos]);
                $product->setUnitPrice($pos);
            }
        }

        $productRentSet = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductRentSet')
            ->findOneBy(array(
                'product' => $product,
                'status' => true,
            ));

        $product->setRentSet($productRentSet);

        if ($type == Room::TYPE_OFFICE && empty($productLeasingSets) && !is_null($productRentSet)) {
            $unitPrice = $this->get('translator')
                ->trans(ProductOrderExport::TRANS_ROOM_UNIT.$productRentSet->getUnitPrice());

            $product->setBasePrice($productRentSet->getBasePrice());
            $product->setUnitPrice($unitPrice);
        }

        return $product;
    }
}
