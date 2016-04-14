<?php

namespace Sandbox\SalesApiBundle\Controller\Product;

use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\Product\ProductController;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermission;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermissionMap;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminType;
use Sandbox\ApiBundle\Form\Product\ProductType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Admin product controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminProductController extends ProductController
{
    const ALREADY_EXISTS_MESSAGE = 'This resource already exists';
    const ROOM_DO_NOT_EXISTS = 'Room do not exists';
    const NEED_SEAT_NUMBER = 'Fixed Room Needs A Seat Number';
    const PRODUCT_EXISTS = 'Product with this Room already exists';
    const ROOM_IS_FULL = 'This Room is Full';

    /**
     * Product.
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
     * @Annotations\QueryParam(
     *    name="floor",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by floor id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="min_seat",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by seats minimum"
     * )
     *
     * @Annotations\QueryParam(
     *    name="max_seat",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by seats maximum"
     * )
     *
     * @Annotations\QueryParam(
     *    name="min_area",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by area minimum"
     * )
     *
     * @Annotations\QueryParam(
     *    name="max_area",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by area maximum"
     * )
     *
     * @Annotations\QueryParam(
     *    name="min_price",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by price minimum"
     * )
     *
     * @Annotations\QueryParam(
     *    name="max_price",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by price maximum"
     * )
     *
     * @Route("/products")
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
        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $this->getAdminId(),
            SalesAdminType::KEY_PLATFORM,
            array(
                SalesAdminPermission::KEY_BUILDING_PRODUCT,
                SalesAdminPermission::KEY_BUILDING_ORDER_PREORDER,
                SalesAdminPermission::KEY_BUILDING_ORDER_RESERVE,
            ),
            SalesAdminPermissionMap::OP_LEVEL_VIEW
        );

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $type = $paramFetcher->get('type');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $visible = $paramFetcher->get('visible');

        $floor = $paramFetcher->get('floor');
        $minSeat = $paramFetcher->get('min_seat');
        $maxSeat = $paramFetcher->get('max_seat');
        $minArea = $paramFetcher->get('min_area');
        $maxArea = $paramFetcher->get('max_area');
        $minPrice = $paramFetcher->get('min_price');
        $maxPrice = $paramFetcher->get('max_price');

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                SalesAdminPermission::KEY_BUILDING_PRODUCT,
            )
        );

        if (!is_null($buildingId) && !in_array((int) $buildingId, $myBuildingIds)) {
            return new View(array());
        }

        // sort by
        $sortBy = $paramFetcher->get('sortBy');
        $direction = $paramFetcher->get('direction');

        // search by name and number
        $search = $paramFetcher->get('query');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        $query = $this->getRepo('Product\Product')->getSalesAdminProducts(
            $myBuildingIds,
            $type,
            $city,
            $building,
            $visible,
            $sortBy,
            $direction,
            $search,
            $floor,
            $minSeat,
            $maxSeat,
            $minArea,
            $maxArea,
            $minPrice,
            $maxPrice
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
     * Get product by id.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/products/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getProductByIdAction(
        Request $request,
        $id
    ) {
        $product = $this->getRepo('Product\Product')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        $buildingId = $product->getRoom()->getBuildingId();

        // check user permission

        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $this->getAdminId(),
            SalesAdminType::KEY_PLATFORM,
            array(
                SalesAdminPermission::KEY_BUILDING_PRODUCT,
                SalesAdminPermission::KEY_BUILDING_ORDER_PREORDER,
                SalesAdminPermission::KEY_BUILDING_ORDER_RESERVE,
            ),
            SalesAdminPermissionMap::OP_LEVEL_VIEW,
            $buildingId
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_room']));
        $view->setData($product);

        return $view;
    }

    /**
     * Delete a product.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "OK"
     *  }
     * )
     *
     * @Route("/products/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteProductAction(
        Request $request,
        $id
    ) {
        // get product
        $product = $this->getRepo('Product\Product')->find($id);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        $buildingId = $product->getRoom()->getBuildingId();

        // check user permission
        $this->checkAdminProductPermission(
            SalesAdminPermissionMap::OP_LEVEL_EDIT,
            $buildingId
        );

        $roomId = $product->getRoomId();
        $room = $this->getRepo('Room\Room')->find($roomId);
        $type = $room->getType();
        $seatNumber = $product->getSeatNumber();
        if ($type == Room::TYPE_FIXED && !is_null($seatNumber)) {
            $fixed = $this->getRepo('Room\RoomFixed')->findOneBy(
                [
                    'seatNumber' => $seatNumber,
                    'room' => $roomId,
                ]
            );
            !is_null($fixed) ? $fixed->setAvailable(true) : null;
        }

        $product->setVisible(false);
        $product->setIsDeleted(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * Product.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful created"
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
    public function postProductAction(
        Request $request
    ) {
        $product = new Product();

        $form = $this->createForm(new ProductType(), $product);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $rule_include = $form['price_rule_include_ids']->getData();
        $rule_exclude = $form['price_rule_exclude_ids']->getData();
        $seatNumber = $form['seat_number']->getData();

        $room = $this->getRepo('Room\Room')->find($product->getRoomId());
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        $buildingId = $room->getBuildingId();

        // check user permission
        $this->checkAdminProductPermission(
            SalesAdminPermissionMap::OP_LEVEL_EDIT,
            $buildingId
        );

        $building = $this->getRepo('Room\RoomBuilding')->findOneBy(array(
            'id' => $buildingId,
            'status' => RoomBuilding::STATUS_ACCEPT,
            'visible' => true,
        ));
        if (is_null($building)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $type = $room->getType();
        $allowedPeople = $room->getAllowedPeople();
        if ($type == Room::TYPE_FIXED) {
            $count = $this->getRepo('Product\Product')->checkFixedRoomInProduct($product->getRoomId());
            if ((int) $count >= $allowedPeople) {
                return $this->customErrorView(
                    400,
                    400003,
                    self::ROOM_IS_FULL
                );
            }
            $roomInProduct = $this->getRepo('Product\Product')->findOneBy(
                [
                    'roomId' => $product->getRoomId(),
                    'visible' => true,
                    'seatNumber' => $seatNumber,
                ]
            );
            if (!is_null($roomInProduct)) {
                return $this->customErrorView(
                    400,
                    400002,
                    self::PRODUCT_EXISTS
                );
            }
        } else {
            $roomInProduct = $this->getRepo('Product\Product')->findOneBy(
                [
                    'roomId' => $product->getRoomId(),
                    'visible' => true,
                ]
            );
            if (!is_null($roomInProduct)) {
                return $this->customErrorView(
                    400,
                    400002,
                    self::PRODUCT_EXISTS
                );
            }
        }

        $startDate = $form['start_date']->getData();
        $startDate->setTime(00, 00, 00);
        $endDate = $form['end_date']->getData();
        $endDate->setTime(23, 59, 59);

        if (!is_null($seatNumber) && !empty($seatNumber) && $type == Room::TYPE_FIXED) {
            $product->setSeatNumber($seatNumber);
            $fixed = $this->getRepo('Room\RoomFixed')->findOneBy(
                [
                    'seatNumber' => $seatNumber,
                    'room' => $product->getRoomId(),
                ]
            );
            !is_null($fixed) ? $fixed->setAvailable(false) : null;
        } elseif ($type == Room::TYPE_FIXED) {
            throw new NotFoundHttpException(self::NEED_SEAT_NUMBER);
        }

        $product->setRoom($room);

        $now = new \DateTime('now');
        $product->setStartDate($startDate);
        $product->setEndDate($endDate);
        $product->setCreationDate($now);
        $product->setModificationDate($now);

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();

        $roomId = $product->getRoomId();
        $roomEm = $this->getRepo('Room\Room')->findOneById($roomId);
        $roomNumber = $roomEm->getNumber();
        $buildingId = $roomEm->getBuilding()->getId();
        $this->handleProductPost(
            $roomNumber,
            $buildingId,
            $rule_include,
            $rule_exclude
        );

        $response = array(
            'id' => $product->getId(),
        );

        return new View($response);
    }

    /**
     * Update a product.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful created"
     *  }
     * )
     *
     * @Route("/products/{id}")
     * @Method({"PUT"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putProductAction(
        Request $request,
        $id
    ) {
        $product = $this->getRepo('Product\Product')->find(array(
            'id' => $id,
            'isDeleted' => false,
        ));

        $form = $this->createForm(
            new ProductType(),
            $product,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $rule_include = $form['price_rule_include_ids']->getData();
        $rule_exclude = $form['price_rule_exclude_ids']->getData();

        $room = $this->getRepo('Room\Room')->find($product->getRoomId());
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        $buildingId = $room->getBuildingId();

        // check user permission
        $this->checkAdminProductPermission(
            SalesAdminPermissionMap::OP_LEVEL_EDIT,
            $buildingId
        );

        $startDate = $form['start_date']->getData();
        $startDate->setTime(00, 00, 00);
        $endDate = $form['end_date']->getData();
        $endDate->setTime(23, 59, 59);

        $product->setRoom($room);
        $product->setStartDate($startDate);
        $product->setEndDate($endDate);
        $product->setModificationDate(new \DateTime('now'));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $roomId = $product->getRoomId();
        $roomEm = $this->getRepo('Room\Room')->findOneById($roomId);
        $roomNumber = $roomEm->getNumber();
        $buildingId = $roomEm->getBuilding()->getId();
        $this->handleProductPut(
            $roomNumber,
            $buildingId,
            $rule_include,
            $rule_exclude
        );

        $response = array(
            'id' => $product->getId(),
        );

        return new View($response);
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     * @param int $buildingId
     */
    protected function checkAdminProductPermission(
        $opLevel,
        $buildingId = null
    ) {
        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $this->getAdminId(),
            SalesAdminType::KEY_PLATFORM,
            array(
                SalesAdminPermission::KEY_BUILDING_PRODUCT,
            ),
            $opLevel,
            $buildingId
        );
    }

    /**
     * @param int   $roomNumber
     * @param int   $buildingId
     * @param array $rule_include
     * @param array $rule_exclude
     */
    private function handleProductPost(
        $roomNumber,
        $buildingId,
        $rule_include,
        $rule_exclude
    ) {
        //add price rules
        if (!is_null($rule_include) && !empty($rule_include)) {
            self::postSalesPriceRule($roomNumber, $buildingId, $rule_include, 'include');
        }
        if (!is_null($rule_exclude) && !empty($rule_exclude)) {
            self::postSalesPriceRule($roomNumber, $buildingId, $rule_exclude, 'exclude');
        }
    }

    /**
     * @param int   $roomNumber
     * @param int   $buildingId
     * @param array $rule_include
     * @param array $rule_exclude
     */
    private function handleProductPut(
        $roomNumber,
        $buildingId,
        $rule_include,
        $rule_exclude
    ) {
        //edit price rules
        self::postSalesPriceRule($roomNumber, $buildingId, $rule_include, 'include');
        self::postSalesPriceRule($roomNumber, $buildingId, $rule_exclude, 'exclude');
    }
}
