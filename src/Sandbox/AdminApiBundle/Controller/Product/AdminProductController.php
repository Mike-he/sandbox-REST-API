<?php

namespace Sandbox\AdminApiBundle\Controller\Product;

use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\Product\ProductController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Form\Product\ProductType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

    /**
     * Product.
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
        $this->checkAdminProductPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $type = $paramFetcher->get('type');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $visible = $paramFetcher->get('visible');

        $sortBy = $paramFetcher->get('sortBy');
        $direction = $paramFetcher->get('direction');

        $city = !is_null($cityId) ? $this->getRepo('Room\RoomCity')->find($cityId) : null;
        $building = !is_null($buildingId) ? $this->getRepo('Room\RoomBuilding')->find($buildingId) : null;

        $query = $this->getRepo('Product\Product')->getAdminProducts(
            $type,
            $city,
            $building,
            $visible,
            $sortBy,
            $direction
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
     * Product.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
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
     *    description="search query"
     * )
     *
     * @Route("/products/search")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getProductsSearchAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminProductPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $query = $paramFetcher->get('query');

        $products = $this->getRepo('Product\Product')->searchProducts(
            $query
        );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $products,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get product by id.
     *
     * @param Request $request
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
        $this->checkAdminProductPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $product = $this->getRepo('Product\Product')->find($id);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_room']));
        $view->setData($product);

        return $view;
    }

    /**
     * Delete a product.
     *
     * @param Request $request the request object
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
        $this->checkAdminProductPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        // get product
        $product = $this->getRepo('Product\Product')->find($id);

        $product->setVisible(false);

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
        $this->checkAdminProductPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $product = new Product();

        $form = $this->createForm(new ProductType(), $product);
        $form->handleRequest($request);
        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $rule_include = $request->request->get('price_rule_include_ids');
        $rule_exclude = $request->request->get('price_rule_exclude_ids');

        $room = $this->getRepo('Room\Room')->find($product->getRoomId());
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);
        $start = $form['start_date']->getData();
        $startDate = new \DateTime($start);
        $startDate->setTime(00, 00, 00);
        $end = $form['end_date']->getData();
        $endDate = new \DateTime($end);
        $endDate->setTime(23, 59, 59);

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
        $this->checkAdminProductPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $product = $this->getRepo('Product\Product')->find($id);

        $form = $this->createForm(
            new ProductType(),
            $product,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $rule_include = $request->request->get('price_rule_include_ids');
        $rule_exclude = $request->request->get('price_rule_exclude_ids');

        $room = $this->getRepo('Room\Room')->find($product->getRoomId());
        $this->throwNotFoundIfNull($room, self::NOT_FOUND_MESSAGE);

        $start = $form['start_date']->getData();
        $startDate = new \DateTime($start);
        $startDate->setTime(00, 00, 00);
        $end = $form['end_date']->getData();
        $endDate = new \DateTime($end);
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
     * Check user permission.
     *
     * @param Integer $OpLevel
     */
    private function checkAdminProductPermission(
        $OpLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_PRODUCT,
            $OpLevel
        );
    }

    /**
     * @param Integer $roomNumber
     * @param Integer $buildingId
     * @param Array   $rule_include
     * @param Array   $rule_exclude
     */
    private function handleProductPost(
        $roomNumber,
        $buildingId,
        $rule_include,
        $rule_exclude
    ) {
        //add price rules
        if (!is_null($rule_include) || !empty($rule_include)) {
            self::postPriceRule($roomNumber, $buildingId, $rule_include, 'include');
        }
        if (!is_null($rule_exclude) || !empty($rule_include)) {
            self::postPriceRule($roomNumber, $buildingId, $rule_exclude, 'exclude');
        }
    }
}
