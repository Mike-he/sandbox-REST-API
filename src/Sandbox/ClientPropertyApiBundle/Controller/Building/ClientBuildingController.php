<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Building;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Location\LocationController;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

class ClientBuildingController extends LocationController
{
    /**
     * Get Room Buildings.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/buildings")
     * @Method({"GET"})
     *
     * @return View
     */
    public function findAdminBuildingsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getLocationRoomBuildings(
                null,
                null,
                $salesCompanyId,
                RoomBuilding::STATUS_ACCEPT,
                true
            );

        $result = array();
        foreach ($buildings as $building) {
            /* @var RoomBuilding $building */
            $result[] = array(

                'id' => $building->getId(),
                'name' => $building->getName(),
                'address' => $building->getAddress(),
            );
        }

        $view = new View();
        $view->setData($result);

        return $view;
    }
}
