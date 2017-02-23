<?php

namespace Sandbox\AdminApiBundle\Controller\Product;

use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Product\ProductController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Form\Product\ProductPatchType;
use Sandbox\ApiBundle\Form\Product\ProductType;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Admin product controller.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
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
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by sales company id"
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
     *    name="annual_rent",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="tag of annual rent"
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_RESERVE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_PREORDER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT_APPOINTMENT_VERIFY],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $type = $paramFetcher->get('type');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $visible = $paramFetcher->get('visible');
        $companyId = $paramFetcher->get('company');

        $floor = $paramFetcher->get('floor');
        $minSeat = $paramFetcher->get('min_seat');
        $maxSeat = $paramFetcher->get('max_seat');
        $minArea = $paramFetcher->get('min_area');
        $maxArea = $paramFetcher->get('max_area');
        $minPrice = $paramFetcher->get('min_price');
        $maxPrice = $paramFetcher->get('max_price');
        $annualRent = $paramFetcher->get('annual_rent');

        // sort by
        $sortBy = $paramFetcher->get('sortBy');
        $direction = $paramFetcher->get('direction');

        // search by name and number
        $search = $paramFetcher->get('query');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        $query = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->getAdminProducts(
                $type,
                $city,
                $building,
                $visible,
                $sortBy,
                $direction,
                $search,
                false,
                $companyId,
                $floor,
                $minSeat,
                $maxSeat,
                $minArea,
                $maxArea,
                $minPrice,
                $maxPrice,
                $annualRent
            );

        // set annual product appointment count
        $this->setAnnualRentProductAppointmentCounts(
            $query,
            $annualRent
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
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_PREORDER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_RESERVE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT_APPOINTMENT_VERIFY],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        $product = $this->getRepo('Product\Product')->find(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        $room = $product->getRoom();
        $roomType = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomTypes')
            ->findOneBy(array(
                'name' => $room->getType(),
            ));

        $room->setRentType($roomType->getType());

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
        // check user permission
        $this->checkAdminProductPermission(AdminPermission::OP_LEVEL_EDIT);

        // get product
        $product = $this->getRepo('Product\Product')->find($id);

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
        // check user permission
        $this->checkAdminProductPermission(AdminPermission::OP_LEVEL_EDIT);

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

        $room = $this->getRepo('Room\Room')->find($product->getRoomId());
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);
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
        }

        $product->setRoom($room);

        $now = new \DateTime('now');
        $product->setStartDate($startDate);
        $product->setCreationDate($now);
        $product->setModificationDate($now);

        $this->handleRentTypesPost($rentTypeIds, $product);

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
     * patch a product.
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
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT_APPOINTMENT_VERIFY],
            ],
            AdminPermission::OP_LEVEL_EDIT
        );

        $product = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->find(array(
                'id' => $id,
                'isDeleted' => false,
            ));
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        // bind data
        $productJson = $this->get('serializer')->serialize($product, 'json');
        $patch = new Patch($productJson, $request->getContent());
        $productJson = $patch->apply();

        $form = $this->createForm(new ProductPatchType(), $product);
        $form->submit(json_decode($productJson, true));

        // check data validation
        if ($product->isAnnualRent()) {
            if (is_null($product->getAnnualRentUnitPrice())
            || is_null($product->getAnnualRentUnit())) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
        } else {
            $product->setAnnualRentUnitPrice(null);
            $product->setAnnualRentUnit(null);
            $product->setAnnualRentDescription(null);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
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
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT_APPOINTMENT_VERIFY],
            ],
            AdminPermission::OP_LEVEL_EDIT
        );

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
        $rentTypeIds = $form['rent_type_include_ids']->getData();

        $room = $this->getRepo('Room\Room')->find($product->getRoomId());
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        $startDate = $form['start_date']->getData();
        $startDate->setTime(00, 00, 00);

        $product->setRoom($room);
        $product->setStartDate($startDate);
        $product->setModificationDate(new \DateTime('now'));

        $this->handleRentTypesPut($rentTypeIds, $product);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $roomId = $product->getRoomId();
        $roomEm = $this->getRepo('Room\Room')->findOneById($roomId);
        $seats = $form['seats']->getData();

        if (!is_null($seats)) {
            foreach ($seats as $seat) {
                if (array_key_exists('id', $seat) && array_key_exists('price', $seat)) {
                    $fixed = $this->getRepo('Room\RoomFixed')->findOneBy([
                        'id' => $seat['id'],
                        'roomId' => $product->getRoomId(),
                    ]);
                    !is_null($fixed) ? $fixed->setBasePrice($seat['price']) : null;
                }
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

        $response = array(
            'id' => $product->getId(),
        );

        return new View($response);
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    protected function checkAdminProductPermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE],
            ],
            $opLevel
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
            self::postPriceRule($roomNumber, $buildingId, $rule_include, 'include');
        }
        if (!is_null($rule_exclude) && !empty($rule_exclude)) {
            self::postPriceRule($roomNumber, $buildingId, $rule_exclude, 'exclude');
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
        self::postPriceRule($roomNumber, $buildingId, $rule_include, 'include');
        self::postPriceRule($roomNumber, $buildingId, $rule_exclude, 'exclude');
    }

    /**
     * @param $products
     * @param $annualRent
     */
    private function setAnnualRentProductAppointmentCounts(
        $products,
        $annualRent
    ) {
        if (is_null($annualRent) || $annualRent == false) {
            return;
        }

        foreach ($products as $item) {
            $totalAppointments = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\ProductAppointment')
                ->countProductAppointment($item->getId());

            $pendingAppointments = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\ProductAppointment')
                ->countProductAppointment(
                    $item->getId(),
                    ProductAppointment::STATUS_PENDING
                );

            $item->setPendingAppointmentCounts($pendingAppointments);
            $item->setTotalAppointmentCounts($totalAppointments);
        }
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
}
