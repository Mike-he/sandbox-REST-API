<?php

namespace Sandbox\ApiBundle\Controller\Product;

use Sandbox\ApiBundle\Entity\Room\Room;
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
        $product = $this->getRepo('Product\Product')->find($id);
        if (is_null($product)) {
            return $this->customErrorView(
                400,
                self::PRODUCT_NOT_FOUND_CODE,
                self::PRODUCT_NOT_FOUND_MESSAGE
            );
        }

        $room = $product->getRoom();
        $type = $room->getType();
        if ($type == Room::TYPE_LONG_TERM) {
            $company = $room->getBuilding()->getCompany();

            $collectionMethod = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
                ->getCollectionMethod($company, $type);

            $product->setCollectionMethod($collectionMethod);
        }

        $productLeasingSets = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
            ->findBy(array('product' => $product));
        $product->setLeasingSets($productLeasingSets);

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
}
