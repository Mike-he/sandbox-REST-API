<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Event;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Event\CommnueEventHot;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

class AdminEventHotController extends SandboxRestController
{
    const ERROR_NOT_ALLOWED_ADD_CODE = 400001;
    const ERROR_NOT_ALLOWED_ADD_MESSAGE = 'More than the allowed number of hits';

    /**
     * @param Request $request
     *
     * @Method({"GET"})
     * @Route("/events/hots")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getEventHotsAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminEventPermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        $count = $em->getRepository('SandboxApiBundle:Event\CommnueEventHot')->countHots();

        $parameter = $em->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array('key' => Parameter::KEY_COMMNUE_EVENT_HOT));

        $allowNumber = $parameter ? (int) $parameter->getValue() : 3;

        $result = [
            'max_allow_number' => $allowNumber,
            'count' => $count,
        ];

        return new View($result);
    }

    /**
     * @param Request $request
     *
     * @Method({"POST"})
     * @Route("/events/{id}/hots")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postEventHotsAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminEventPermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        $count = $em->getRepository('SandboxApiBundle:Event\CommnueEventHot')->countHots();

        $parameter = $em->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array('key' => Parameter::KEY_COMMNUE_EVENT_HOT));

        $allowNumber = $parameter ? (int) $parameter->getValue() : 3;

        if ($count >= $allowNumber) {
            return $this->customErrorView(
                400,
                self::ERROR_NOT_ALLOWED_ADD_CODE,
                self::ERROR_NOT_ALLOWED_ADD_MESSAGE
            );
        }

        $hot = new CommnueEventHot();
        $hot->setEventId($id);
        $em->persist($hot);

        $em->flush();

        return new View(null, 201);
    }

    /**
     * Delete a event hot.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/events/{id}/hots")
     * @Method({"DELETE"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteEventAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminEventPermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        $hot = $em->getRepository('SandboxApiBundle:Event\CommnueEventHot')
            ->findOneBy(array('eventId' => $id));

        if ($hot) {
            $em->remove($hot);
            $em->flush();
        }

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
                ['key' => AdminPermission::KEY_COMMNUE_PLATFORM_EVENT],
            ],
            $opLevel
        );
    }
}
