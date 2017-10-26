<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Room;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Location\LocationController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;

class ClientRoomController extends LocationController
{
    /**
     * Get Room Buildings.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
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
     *    name="query",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Route("/rooms")
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

        $buildingId = $paramFetcher->get('building');
        $query = $paramFetcher->get('query');

        $rooms = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->getProductRoomsForPropertyClient(
                $salesCompanyId,
                $buildingId,
                $query
            );

        $view = new View();
        $view->setData($rooms);

        return $view;
    }
}
