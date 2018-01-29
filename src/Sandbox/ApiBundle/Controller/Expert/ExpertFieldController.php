<?php

namespace Sandbox\ApiBundle\Controller\Expert;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

class ExpertFieldController extends SandboxRestController
{
    /**
     * @param Request $request
     *
     * @Route("/experts/fields")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getRoomTypesAction(
        Request $request
    ) {
        $fields = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\ExpertField')
            ->getFields();

        $view = new View($fields);

        return $view;
    }
}
