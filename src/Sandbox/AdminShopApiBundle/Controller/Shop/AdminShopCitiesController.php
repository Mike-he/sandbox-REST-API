<?php

namespace Sandbox\AdminShopApiBundle\Controller\Shop;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\AdminShopApiBundle\Controller\ShopRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_SHOP,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        // get my company
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $myCompany = $adminPlatform['sales_company_id'];

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
}
