<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Event;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\Event\EventController;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;

class ClientEventHotController extends EventController
{
    /**
     * Get Hot Events Lists
     *
     * @param Request $request
     *
     * @Route("/events/hot")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getHotEventsAction(
        Request $request
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        $ids = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\CommnueEventHot')
            ->getCommnueHotEventsId();
        $hots =  $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\Event')
            ->getCommnueHotEvents($ids);

        foreach ($hots as &$hot) {
            $url = $this->getParameter('mobile_url');
            $id = $hot['id'];
            $hot['url'] = $url.'/'.'event?ptype=detail&id='.$id;
            $hot['registration_counts'] = $this->getRepo('Event\EventRegistration')
                ->getRegistrationCounts($id);
        }
        $view = new View($hots);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_event']));

        return $view;
    }
}