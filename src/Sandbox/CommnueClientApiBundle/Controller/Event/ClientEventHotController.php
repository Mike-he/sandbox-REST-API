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

        $hots = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\CommnueEventHot')
            ->findAll();
        $events = [];
        foreach ($hots as $hot){
            $eventId = $hot->getEventId();
            $event = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Event\Event')
                ->find($eventId);
            try {
               $events[] = $this->setEventExtra($event, $userId);
            } catch (\Exception $e) {
                continue;
            }
        }

        $view = new View($events);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_event']));

        return $view;
    }

    /**
     * @param Event $event
     * @param int   $userId
     *
     * @return Event
     */
    private function setEventExtra(
        $event,
        $userId = null
    ) {
        $eventId = $event->getId();

        // get attachment, dates, forms, registrationCounts, likesCount, commentsCount
        $attachments = $this->getRepo('Event\EventAttachment')->findByEvent($event);
        $dates = $this->getRepo('Event\EventDate')->findByEvent($event);
        $forms = $this->getRepo('Event\EventForm')->findByEvent($event);
        $registrationCounts = $this->getRepo('Event\EventRegistration')
            ->getRegistrationCounts($eventId);
        $likesCount = $this->getRepo('Event\EventLike')->getLikesCount($eventId);
        $commentsCount = $this->getRepo('Event\EventComment')->getCommentsCount($eventId);

        // set attachment, dates, forms, registrationCounts
        $event->setAttachments($attachments);
        $event->setDates($dates);
        $event->setForms($forms);
        $event->setRegisteredPersonNumber((int) $registrationCounts);
        $event->setLikesCount((int) $likesCount);
        $event->setCommentsCount((int) $commentsCount);

        // set accepted person number
        if ($event->isVerify()) {
            $acceptedCounts = $this->getRepo('Event\EventRegistration')
                ->getAcceptedPersonNumber($eventId);
            $event->setAcceptedPersonNumber((int) $acceptedCounts);
        }

        // set my registration status
        if (!is_null($userId)) {
            // check if user is registered
            $registration = $this->getRepo('Event\EventRegistration')->findOneBy(array(
                'eventId' => $eventId,
                'userId' => $userId,
            ));

            if (!is_null($registration)) {
                // set registration
                $event->setEventRegistration($registration);
            }

            // check my like if
            $like = $this->getRepo('Event\EventLike')->findOneBy(array(
                'eventId' => $event->getId(),
                'authorId' => $userId,
            ));

            if (!is_null($like)) {
                $event->setMyLikeId($like->getId());
            }
        }

        return $event;
    }
}