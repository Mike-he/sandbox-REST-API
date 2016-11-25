<?php

namespace Sandbox\SalesApiBundle\Controller\Space;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AdminCommunityController.
 */
class AdminCommunityController extends SalesRestController
{
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

        $adminPlatform = $this->getAdminPlatform();
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
            )
        );

        $using = $this->getBuildingInfo($companyId, $buildingIds, RoomBuilding::STATUS_ACCEPT, true);
        $invisible = $this->getBuildingInfo($companyId, $buildingIds, RoomBuilding::STATUS_ACCEPT, false);
        $banned = $this->getBuildingInfo($companyId, $buildingIds, RoomBuilding::STATUS_BANNED);
        $pending = $this->getBuildingInfo($companyId, $buildingIds, RoomBuilding::STATUS_PENDING);

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

        $building = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($id);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        $roomTypes = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomTypes')->findAll();

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
                    'icon' => $roomType->getIcon(),
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
     *    name="has_product",
     *    default=true,
     *    nullable=true,
     *    array=false,
     *    description="has product or not"
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
     * @Route("/communities/{id}/spaces")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSpacesByCommunityIdAction(
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkAdminCommunityPermissions(AdminPermission::OP_LEVEL_VIEW);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $roomType = $paramFetcher->get('room_types');
        $hasProduct = $paramFetcher->get('has_product');
        $visible = $paramFetcher->get('visible');
        $query = $paramFetcher->get('query');

        $spaces = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->findSpacesByBuilding(
                $id,
                $pageLimit,
                $offset,
                $roomType,
                $hasProduct,
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

    private function handleSpacesData(
        $spaces
    ) {
        $limit = 1;
        foreach ($spaces as &$space) {
            $attachment = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
                ->findAttachmentsByRoom($space['id'], $limit);

            if (!empty($attachment)) {
                $space['content'] = $attachment[0]['content'];
                $space['preview'] = $attachment[0]['preview'];
            }

            $space['product'] = [];
            if ($space['is_deleted'] == false) {
                if ($space['type'] == 'fixed') {
                    $seats = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Room\RoomFixed')
                        ->findBy(array(
                            'room' => $space['id'],
                        ));

                    $space['product']['seats'] = $seats;
                } else {
                    $space['product']['base_price'] = $space['base_price'];
                }

                $space['product']['id'] = $space['product_id'];
                $space['product']['unit_price'] = $space['unit_price'];
                $space['product']['start_date'] = $space['start_date'];
                $space['product']['recommend'] = $space['recommend'];
                $space['product']['visible'] = $space['visible'];
            }

            unset($space['product_id']);
            unset($space['base_price']);
            unset($space['unit_price']);
            unset($space['start_date']);
            unset($space['visible']);
            unset($space['recommend']);
            unset($space['is_deleted']);
        }

        return $spaces;
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_BUILDING_SPACE],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING],
                ['key' => AdminPermission::KEY_SALES_BUILDING_BUILDING],
                ['key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER],
                ['key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE],
                ['key' => AdminPermission::KEY_SALES_BUILDING_ROOM],
                ['key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT],
            ],
            $opLevel
        );
    }
}
