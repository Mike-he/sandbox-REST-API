<?php

namespace Sandbox\AdminApiBundle\Controller\Product;

use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Room\Room;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Admin product recommend controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminProductRecommendController extends AdminProductController
{
    /**
     * Get products recommend.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by city id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="(office|meeting|flexible|fixed)",
     *    strict=true,
     *    description="Filter by room type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="visible",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by visibility"
     * )
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
     *    name="sortBy",
     *    array=false,
     *    default="creationDate",
     *    nullable=true,
     *    requirements="(area|basePrice)",
     *    strict=true,
     *    description="Sort by date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="direction",
     *    array=false,
     *    default="DESC",
     *    nullable=true,
     *    requirements="(ASC|DESC)",
     *    strict=true,
     *    description="sort direction"
     * )
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Route("/products/recommend")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getProductsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminProductPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $type = $paramFetcher->get('type');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');

        // sort by
        $direction = $paramFetcher->get('direction');

        // search by name and number
        $search = $paramFetcher->get('query');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        $query = $this->getRepo('Product\Product')->getAdminProducts(
            $type,
            $city,
            $building,
            null,
            'sortTime',
            $direction,
            $search,
            true
        );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $query,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Set recommend products.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/products")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws BadRequestHttpException
     */
    public function setProductRecommendAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminProductPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // get payload
        $payload = json_decode($request->getContent(), true);
        $add = $payload['add'];
        $remove = $payload['remove'];

        foreach ($add as $productId) {
            $this->setProductRecommend($productId, true);
        }

        foreach ($remove as $productId) {
            $this->setProductRecommend($productId, false);
        }

        if (!is_null($add) || !is_null($remove)) {
            // save
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        return new View();
    }

    /**
     * @param $productId
     * @param $recommend
     */
    private function setProductRecommend(
        $productId,
        $recommend
    ) {
        $product = $this->getRepo('Product\Product')->find($productId);
        if (is_null($product)) {
            return;
        }

        $product->setRecommend($recommend);

        if ($recommend) {
            $product->setSortTime(round(microtime(true) * 1000));
        } else {
            $product->setSortTime(null);
        }
    }

    /**
     * Change position of a given recommend product.
     *
     * @param Request $request the request object
     * @param int     $id      id of the product
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/products/{id}/recommend/position")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws BadRequestHttpException
     */
    public function changeProductRecommendPositionAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminProductPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // get product
        $product = $this->getRepo('Product\Product')->find($id);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        // get payload
        $payload = json_decode($request->getContent(), true);
        $action = $payload['action'];
        $cityId = $payload['city_id'];
        $buildingId = $payload['building_id'];

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        // move product
        if ($action == 'top') {
            $this->topProduct($product);
        } elseif ($action == 'up' || $action == 'down') {
            $this->moveProduct($product, $action, $city, $building);
        }

        return new View();
    }

    /**
     * @param Product $product
     */
    private function topProduct(
        $product
    ) {
        if (is_null($product) || !$product->isRecommend()) {
            return;
        }

        // set sortTime to current timestamp
        $product->setSortTime(round(microtime(true) * 1000));

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @param Product      $product
     * @param string       $action
     * @param RoomCity     $city
     * @param RoomBuilding $building
     */
    private function moveProduct(
        $product,
        $action,
        $city,
        $building
    ) {
        if (is_null($product) || !$product->isRecommend()) {
            return;
        }

        $swapProduct = $this->getRepo('Product\Product')->findSwapProduct(
            $product,
            $action,
            $city,
            $building
        );

        if (is_null($swapProduct) || !$product->isRecommend()) {
            return;
        }

        // swap
        $productSortTime = $product->getSortTime();
        $product->setSortTime($swapProduct->getSortTime());
        $swapProduct->setSortTime($productSortTime);

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }
}
