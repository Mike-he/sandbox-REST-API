<?php

namespace Sandbox\SalesApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

/**
 * AdminLogo controller.
 */
class AdminLogoController extends SandboxRestController
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
        $logo = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getFirstBuildingLogo();

        return new View($logo);
    }
}