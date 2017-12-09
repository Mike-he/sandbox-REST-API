<?php

namespace Sandbox\ClientApiBundle\Controller\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Event\EventController;
use Sandbox\ApiBundle\Entity\Event\Event;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
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
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
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
     *     name="platform",
     *     array=false,
     *     default="official",
     *     nullable=true,
     *     strict=true
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

        // filters
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $platform = $paramFetcher->get('platform');

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

        $events = $this->getRepo('Event\Event')->getAllClientEvents(
            $limit,
            $offset,
            $platform
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
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
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
     * @Annotations\QueryParam(
     *     name="platform",
     *     array=false,
     *     default="official",
     *     nullable=true,
     *     strict=true
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
        $platform = $paramFetcher->get('platform');

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

        $events = $this->getRepo('Event\Event')->getMyClientEvents(
            $userId,
            $limit,
            $offset,
            $platform
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
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
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
        $event = $this->getRepo('Event\Event')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
            'visible' => true,
        ));
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        // set extra
        $event = $this->setEventExtra($event, $userId);

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
