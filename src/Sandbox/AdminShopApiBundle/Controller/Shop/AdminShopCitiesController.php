<?php

namespace Sandbox\AdminShopApiBundle\Controller\Shop;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\AdminShopApiBundle\Controller\ShopRestController;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermission;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermissionMap;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class AdminShopCitiesController extends ShopRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/cities")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getShopCitiesAllAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminCitiesPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_PLATFORM_SHOP,
            )
        );

        // get my company
        $myCompany = $this->getUser()->getMyAdmin()->getSalesCompany();

        // get my buildings
        $buildings = $this->getRepo('Room\RoomBuilding')->findBy(array(
            'companyId' => $myCompany->getId(),
            'isDeleted' => false,
            'status' => RoomBuilding::STATUS_ACCEPT,
            'visible' => true,
        ));

        // get my cities
        $cities = $this->getRepo('Room\RoomCity')->getSalesRoomCityByBuilding($buildings);

        return new View($cities);
    }

    /**
     * @param $opLevel
     * @param $permissions
     * @param $shopId
     */
    private function checkAdminCitiesPermission(
        $opLevel,
        $permissions,
        $shopId = null
    ) {
        $this->throwAccessDeniedIfShopAdminNotAllowed(
            $this->getAdminId(),
            ShopAdminType::KEY_PLATFORM,
            $permissions,
            $opLevel,
            $shopId
        );
    }
}
