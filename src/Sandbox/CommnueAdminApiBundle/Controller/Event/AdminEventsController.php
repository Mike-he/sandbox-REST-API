<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Event;

use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Event\CommnueEventHot;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sandbox\ApiBundle\Form\Event\CommnueEventPatchType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

class AdminEventsController extends SandboxRestController
{
    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/commnue/events/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchEventsAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminEventPermission(AdminPermission::OP_LEVEL_EDIT);

        $events = $this->getDoctrine()->getRepository('SandboxApiBundle:Event\Event')
            ->find($id);

        $this->throwNotFoundIfNull($events, self::NOT_FOUND_MESSAGE);

        $eventsJson = $this->container->get('serializer')->serialize($events, 'json');
        $patch = new Patch($eventsJson, $request->getContent());
        $eventsJson = $patch->apply();

        $form = $this->createForm(new CommnueEventPatchType(), $events);
        $form->submit(json_decode($eventsJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminEventPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_EVENT],
                ['key' => AdminPermission::KEY_COMMNUE_PLATFORM_EVENT],
            ],
            $opLevel
        );
    }
}