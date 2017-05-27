<?php

namespace Sandbox\AdminApiBundle\Controller\Product;

use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\Product\ProductController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
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

        $productRentSet = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductRentSet')
            ->findOneBy(array('product' => $product));

        $product->setRentSet($productRentSet);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_room']));
        $view->setData($product);

        return $view;
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
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE],
            ],
            $opLevel
        );
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
}
