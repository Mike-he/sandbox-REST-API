<?php

namespace Sandbox\SalesApiBundle\Controller\Product;

use JMS\Serializer\SerializationContext;
use Sandbox\AdminApiBundle\Data\Product\ProductRecommendPosition;
use Sandbox\AdminApiBundle\Form\Product\ProductRecommendPositionType;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Traits\HandleSpacesDataTrait;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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
    use HandleSpacesDataTrait;

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
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=false,
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
        $this->checkAdminProductPermission(AdminPermission::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $buildingId = $paramFetcher->get('building');

        $building = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->find($buildingId);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_PLATFORM_BUILDING,
                AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                AdminPermission::KEY_SALES_BUILDING_SPACE,
                AdminPermission::KEY_SALES_BUILDING_BUILDING,
                AdminPermission::KEY_SALES_BUILDING_ROOM,
            )
        );

        if (!in_array($buildingId, $myBuildingIds)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->countRecommendedSpaces(
                $buildingId,
                true
            );

        $spaces = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->findRecommendedSpaces(
                $pageLimit,
                $offset,
                $buildingId,
                true
            );

        $spaces = $this->handleSpacesData($spaces);

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $spaces,
                'total_count' => (int) $count,
            )
        );
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['admin_spaces'])
        );

        return $view;
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
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        // get payload
        $productIds = json_decode($request->getContent(), true);
        if (is_null($productIds) || empty($productIds)) {
            return new View();
        }

        // enable recommend
        $products = $this->setProductRecommend($productIds, true);

        foreach ($products as $product) {
            $this->generateAdminLogs(array(
                'logModule' => Log::MODULE_PRODUCT,
                'logAction' => Log::ACTION_RECOMMEND,
                'logObjectKey' => Log::OBJECT_PRODUCT,
                'logObjectId' => $product->getId(),
            ));
        }

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
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        // get parameter
        $productIds = $paramFetcher->get('id');
        if (is_null($productIds) || empty($productIds)) {
            return new View();
        }

        // disable recommend
        $products = $this->setProductRecommend($productIds, false);

        foreach ($products as $product) {
            $this->generateAdminLogs(array(
                'logModule' => Log::MODULE_PRODUCT,
                'logAction' => Log::ACTION_REMOVE_RECOMMEND,
                'logObjectKey' => Log::OBJECT_PRODUCT,
                'logObjectId' => $product->getId(),
            ));
        }

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
        $product = $this->getRepo('Product\Product')
            ->findOneBy(array(
                'id' => $id,
                'salesRecommend' => true,
                'visible' => true,
                'isDeleted' => false,
            ));
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        $buildingId = $product->getRoom()->getBuildingId();

        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                    'building_id' => $buildingId,
                ),
            ),
            AdminPermission::OP_LEVEL_EDIT
        );

        // get payload
        $position = new ProductRecommendPosition();

        $form = $this->createForm(new ProductRecommendPositionType(), $position);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->changeProductPosition($product, $position);
    }

    /**
     * @param array $productIds
     * @param bool  $recommend
     */
    private function setProductRecommend(
        $productIds,
        $recommend
    ) {
        $products = [];
        $buildings = [];
        foreach ($productIds as $productId) {
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->findOneBy([
                    'id' => $productId,
                    'visible' => true,
                    'isDeleted' => false,
                ]);
            if (is_null($product)) {
                continue;
            }
            $buildingId = $product->getRoom()->getBuildingId();

            // check user permission
            $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
                $this->getAdminId(),
                array(
                    array(
                        'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                        'building_id' => $buildingId,
                    ),
                ),
                AdminPermission::OP_LEVEL_EDIT
            );

            if ($recommend) {
                if ($product->isSalesRecommend()) {
                    continue;
                }

                $product->setSalesSortTime(round(microtime(true) * 1000));

                if (!array_key_exists($buildingId, $buildings)) {
                    $buildings["$buildingId"] = 1;
                } else {
                    $buildings["$buildingId"] += 1;
                }
            } else {
                $product->setSalesSortTime(null);
            }

            $product->setSalesRecommend($recommend);
            array_push($products, $product);
        }

        foreach ($buildings as $key => $val) {
            $existCount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\Room')
                ->countRecommendedSpaces(
                    $key,
                    true
                );

            $finalCount = $existCount + $val;
            if ($finalCount > Product::SALES_RECOMMEND_LIMIT) {
                throw new ConflictHttpException();
            }
        }

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $products;
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
        $buildingId = $product->getRoom()->getBuildingId();

        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        // move product
        if ($action == ProductRecommendPosition::ACTION_TOP) {
            $this->topProduct($product);
        } elseif ($action == ProductRecommendPosition::ACTION_UP
            || $action == ProductRecommendPosition::ACTION_DOWN) {
            $this->moveProduct($product, $action, $building);
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
        $product->setSalesSortTime(round(microtime(true) * 1000));

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @param Product      $product
     * @param string       $action
     * @param RoomBuilding $building
     */
    private function moveProduct(
        $product,
        $action,
        $building
    ) {
        $swapProduct = $this->getRepo('Product\Product')->findSwapProduct(
            $product,
            $action,
            $building
        );

        if (is_null($swapProduct)) {
            return;
        }

        // swap
        $productSortTime = $product->getSalesSortTime();
        $product->setSalesSortTime($swapProduct->getSalesSortTime());
        $swapProduct->setSalesSortTime($productSortTime);

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }
}
