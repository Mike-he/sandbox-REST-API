<?php

namespace Sandbox\SalesApiBundle\Controller\Space;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * get buildings menu.
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
        $adminPlatform = $this->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $companyId = $adminPlatform['sales_company_id'];

        if ($platform != AdminPermission::PERMISSION_PLATFORM_SALES) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
        $this->throwNotFoundIfNull($companyId, self::NOT_FOUND_MESSAGE);

        $using = $this->getbuildingInfo($companyId, RoomBuilding::STATUS_ACCEPT, true);
        $invisible = $this->getbuildingInfo($companyId, RoomBuilding::STATUS_ACCEPT, false);
        $banned = $this->getbuildingInfo($companyId, RoomBuilding::STATUS_BANNED);
        $pending = $this->getbuildingInfo($companyId, RoomBuilding::STATUS_PENDING);

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
        $adminPlatform = $this->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $companyId = $adminPlatform['sales_company_id'];

        if ($platform != AdminPermission::PERMISSION_PLATFORM_SALES) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $building = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($id);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        if ($building->getCompanyId() != $companyId) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

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
                ->getRepository('SandboxApiBundle:Product\Product')
                ->countsProductByType(
                    $id,
                    $roomType->getName()
                );

            if ($all_number > 0) {
                $result[] = array(
                    'id' => $roomType->getId(),
                    'name' => $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$roomType->getName()),
                    'icon' => $roomType->getIcon(),
                    'using_number' => (int) $using_number,
                    'all_number' => (int) $all_number,
                );
            }
        }

        return new View($result);
    }

    /**
     * @param $company
     * @param $status
     * @param null $visible
     *
     * @return array
     */
    private function getbuildingInfo(
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
                ->getRepository('SandboxApiBundle:Product\Product')
                ->countsProductByBuilding($building);

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
}
