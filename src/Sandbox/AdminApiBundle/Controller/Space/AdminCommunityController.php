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

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $roomType = $paramFetcher->get('room_types');
        $visible = $paramFetcher->get('visible');
        $query = $paramFetcher->get('query');
        $building = $paramFetcher->get('building');

        $spaces = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->findSpacesByBuilding(
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
        $this->throwAccessDeniedIfAdminNotAllowed(
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
