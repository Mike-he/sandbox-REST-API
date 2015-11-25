<?php

namespace Sandbox\ClientApiBundle\Controller\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Event\EventController;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventRegistration;
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
 * @link     http://www.Sandbox.cn/
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
        $userId = $this->getUserId();

        // filters
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

        $events = $this->getRepo('Event\Event')->getAllClientEvents(
            $limit,
            $offset
        );
        foreach ($events as $event) {
            $eventId = $event->getId();
            $attachments = $this->getRepo('Event\EventAttachment')->findByEvent($event);
            $dates = $this->getRepo('Event\EventDate')->findByEvent($event);
            $forms = $this->getRepo('Event\EventForm')->findByEvent($event);
            $registrationCounts = $this->getRepo('Event\EventRegistration')
                ->getRegistrationCounts($eventId);

            // check if user is registered
            $registration = $this->getRepo('Event\EventRegistration')->findOneBy(array(
                'eventId' => $eventId,
                'userId' => $userId,
            ));

            if (!is_null($registration)) {
                // set registered true
                $event->setIsRegistered(true);

                // set status accepted
                if ($registration->getStatus() == EventRegistration::STATUS_ACCEPTED) {
                    $event->setIsAccepted(true);
                }
            }

            $event->setAttachments($attachments);
            $event->setDates($dates);
            $event->setForms($forms);
            $event->setRegisteredPersonNumber((int) $registrationCounts);

            // set accepted person number
            if ($event->getVerify()) {
                $acceptedCounts = $this->getRepo('Event\EventRegistration')
                    ->getAcceptedPersonNumber($eventId);
                $event->setAcceptedPersonNumber((int) $acceptedCounts);
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

        $eventsArray = array();
        $events = $this->getRepo('Event\Event')->getMyClientEvents(
            $userId,
            $limit,
            $offset
        );
        foreach ($events as $event) {
            $eventId = $event->getId();
            $attachments = $this->getRepo('Event\EventAttachment')->findByEvent($event);
            $dates = $this->getRepo('Event\EventDate')->findByEvent($event);
            $forms = $this->getRepo('Event\EventForm')->findByEvent($event);
            $registrationCounts = $this->getRepo('Event\EventRegistration')
                ->getRegistrationCounts($eventId);

            // check if user is registered
            $registration = $this->getRepo('Event\EventRegistration')->findOneBy(array(
                'eventId' => $eventId,
                'userId' => $userId,
                'status' => EventRegistration::STATUS_ACCEPTED,
            ));

            if (!is_null($registration)) {
                $event->setIsAccepted(true);
            }

            $event->setIsRegistered(true);
            $event->setAttachments($attachments);
            $event->setDates($dates);
            $event->setForms($forms);
            $event->setRegisteredPersonNumber((int) $registrationCounts);

            // set accepted person number
            if ($event->getVerify()) {
                $acceptedCounts = $this->getRepo('Event\EventRegistration')
                    ->getAcceptedPersonNumber($eventId);
                $event->setAcceptedPersonNumber((int) $acceptedCounts);
            }

            array_push($eventsArray, $event);
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
        $userId = $this->getUserId();

        // get an event
        $event = $this->getRepo('Event\Event')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        $eventId = $event->getId();

        // check if user is registered
        $registration = $this->getRepo('Event\EventRegistration')->findOneBy(array(
            'eventId' => $eventId,
            'userId' => $userId,
        ));

        if (!is_null($registration)) {
            // set registered true
            $event->setIsRegistered(true);

            // set status accepted
            if ($registration->getStatus() == EventRegistration::STATUS_ACCEPTED) {
                $event->setIsAccepted(true);
            }
        }

        // set other array
        $attachments = $this->getRepo('Event\EventAttachment')->findByEvent($event);
        $dates = $this->getRepo('Event\EventDate')->findByEvent($event);
        $forms = $this->getRepo('Event\EventForm')->findByEvent($event);
        $registrationCounts = $this->getRepo('Event\EventRegistration')
            ->getRegistrationCounts($eventId);

        $event->setAttachments($attachments);
        $event->setDates($dates);
        $event->setForms($forms);
        $event->setRegisteredPersonNumber((int) $registrationCounts);

        // set accepted person number
        if ($event->getVerify()) {
            $acceptedCounts = $this->getRepo('Event\EventRegistration')
                ->getAcceptedPersonNumber($eventId);
            $event->setAcceptedPersonNumber((int) $acceptedCounts);
        }

        // set view
        $view = new View($event);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('client_event'))
        );

        return $view;
    }
}
