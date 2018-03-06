<?php

namespace Sandbox\SalesApiBundle\Controller\Admin;

use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

/**
 * AdminLogo controller.
 */
class AdminLogoController extends SalesRestController
{
    /**
     * @param Request $request
     *
     * @Route("/logo")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesAdminLogoAction(
        Request $request
    ) {
        $salesCompnayId = $this->getSalesCompanyId();
        $logo = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getFirstBuildingLogo($salesCompnayId);

        if (!$logo) {
            $logo = 'https://testimage.sandbox3.cn/saleadmin/default/1.png';
        }
        return new View($logo);
    }
}