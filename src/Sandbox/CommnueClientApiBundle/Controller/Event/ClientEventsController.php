<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Event\EventController;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;

class ClientEventsController extends EventController
{
    /**
     * Get all client events.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="offset of page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="event status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="sort string"
     * )
     *
     * @Route("/events/all")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throw \Exception
     */
    public function getAllClientEventsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        $status = $paramFetcher->get('status');
        $sort = $paramFetcher->get('sort');

        // filters
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

        if ((is_null($status) || empty($status)) && (is_null($sort) || empty($sort))) {
            $events = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Event\Event')
                ->getAllClientEvents(
                    null,
                    null,
                    null,
                    Event::STATUS_REGISTERING,
                    null,
                    null,
                    Event::PLATFORM_COMMNUE
                );

            $elseEvents = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Event\Event')
                ->getAllClientEvents(
                    null,
                    null,
                    null,
                    null,
                    Event::STATUS_REGISTERING,
                    null,
                    Event::PLATFORM_COMMNUE
                );

            $eventsAll = array_merge($events, $elseEvents);
            $counts = count($eventsAll);
            $events = [];
            for ($i = $offset; $i < $offset + $limit && $i < $counts; $i++) {
                array_push($events, $eventsAll[$i]);
            }
        } else {
            $events = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Event\Event')
                ->getAllClientEvents(
                    null,
                    $limit,
                    $offset,
                    $status,
                    null,
                    $sort,
                    Event::PLATFORM_COMMNUE
                );
        }

        foreach ($events as $event) {
            try {
                $this->setEventExtra($event, $userId);
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

        $attachments = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventAttachment')
            ->findByEvent($event);
        $event->setAttachments($attachments);

        $dates = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventDate')
            ->findByEvent($event);
        $event->setDates($dates);

        $forms = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventForm')
            ->findByEvent($event);
        $event->setForms($forms);

        $registrationCounts = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventRegistration')
            ->getRegistrationCounts($eventId);
        $event->setRegisteredPersonNumber((int) $registrationCounts);

        $likesCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventLike')
            ->getLikesCount($eventId);
        $event->setLikesCount((int) $likesCount);

        $commentsCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventComment')
            ->getCommentsCount($eventId);
        $event->setCommentsCount((int) $commentsCount);

        // set accepted person number
        if ($event->isVerify()) {
            $acceptedCounts = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Event\EventRegistration')
                ->getAcceptedPersonNumber($eventId);
            $event->setAcceptedPersonNumber((int) $acceptedCounts);
        }

        // set my registration status
        if (!is_null($userId)) {
            // check if user is registered
            $registration = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Event\EventRegistration')
                ->findOneBy(array(
                    'eventId' => $eventId,
                    'userId' => $userId,
                ));

            if (!is_null($registration)) {
                // set registration
                $event->setEventRegistration($registration);
            }

            // check my like if
            $like = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Event\EventLike')
                ->findOneBy(array(
                    'eventId' => $event->getId(),
                    'authorId' => $userId,
                ));

            if (!is_null($like)) {
                $event->setMyLikeId($like->getId());
            }

            $favorite = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserFavorite')
                ->findOneBy(array(
                    'userId' => $userId,
                    'object' => UserFavorite::OBJECT_EVENT,
                    'objectId' => $eventId,
                ));

            $userFavorite = $favorite ? true : false;
            $event->setFavorite($userFavorite);
        }

        return $event;
    }
}