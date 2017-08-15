<?php

namespace Sandbox\SalesApiBundle\Controller\Space;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Traits\HandleSpacesDataTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class AdminCommunityController.
 */
class AdminCommunityController extends SalesRestController
{
    use HandleSpacesDataTrait;

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/administrative_region")
     * @Method({"GET"})
     *
     * @Annotations\QueryParam(
     *    name="parent",
     *    default=null,
     *    nullable=false,
     *    description="parent id"
     * )
     *
     * @return View
     */
    public function getAdministrativeRegionsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $parentId = $paramFetcher->get('parent');

        $regions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findBy(array(
                'parentId' => $parentId,
            ));

        $response = array();
        foreach ($regions as $region) {
            array_push($response, array(
                'id' => $region->getId(),
                'name' => $region->getName(),
            ));
        }

        return new View($response);
    }

    /**
     * Get Communities.
     *
     * @param Request $request the request object
     *
     * @Method({"GET"})
     * @Route("/communities")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getCommunitiesAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminCommunityPermissions(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        $this->throwNotFoundIfNull($companyId, self::NOT_FOUND_MESSAGE);

        // get my buildings list
        $buildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_SPACE,
                AdminPermission::KEY_SALES_PLATFORM_BUILDING,
                AdminPermission::KEY_SALES_BUILDING_BUILDING,
                AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
                AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
                AdminPermission::KEY_SALES_BUILDING_ROOM,
                AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
                AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT,
                AdminPermission::KEY_SALES_BUILDING_ORDER,
            )
        );
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];

        if ($platform != AdminPermission::PERMISSION_PLATFORM_SALES) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $salesCompanyId = $adminPlatform['sales_company_id'];
        $salesCompany = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($salesCompanyId);

        $salesInfos = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->findBy(array(
                'company' => $salesCompany,
                'status' => true,
            ));

        $typeKeys = array();
        foreach ($salesInfos as $info) {
            array_push($typeKeys, $info->getTradeTypes());
        }

        $using = $this->getBuildingInfo($companyId, $buildingIds, RoomBuilding::STATUS_ACCEPT, $typeKeys, true);
        $invisible = $this->getBuildingInfo($companyId, $buildingIds, RoomBuilding::STATUS_ACCEPT, $typeKeys, false);
        $banned = $this->getBuildingInfo($companyId, $buildingIds, RoomBuilding::STATUS_BANNED, $typeKeys);
        $pending = $this->getBuildingInfo($companyId, $buildingIds, RoomBuilding::STATUS_PENDING, $typeKeys);

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
        // check user permission
        $this->checkAdminCommunityPermissions(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $building = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($id);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        $UsedRoomTypes = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->getCompanyService($salesCompanyId);

        $imageUrl = $this->getParameter('image_url');

        $result = array();
        foreach ($UsedRoomTypes as $usedRoomType) {
            $roomType = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomTypes')
                ->findOneBy(array('name' => $usedRoomType->getTradeTypes()));

            if (is_null($roomType)) {
                continue;
            }

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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
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

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $roomType = $paramFetcher->get('room_types');
        $visible = $paramFetcher->get('visible');
        $query = $paramFetcher->get('query');
        $building = $paramFetcher->get('building');

        // get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                AdminPermission::KEY_SALES_BUILDING_SPACE,
                AdminPermission::KEY_SALES_BUILDING_BUILDING,
                AdminPermission::KEY_SALES_BUILDING_ROOM,
            )
        );

        if (!is_null($building) && !in_array($building, $myBuildingIds)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $spaces = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->findSpacesByBuilding(
                $salesCompanyId,
                $building,
                $pageLimit,
                $offset,
                $roomType,
                $visible,
                $query
            );

        $spaces = $this->handleSpacesData($spaces);

        $view = new View($spaces);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['admin_spaces'])
        );

        return $view;
    }

    /**
     * @param $company
     * @param $buildingIds
     * @param $status
     * @param null $visible
     *
     * @return array
     */
    private function getBuildingInfo(
        $company,
        $buildingIds,
        $status,
        $typeKeys,
        $visible = null
    ) {
        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getLocationRoomBuildings(
                null,
                $buildingIds,
                $company,
                $status,
                $visible
            );

        $result = array();
        foreach ($buildings as $building) {
            $allNumber = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\Room')
                ->countsRoomByBuilding($building, $typeKeys);

            $usingNumber = 0;
            if ($visible == true) {
                $usingNumber = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Product\Product')
                    ->countsProductByBuilding($building, $visible, $typeKeys);
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
                ['key' => AdminPermission::KEY_SALES_BUILDING_SPACE],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING],
                ['key' => AdminPermission::KEY_SALES_BUILDING_BUILDING],
                ['key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER],
                ['key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE],
                ['key' => AdminPermission::KEY_SALES_BUILDING_ROOM],
                ['key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT],
                ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE],
                ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT],
                ['key' => AdminPermission::KEY_SALES_BUILDING_ORDER],
            ],
            $opLevel
        );
    }
}
