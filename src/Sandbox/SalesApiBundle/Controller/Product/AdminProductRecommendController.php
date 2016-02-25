<?php

namespace Sandbox\SalesApiBundle\Controller\Product;

use Knp\Component\Pager\Paginator;
use Sandbox\AdminApiBundle\Data\Product\ProductRecommendPosition;
use Sandbox\AdminApiBundle\Form\Product\ProductRecommendPositionType;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermission;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermissionMap;
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
 * @author   Mike He <mike.he@easylinks.com.cn>
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
    public function getProductsRecommendAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminProductPermission(SalesAdminPermissionMap::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');

        // search by name and number
        $search = $paramFetcher->get('query');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                SalesAdminPermission::KEY_BUILDING_PRODUCT,
            )
        );

        $query = $this->getRepo('Product\Product')->getSalesAdminProducts(
            $myBuildingIds,
            null,
            $city,
            $building,
            null,
            'sortTime',
            'DESC',
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
     * Add recommend products.
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
     * @Route("/products/recommend")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws BadRequestHttpException
     */
    public function addProductRecommendAction(
        Request $request
    ) {
        // get payload
        $productIds = json_decode($request->getContent(), true);
        if (is_null($productIds) || empty($productIds)) {
            return new View();
        }

        // enable recommend
        $this->setProductRecommend($productIds, true);

        return new View();
    }

    /**
     * Remove recommend products.
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
     *    name="id",
     *    array=true,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description="Id of recommend product to be disabled"
     * )
     *
     * @Route("/products/recommend")
     * @Method({"DELETE"})
     *
     * @return View
     *
     * @throws BadRequestHttpException
     */
    public function removeProductRecommendAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // get parameter
        $productIds = $paramFetcher->get('id');
        if (is_null($productIds) || empty($productIds)) {
            return new View();
        }

        // disable recommend
        $this->setProductRecommend($productIds, false);

        return new View();
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
        // get product
        $product = $this->getRepo('Product\Product')->findOneBy(array(
            'id' => $id,
            'recommend' => true,
        ));
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        $buildingId = $product->getRoom()->getBuildingId();

        // check user permission
        $this->checkAdminProductPermission(
            AdminPermissionMap::OP_LEVEL_EDIT,
            $buildingId
        );

        // get payload
        $position = new ProductRecommendPosition();

        $form = $this->createForm(new ProductRecommendPositionType(), $position);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->changeProductPosition($product, $position);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param array $productIds
     * @param bool  $recommend
     */
    private function setProductRecommend(
        $productIds,
        $recommend
    ) {
        foreach ($productIds as $productId) {
            $product = $this->getRepo('Product\Product')->find($productId);
            if (is_null($product)) {
                continue;
            }
            $buildingId = $product->getRoom()->getBuildingId();

            // check user permission
            $this->checkAdminProductPermission(
                SalesAdminPermissionMap::OP_LEVEL_EDIT,
                $buildingId
            );

            $product->setRecommend($recommend);

            if ($recommend) {
                $product->setSortTime(round(microtime(true) * 1000));
            } else {
                $product->setSortTime(null);
            }
        }

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @param Product                  $product
     * @param ProductRecommendPosition $position
     *
     * @return View
     */
    private function changeProductPosition(
        $product,
        $position
    ) {
        $action = $position->getAction();
        $cityId = $position->getCityId();
        $buildingId = $position->getBuildingId();

        // find city and building
        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        // move product
        if ($action == ProductRecommendPosition::ACTION_TOP) {
            $this->topProduct($product);
        } elseif ($action == ProductRecommendPosition::ACTION_UP
            || $action == ProductRecommendPosition::ACTION_DOWN) {
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
        $swapProduct = $this->getRepo('Product\Product')->findSwapProduct(
            $product,
            $action,
            $city,
            $building
        );

        // swap
        $productSortTime = $product->getSortTime();
        $product->setSortTime($swapProduct->getSortTime());
        $swapProduct->setSortTime($productSortTime);

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }
}
