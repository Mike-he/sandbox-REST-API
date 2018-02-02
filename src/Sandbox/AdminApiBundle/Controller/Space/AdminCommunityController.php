<?php

namespace Sandbox\AdminApiBundle\Controller\Space;

use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Traits\HandleSpacesDataTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AdminCommunityController.
 */
class AdminCommunityController extends SandboxRestController
{
    use HandleSpacesDataTrait;

    /**
     * Get Sales Companies.
     *
     * @param Request $request the request object
     *
     * @Method({"GET"})
     * @Route("/companies")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getCompaniesAction(
        Request $request
    ) {
        $companies = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findBy(array('banned' => false));

        return new View($companies);
    }

    /**
     * Get Communities.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description="company id"
     * )
     *
     * @Method({"GET"})
     * @Route("/communities")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getCommunitiesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->checkAdminCommunityPermissions(AdminPermission::OP_LEVEL_VIEW);

        $companyId = $paramFetcher->get('company');
        $company = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->find($companyId);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $using = $this->getBuildingInfo($companyId, RoomBuilding::STATUS_ACCEPT, true);
        $invisible = $this->getBuildingInfo($companyId, RoomBuilding::STATUS_ACCEPT, false);
        $banned = $this->getBuildingInfo($companyId, RoomBuilding::STATUS_BANNED);
        $pending = $this->getBuildingInfo($companyId, RoomBuilding::STATUS_PENDING);

        $result = array(
            'using' => $using,
            'invisible' => $invisible,
            'banned' => $banned,
            'pending' => $pending,
        );

        return new View($result);
    }

    /**
     * Get Community Roomtypes.
     *
     * @param Request $request
     *
     * @Route("/community/{id}/roomtypes")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getCommunityRoomTypesAction(
        Request $request,
        $id
    ) {
        $this->checkAdminCommunityPermissions(AdminPermission::OP_LEVEL_VIEW);

        $building = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($id);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        $roomTypes = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomTypes')->findAll();

        $imageUrl = $this->getParameter('image_url');
        $result = array();
        foreach ($roomTypes as $roomType) {
            $using_number = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->countsProductByType(
                    $id,
                    $roomType->getName(),
                    true
                );

            $all_number = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\Room')
                ->countsRoomByBuilding(
                    $id,
                    $roomType->getName()
                );

            if ($all_number > 0) {
                $result[] = array(
                    'id' => $roomType->getId(),
                    'type' => $roomType->getName(),
                    'name' => $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$roomType->getName()),
                    'icon' => $imageUrl.$roomType->getIcon(),
                    'building_id' => $id,
                    'using_number' => (int) $using_number,
                    'all_number' => (int) $all_number,
                );
            }
        }

        return new View($result);
    }

    /**
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many spaces to return "
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
     *    name="room_types",
     *    default=null,
     *    nullable=true,
     *    array=true,
     *    description="types of room"
     * )
     *
     * @Annotations\QueryParam(
     *    name="visible",
     *    default=null,
     *    nullable=true,
     *    array=false,
     *    description="show product visible or not"
     * )
     *
     * @Annotations\QueryParam(
     *    name="no_product",
     *    default=null,
     *    nullable=true,
     *    array=false,
     *    description="show product visible or not"
     * )
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    default=null,
     *    nullable=true,
     *    array=false,
     *    description="search spaces"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    default=null,
     *    nullable=true,
     *    array=false,
     *    description="id of building"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    array=false,
     *    description="search keyword"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    default=null,
     *    nullable=true,
     *    array=false,
     *    description="search keyword string"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    default=null,
     *    nullable=true,
     *    array=false,
     *    description="start date start time"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_date_start",
     *    default=null,
     *    nullable=true,
     *    array=false,
     *    description="start date start time"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_date_end",
     *    default=null,
     *    nullable=true,
     *    array=false,
     *    description="start date end time"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort_column",
     *    default=null,
     *    nullable=true,
     *    array=false,
     *    description="sort column"
     * )
     *
     * @Annotations\QueryParam(
     *    name="direction",
     *    default=null,
     *    nullable=true,
     *    array=false,
     *    description="sort direction"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sales_company",
     *    default=null,
     *    nullable=true,
     *    array=false,
     *    description="id of sales company"
     * )
     *
     * @Route("/communities/spaces")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSpacesByCommunityIdAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminCommunityPermissions(AdminPermission::OP_LEVEL_VIEW);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $roomType = $paramFetcher->get('room_types');
        $visible = $paramFetcher->get('visible');
        $noProduct = $paramFetcher->get('no_product');
        $query = $paramFetcher->get('query');
        $building = $paramFetcher->get('building');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $startDate = $paramFetcher->get('start_date');
        $startDateStart = $paramFetcher->get('start_date_start');
        $startDateEnd = $paramFetcher->get('start_date_end');
        $sortColumn = $paramFetcher->get('sort_column');
        $direction = $paramFetcher->get('direction');
        $salesCompanyId = $paramFetcher->get('sales_company');

        $spaces = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->findSpacesByBuilding(
                $salesCompanyId,
                $building,
                $pageLimit,
                $offset,
                $roomType,
                $visible,
                $noProduct,
                $query,
                $keyword,
                $keywordSearch,
                $startDate,
                $startDateStart,
                $startDateEnd
            );

        $spaces = $this->handleSpacesData($spaces);

        if(!is_null($sortColumn) && !is_null($direction)) {
            $sort = [];

            foreach ($spaces as $space) {
                if (!empty($space['product'])) {
                    if($sortColumn == 'price') {
                        $sort[] = $space['product']['leasing_sets'][0]['base_price'];
                    }else{
                        $sort[] = $space['product'][$sortColumn];
                    }
                }else{
                    $sort[] = '';
                }
            }

            if ($direction == 'asc') {
                array_multisort($sort, SORT_ASC, $spaces);
            } elseif ($direction == 'desc') {
                array_multisort($sort, SORT_DESC, $spaces);
            }
        }

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->countSpacesByBuilding(
                $salesCompanyId,
                $building,
                $roomType,
                $visible,
                $noProduct,
                $query,
                $keyword,
                $keywordSearch,
                $startDate,
                $startDateStart,
                $startDateEnd
            );
        
        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $spaces,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @param $company
     * @param $status
     * @param null $visible
     *
     * @return array
     */
    private function getBuildingInfo(
        $company,
        $status,
        $visible = null
    ) {
        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getLocationRoomBuildings(
                null,
                null,
                $company,
                $status,
                $visible
            );

        $result = array();
        foreach ($buildings as $building) {
            $allNumber = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\Room')
                ->countsRoomByBuilding($building);

            $usingNumber = 0;
            if ($visible == true) {
                $usingNumber = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Product\Product')
                    ->countsProductByBuilding($building, $visible);
            }

            $result[] = array(
                'id' => $building->getId(),
                'name' => $building->getName(),
                'using_number' => (int) $usingNumber,
                'all_number' => (int) $allNumber,
            );
        }

        return $result;
    }

    /**
     * @param $opLevel
     */
    private function checkAdminCommunityPermissions(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_BUILDING],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_PREORDER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_RESERVE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ROOM],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_LEASE],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_APPOINTMENT],
            ],
            $opLevel
        );
    }
}
