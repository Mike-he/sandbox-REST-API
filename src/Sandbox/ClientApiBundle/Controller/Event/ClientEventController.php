<?php

namespace Sandbox\ClientApiBundle\Controller\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Event\EventController;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Service\ViewCounts;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;

/**
 * Class ClientEventController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class ClientEventController extends EventController
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

        $events = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\Event')
            ->getAllClientEvents(
                null,
                $limit,
                $offset,
                $status,
                $sort
            );

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
     * Get my register client events.
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
     *    description="offset of the page"
     * )
     *
     * @Route("/events/my")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throw \Exception
     */
    public function getMyClientEventsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        // filters
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

        $events = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\Event')
            ->getMyClientEvents(
                $userId,
                $limit,
                $offset
            );

        $eventsArray = array();

        foreach ($events as $event) {
            try {
                // set event extra
                $event = $this->setEventExtra($event, $userId);

                array_push($eventsArray, $event);
            } catch (\Exception $e) {
                continue;
            }
        }

        $view = new View($eventsArray);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_event']));

        return $view;
    }

    /**
     * Get definite id of event.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/events/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getClientEventAction(
        Request $request,
        $id
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        // get an event
        $event = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\Event')
            ->findOneBy(array(
                'id' => $id,
                'isDeleted' => false,
                'visible' => true,
            ));
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        // set extra
        $event = $this->setEventExtra($event, $userId);

        $this->get('sandbox_api.view_count')->autoCounting(
            ViewCounts::OBJECT_EVENT,
            $id,
            ViewCounts::TYPE_VIEW
        );

        $this->get('sandbox_api.view_count')->autoCounting(
            ViewCounts::OBJECT_EVENT,
            $id,
            ViewCounts::TYPE_VIEW
        );

        // set view
        $view = new View($event);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('client_event'))
        );

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
