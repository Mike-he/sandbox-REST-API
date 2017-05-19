<?php

namespace Sandbox\SalesApiBundle\Controller\Product;

use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Controller\Product\ProductController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Product\ProductLeasingSet;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Sandbox\ApiBundle\Form\Product\ProductPatchVisibleType;
use Sandbox\ApiBundle\Form\Product\ProductType;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
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

    use HasAccessToEntityRepositoryTrait;

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
     * @Annotations\QueryParam(
     *    name="permission",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="permission array"
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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
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
        $permissions = $paramFetcher->get('permission');

        // set default permission
        if (is_null($permissions) || empty($permissions)) {
            $permissions = array(
                AdminPermission::KEY_SALES_BUILDING_PRODUCT,
            );
        }

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            $permissions
        );

        if (empty($myBuildingIds) ||
            (
                !is_null($buildingId) &&
                !in_array((int) $buildingId, $myBuildingIds)
            )
        ) {
            return new View();
        }

        // sort by
        $sortBy = $paramFetcher->get('sortBy');
        $direction = $paramFetcher->get('direction');

        // search by name and number
        $search = $paramFetcher->get('query');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        $query = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->getSalesAdminProducts(
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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_SPACE,
                    'building_id' => $buildingId,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                    'building_id' => $buildingId,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
                    'building_id' => $buildingId,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
                    'building_id' => $buildingId,
                ),
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_DASHBOARD,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $favorite = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserFavorite')
            ->countFavoritesByObject(
                UserFavorite::OBJECT_PRODUCT,
                $id
            );

        $product->setFavorite($favorite);

        $productLeasingSets = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
            ->findBy(array('product' => $product));

        $product->setLeasingSets($productLeasingSets);

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

        $roomId = $product->getRoomId();
        $room = $this->getRepo('Room\Room')->find($roomId);
        $type = $room->getType();

        if ($type == Room::TYPE_FIXED) {
            $fixedSeats = $this->getRepo('Room\RoomFixed')->findBy(
                [
                    'room' => $roomId,
                ]
            );

            foreach ($fixedSeats as $fixedSeat) {
                $fixedSeat->setBasePrice(null);
            }
        }

        $product->setVisible(false);
        $product->setIsDeleted(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_PRODUCT,
            'logAction' => Log::ACTION_DELETE,
            'logObjectKey' => Log::OBJECT_PRODUCT,
            'logObjectId' => $product->getId(),
        ));
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
        $seats = $form['seats']->getData();
        $rentTypeIds = $form['rent_type_include_ids']->getData();
        $leasingSets = $form['leasing_sets']->getData();

        $room = $this->getRepo('Room\Room')->find($product->getRoomId());
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        $buildingId = $room->getBuildingId();

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

        $building = $this->getRepo('Room\RoomBuilding')->findOneBy(array(
            'id' => $buildingId,
            'status' => RoomBuilding::STATUS_ACCEPT,
            'visible' => true,
        ));
        if (is_null($building)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $type = $room->getType();

        $roomInProduct = $this->getRepo('Product\Product')->findOneBy(
            [
                'roomId' => $product->getRoomId(),
                'isDeleted' => false,
            ]
        );
        if (!is_null($roomInProduct)) {
            return $this->customErrorView(
                400,
                400002,
                self::PRODUCT_EXISTS
            );
        }

        $startDate = $form['start_date']->getData();
        $startDate->setTime(00, 00, 00);

        if (!is_null($seats) && $type == Room::TYPE_FIXED) {
            foreach ($seats as $seat) {
                if (array_key_exists('id', $seat) && array_key_exists('price', $seat)) {
                    $fixed = $this->getRepo('Room\RoomFixed')->findOneBy([
                        'id' => $seat['id'],
                        'roomId' => $product->getRoomId(),
                    ]);
                    !is_null($fixed) ? $fixed->setBasePrice($seat['price']) : null;
                }
            }
        } elseif ($type == Room::TYPE_FIXED) {
            throw new NotFoundHttpException(self::NEED_SEAT_NUMBER);
        } elseif ($type == Room::TYPE_LONG_TERM) {
            $earliestRendDate = $form['earliest_rent_date']->getData();
            $deposit = $product->getDeposit();
            $rentalInfo = $product->getRentalInfo();

            if (is_null($earliestRendDate) ||
                empty($earliestRendDate) ||
                is_null($deposit) ||
                is_null($rentalInfo) ||
                empty($rentalInfo)
            ) {
                return $this->customErrorView(
                    400,
                    Product::LONG_TERM_ROOM_MISSING_INFO_CODE,
                    Product::LONG_TERM_ROOM_MISSING_INFO_MESSAGE
                );
            }

            $earliestRendDate->setTime(00, 00, 00);
            $product->setEarliestRentDate($earliestRendDate);
        }

        $product->setRoom($room);

        $now = new \DateTime('now');
        $product->setStartDate($startDate);
        $product->setCreationDate($now);
        $product->setModificationDate($now);

        $this->handleRentTypesPost($rentTypeIds, $product);

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);

        $this->handleLeasingSetsPost($leasingSets, $product);

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

        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_PRODUCT,
            'logAction' => Log::ACTION_CREATE,
            'logObjectKey' => Log::OBJECT_PRODUCT,
            'logObjectId' => $product->getId(),
        ));

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

        $oldPrivate = $product->getPrivate();

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
        $rentTypeIds = $form['rent_type_include_ids']->getData();
        $leasingSets = $form['leasing_sets']->getData();

        $room = $this->getRepo('Room\Room')->find($product->getRoomId());
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        $buildingId = $room->getBuildingId();

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

        $startDate = $form['start_date']->getData();
        $startDate->setTime(00, 00, 00);

        $product->setRoom($room);
        $product->setStartDate($startDate);
        $product->setModificationDate(new \DateTime('now'));

        $roomId = $product->getRoomId();
        $roomEm = $this->getRepo('Room\Room')->findOneById($roomId);
        $seats = $form['seats']->getData();
        $type = $room->getType();

        if (!is_null($seats) && $type == Room::TYPE_FIXED) {
            foreach ($seats as $seat) {
                if (array_key_exists('id', $seat) && array_key_exists('price', $seat)) {
                    $fixed = $this->getRepo('Room\RoomFixed')->findOneBy([
                        'id' => $seat['id'],
                        'roomId' => $product->getRoomId(),
                    ]);
                    !is_null($fixed) ? $fixed->setBasePrice($seat['price']) : null;
                }
            }
        } elseif ($type == Room::TYPE_LONG_TERM) {
            $earliestRendDate = $form['earliest_rent_date']->getData();

            if (!is_null($earliestRendDate) && !empty($earliestRendDate)) {
                $earliestRendDate->setTime(00, 00, 00);
                $product->setEarliestRentDate($earliestRendDate);
            }

            $deposit = $product->getDeposit();
            $rentalInfo = $product->getRentalInfo();

            if (is_null($deposit) ||
                is_null($rentalInfo) ||
                empty($rentalInfo)
            ) {
                return $this->customErrorView(
                    400,
                    Product::LONG_TERM_ROOM_MISSING_INFO_CODE,
                    Product::LONG_TERM_ROOM_MISSING_INFO_MESSAGE
                );
            }
        }

        $roomNumber = $roomEm->getNumber();
        $buildingId = $roomEm->getBuilding()->getId();
        $this->handleProductPut(
            $roomNumber,
            $buildingId,
            $rule_include,
            $rule_exclude
        );

        $this->handleRentTypesPut($rentTypeIds, $product);

        $this->handleLeasingSetsPut($leasingSets, $product);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $private = $product->getPrivate();

        if ($private != $oldPrivate) {
            $action = Log::ACTION_PRIVATE;

            if ($oldPrivate && !$private) {
                $action = Log::ACTION_REMOVE_PRIVATE;
            }

            $this->generateAdminLogs(array(
                'logModule' => Log::MODULE_PRODUCT,
                'logAction' => $action,
                'logObjectKey' => Log::OBJECT_PRODUCT,
                'logObjectId' => $product->getId(),
            ));
        }

        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_PRODUCT,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_PRODUCT,
            'logObjectId' => $product->getId(),
        ));

        $response = array(
            'id' => $product->getId(),
        );

        return new View($response);
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
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchProductAction(
        Request $request,
        $id
    ) {
        // get product
        $product = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->findOneBy([
                'id' => $id,
                'isDeleted' => false,
            ]);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        $buildingId = $product->getRoom()->getBuildingId();
        $oldVisible = $product->getVisible();

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

        // bind data
        $productJson = $this->container->get('serializer')->serialize($product, 'json');
        $patch = new Patch($productJson, $request->getContent());
        $productJson = $patch->apply();

        $form = $this->createForm(new ProductPatchVisibleType(), $product);
        $form->submit(json_decode($productJson, true));

        $newVisible = $product->getVisible();

        !$newVisible ? $product->setAppointment(false) : null;

        $rentDate = $form['earliest_rent_date']->getData();

        if ($newVisible && ($newVisible !== $oldVisible)) {
            $this->setAppointmentEarliestDate(
                $product,
                $rentDate
            );
//            $product->setAppointment(true);
        } elseif ($newVisible && $product->isAppointment()) {
            $this->setAppointmentEarliestDate(
                $product,
                $rentDate
            );
        }

        $product->setModificationDate(new \DateTime('now'));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_PRODUCT,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_PRODUCT,
            'logObjectId' => $product->getId(),
        ));
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    protected function checkAdminProductPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                ['key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT],
                ['key' => AdminPermission::KEY_SALES_BUILDING_SPACE],
                ['key' => AdminPermission::KEY_SALES_BUILDING_BUILDING],
                ['key' => AdminPermission::KEY_SALES_BUILDING_ROOM],
            ),
            $opLevel
        );
    }

    /**
     * @param $product
     * @param $rentDate
     */
    private function setAppointmentEarliestDate(
        $product,
        $rentDate
    ) {
        if (!is_null($rentDate) && !empty($rentDate)) {
            $rentDate->setTime(0, 0, 0);
            $product->setEarliestRentDate($rentDate);
        }
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

    private function handleRentTypesPost(
        $rentTypeIds,
        $product
    ) {
        foreach ($rentTypeIds as $rentTypeId) {
            $rentType = $this->getLeaseRentTypesRepo()->find($rentTypeId);
            if (is_null($rentType)) {
                throw new NotFoundHttpException(CustomErrorMessagesConstants::ERROR_LEASE_RENT_TYPE_NOT_FOUND_MESSAGE);
            }
            $product->addLeaseRentTypes($rentType);
        }
    }

    private function handleRentTypesPut(
        $rentTypeIds,
        $product
    ) {
        $rentTypes = $product->getLeaseRentTypes();
        foreach ($rentTypes as $rentType) {
            $product->removeLeaseRentTypes($rentType);
        }

        foreach ($rentTypeIds as $rentTypeId) {
            $rentType = $this->getLeaseRentTypesRepo()->find($rentTypeId);
            if (is_null($rentType)) {
                throw new NotFoundHttpException(CustomErrorMessagesConstants::ERROR_LEASE_RENT_TYPE_NOT_FOUND_MESSAGE);
            }
            $product->addLeaseRentTypes($rentType);
        }
    }

    /**
     * @param $leasingSets
     * @param $product
     */
    private function handleLeasingSetsPost(
        $leasingSets,
        $product
    ) {
        $em = $this->getDoctrine()->getManager();
        foreach ($leasingSets as $leasingSet) {
            $productLeasingSet = new ProductLeasingSet();
            $productLeasingSet->setProduct($product);
            $productLeasingSet->setUnitPrice($leasingSet['unit_price']);
            $productLeasingSet->setBasePrice($leasingSet['base_price']);
            $productLeasingSet->setAmount($leasingSet['amount']);
            $em->persist($productLeasingSet);
        }
    }

    /**
     * @param $leasingSets
     * @param $product
     */
    private function handleLeasingSetsPut(
        $leasingSets,
        $product
    ) {
        $em = $this->getDoctrine()->getManager();
        $productLeasingSets = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
            ->findBy(array('product' => $product));

        if ($productLeasingSets) {
            foreach ($productLeasingSets as $productLeasingSet) {
                $em->remove($productLeasingSet);
            }
        }

        foreach ($leasingSets as $leasingSet) {
            $productLeasingSet = new ProductLeasingSet();
            $productLeasingSet->setProduct($product);
            $productLeasingSet->setUnitPrice($leasingSet['unit_price']);
            $productLeasingSet->setBasePrice($leasingSet['base_price']);
            $productLeasingSet->setAmount($leasingSet['amount']);
            $em->persist($productLeasingSet);
        }
    }
}
