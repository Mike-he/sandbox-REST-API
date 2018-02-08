<?php

namespace Sandbox\AdminApiBundle\Controller\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\PlatformConstants;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventAttachment;
use Sandbox\ApiBundle\Entity\Event\EventDate;
use Sandbox\ApiBundle\Entity\Event\EventForm;
use Sandbox\ApiBundle\Entity\Event\EventFormOption;
use Sandbox\ApiBundle\Entity\Event\EventTime;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sandbox\ApiBundle\Entity\Service\ViewCounts;
use Sandbox\ApiBundle\Form\Event\EventPatchType;
use Sandbox\ApiBundle\Form\Event\EventPostType;
use Sandbox\ApiBundle\Form\Event\EventPutType;
use Sandbox\ApiBundle\Traits\SetStatusTrait;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\Form;

/**
 * Class AdminEventController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class AdminEventController extends SandboxRestController
{
    use SetStatusTrait;

    const ERROR_NOT_ALLOWED_MODIFY_CODE = 400001;
    const ERROR_NOT_ALLOWED_MODIFY_MESSAGE = 'Not allowed to modify - 不允许被修改';
    const ERROR_NOT_ALLOWED_DELETE_CODE = 400002;
    const ERROR_NOT_ALLOWED_DELETE_MESSAGE = 'Not allowed to delete - 不允许被删除';
    const ERROR_INVALID_LIMIT_NUMBER_CODE = 400003;
    const ERROR_INVALID_LIMIT_NUMBER_MESSAGE = 'Invalid limit number';
    const ERROR_INVALID_REGISTRATION_DATE_CODE = 400004;
    const ERROR_INVALID_REGISTRATION_DATE_MESSAGE = 'Registration start date should before registration end date';
    const ERROR_INVALID_EVENT_START_DATE_COED = 400005;
    const ERROR_INVALID_EVENT_START_DATE_MESSAGE = 'Registration end date should before event start date';
    const ERROR_INVALID_EVENT_TIME_CODE = 400006;
    const ERROR_INVALID_EVENT_TIME_MESSAGE = 'Event start time should before event end time';
    const ERROR_INVALID_EVENT_PRICE_CODE = 400007;
    const ERROR_INVALID_EVENT_PRICE_MESSAGE = 'Event price can not be null';

    const ERROR_ROOM_INVALID = 'Invalid room';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/event/starttime/sync")
     * @Method({"POST"})
     *
     * @return view
     */
    public function syncEventStartTimeAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $em = $this->getDoctrine()->getManager();

        $events = $this->getRepo('Event\Event')->findAll();

        foreach ($events as $event) {
            $repository = $this->getRepo('Event\EventTime');
            $times = $repository->createQueryBuilder('et')
                ->select('min(et.startTime)')
                ->leftJoin('SandboxApiBundle:Event\EventDate', 'ed', 'WITH', 'ed.id = et.dateId')
                ->leftJoin('SandboxApiBundle:Event\Event', 'e', 'WITH', 'e.id = :eventId')
                ->where('e.id = ed.eventId')
                ->setParameter('eventId', $event->getId());

            $result = $times->getQuery()->getSingleScalarResult();

            $event->setEventStartDate(new \DateTime($result));
        }

        $em->flush();

        return new View();
    }

    /**
     * Get Events.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="event status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="visible",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="event visible"
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
     * @Annotations\QueryParam(
     *     name="query",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="verify",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="charge",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="method",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="sort_column",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="direction",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="keyword",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="keyword_search",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="commnue_visible",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="commnue visible"
     * )
     *
     * @Route("/events")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getEventsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_EVENT],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_BANNER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADVERTISING],
                ['key' => AdminPermission::KEY_COMMNUE_PLATFORM_EVENT],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $status = $paramFetcher->get('status');
        $visible = $paramFetcher->get('visible');
        $search = $paramFetcher->get('query');
        $verify = $paramFetcher->get('verify');
        $verify = !is_null($verify) ? (bool) $verify : $verify;
        $charge = $paramFetcher->get('charge');
        $charge = !is_null($charge) ? (bool) $charge : $charge;
        $method = $paramFetcher->get('method');
        $sortColumn = $paramFetcher->get('sort_column');
        $direction = $paramFetcher->get('direction');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $commnueVisible = $paramFetcher->get('commnue_visible');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $events = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\Event')
            ->getEvents(
                $status,
                $visible,
                $limit,
                $offset,
                $platform,
                $search,
                $verify,
                $charge,
                $method,
                $commnueVisible,
                $keyword,
                $keywordSearch,
                $sortColumn,
                $direction
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\Event')
            ->countEvents(
                $status,
                $visible,
                $platform,
                $search,
                $verify,
                $charge,
                $method,
                $commnueVisible,
                $keyword,
                $keywordSearch
            );

        $eventsArray = array();
        foreach ($events as $value) {
            /** @var Event $event */
            $event = $value['event'];
            $eventId = $event->getId();

            $attachments = $this->getRepo('Event\EventAttachment')->findByEvent($event);
            $dates = $this->getRepo('Event\EventDate')->findByEvent($event);
            $forms = $this->getRepo('Event\EventForm')->findByEvent($event);
            $registrationCounts = $this->getRepo('Event\EventRegistration')
                ->getRegistrationCounts($event->getId());

            // set sales company
            if (!is_null($event->getSalesCompanyId())) {
                $salesCompany = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
                    ->find($event->getSalesCompanyId());
                $event->setSalesCompany($salesCompany);
            }

            $event->setAttachments($attachments);
            $event->setDates($dates);
            $event->setForms($forms);
            $event->setRegisteredPersonNumber((int) $registrationCounts);

            $commnueHot = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Event\CommnueEventHot')
                ->findOneBy(array('eventId' => $eventId));

            $event->setCommnueHot($commnueHot ? true : false);

            $commentsCount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Event\EventComment')
                ->getCommentsCount($eventId);
            $event->setCommentsCount((int) $commentsCount);

            if($event->getSalesCompanyId()) {
                $event->setPlatform('sales');
            }

            array_push($eventsArray, $event);
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $eventsArray,
                'total_count' => (int) $count,
            )
        );

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
    public function getEventAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_EVENT],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_BANNER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADVERTISING],
                ['key' => AdminPermission::KEY_COMMNUE_PLATFORM_EVENT],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];

        // get an event
        $event = $this->getRepo('Event\Event')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        // set other array
        $attachments = $this->getRepo('Event\EventAttachment')->findByEvent($event);
        $dates = $this->getRepo('Event\EventDate')->findByEvent($event);
        $forms = $this->getRepo('Event\EventForm')->findByEvent($event);
        $registrationCounts = $this->getRepo('Event\EventRegistration')
            ->getRegistrationCounts($event->getId());
        $commentsCount = $this->getRepo('Event\EventComment')->getCommentsCount($event->getId());

        $event->setAttachments($attachments);
        $event->setDates($dates);
        $event->setForms($forms);
        $event->setRegisteredPersonNumber((int) $registrationCounts);
        $event->setCommentsCount((int) $commentsCount);

        if($event->getSalesCompanyId()) {
            $event->setPlatform('sales');
        }

        if($platform == PlatformConstants::PLATFORM_COMMNUE) {
            $eventHot = $this->getDoctrine()->getRepository('SandboxApiBundle:Event\CommnueEventHot')
                ->findOneBy([
                    'eventId' => $event->getId()
                ]);

            if(!is_null($eventHot)) {
                $event->setCommnueHot(true);
            }
        }

        // set view
        $view = new View($event);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('admin_event'))
        );

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Method({"POST"})
     * @Route("/events")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postEventsAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminEventPermission(AdminPermission::OP_LEVEL_EDIT);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];

        $event = new Event();

        $form = $this->createForm(new EventPostType(), $event);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $requestContent = json_decode($request->getContent(), true);

        // set default submit value
        $submit = $requestContent['submit'];
        if (is_null($submit)) {
            $submit = true;
        }
        $event->setPlatform($platform);

        return $this->handleEventPost(
            $event,
            $submit
        );
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Method({"PUT"})
     * @Route("/events/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putEventAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminEventPermission(AdminPermission::OP_LEVEL_EDIT);

        $event = $this->getRepo('Event\Event')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        // bind form
        $form = $this->createForm(
            new EventPutType(),
            $event,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $requestContent = json_decode($request->getContent(), true);

        // set default submit value
        $submit = $requestContent['submit'];
        if (is_null($submit)) {
            $submit = true;
        }

        // check charge valid
        if ($event->isCharge()) {
            if (is_null($event->getPrice())) {
                return $this->customErrorView(
                    400,
                    self::ERROR_INVALID_EVENT_PRICE_CODE,
                    self::ERROR_INVALID_EVENT_PRICE_MESSAGE
                );
            }
        } else {
            $event->setPrice(null);
        }

        // handle event form
        return $this->handleEventPut(
            $event,
            $submit
        );
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/events/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchEventAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminEventPermission(AdminPermission::OP_LEVEL_EDIT);

        $event = $this->getRepo('Event\Event')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        // bind data
        $eventJson = $this->container->get('serializer')->serialize($event, 'json');
        $patch = new Patch($eventJson, $request->getContent());
        $eventJson = $patch->apply();

        $form = $this->createForm(new EventPatchType(), $event);
        $form->submit(json_decode($eventJson, true));

        // change save status
        if ($event->isVisible()) {
            $event->setIsSaved(false);
            $this->setEventStatus($event);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Delete a event.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/events/{id}")
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

        $event = $this->getRepo('Event\Event')->find($id);

        // check if is valid to delete
        if (new \DateTime('now') >= $event->getRegistrationStartDate()) {
            return $this->customErrorView(
                400,
                self::ERROR_NOT_ALLOWED_DELETE_CODE,
                self::ERROR_NOT_ALLOWED_DELETE_MESSAGE
            );
        }

        // set event visible false
        $event->setIsDeleted(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Save event to db.
     *
     * @param Event $event
     * @param bool  $submit
     *
     * @return View
     */
    private function handleEventPost(
        $event,
        $submit
    ) {
        // check room is valid
        if (!is_null($event->getRoomId())) {
            $room = $this->getRepo('Room\Room')->find($event->getRoomId());
            if (is_null($room)) {
                throw new BadRequestHttpException(self::ERROR_ROOM_INVALID);
            }
        }

        $attachments = $event->getAttachments();
        $dates = $event->getDates();
        $eventForms = $event->getForms();
        $cityId = $event->getCityId();
        $buildingId = $event->getBuildingId();
        $limitNumber = (int) $event->getLimitNumber();
        $registrationStartDate = $event->getRegistrationStartDate();
        $registrationEndDate = $event->getRegistrationEndDate();

        $registrationStartDate = new \DateTime($registrationStartDate);
        $registrationEndDate = new \DateTime($registrationEndDate);
        $registrationStartDate->setTime(00, 00, 00);
        $registrationEndDate->setTime(23, 59, 59);

        $eventStartDate = $this->getEventStartDate($dates);

        // check registration date is valid
        if ($registrationStartDate > $registrationEndDate) {
            return $this->customErrorView(
                400,
                self::ERROR_INVALID_REGISTRATION_DATE_CODE,
                self::ERROR_INVALID_REGISTRATION_DATE_MESSAGE
            );
        }

        // check registration end date is before event date
        if ($registrationEndDate > $eventStartDate) {
            return $this->customErrorView(
                400,
                self::ERROR_INVALID_EVENT_START_DATE_COED,
                self::ERROR_INVALID_EVENT_START_DATE_MESSAGE
            );
        }

        // check event start time and end time
        if (!is_null($dates) && !empty($dates)) {
            foreach ($dates as $date) {
                foreach ($date['times'] as $time) {
                    if ($time['start_time'] >= $time['end_time']) {
                        return $this->customErrorView(
                            400,
                            self::ERROR_INVALID_EVENT_TIME_CODE,
                            self::ERROR_INVALID_EVENT_TIME_MESSAGE
                        );
                    }
                }
            }
        }

        // check limit number is valid
        if ($limitNumber < 0) {
            return $this->customErrorView(
                400,
                self::ERROR_INVALID_LIMIT_NUMBER_CODE,
                self::ERROR_INVALID_LIMIT_NUMBER_MESSAGE
            );
        }

        // add events
        $this->addEvents(
            $event,
            $cityId,
            $buildingId,
            $registrationStartDate,
            $registrationEndDate,
            $dates,
            $submit,
            $eventStartDate
        );

        // add events attachments
        $this->addEventAttachments(
            $event,
            $attachments
        );

        // add events dates
        $this->addEventDates(
            $event,
            $dates
        );

        // add events forms
        $this->addEventForms(
            $event,
            $eventForms
        );

        if($event->getPlatform() == Event::PLATFORM_OFFICIAL) {
            $eventsParameter = $this->getDoctrine()->getRepository('SandboxApiBundle:Parameter\Parameter')
                ->findOneBy([
                    'key' => Parameter::KEY_COMMNUE_EVENTS_MANAGER
                ]);
            $eventsParameter->setValue('true');
        }

        $types = [ViewCounts::TYPE_VIEW, ViewCounts::TYPE_REGISTERING];
        foreach ($types as $type) {
            $this->get('sandbox_api.view_count')->addFirstData(
                ViewCounts::OBJECT_EVENT,
                $event->getId(),
                $type
            );
        }

        $response = array(
            'id' => $event->getId(),
        );

        return new View($response);
    }

    /**
     * Save event modification to db.
     *
     * @param Event $event
     * @param       $submit
     *
     * @return View
     */
    private function handleEventPut(
        $event,
        $submit
    ) {
        // check room is valid
        if (!is_null($event->getRoomId())) {
            $room = $this->getRepo('Room\Room')->find($event->getRoomId());
            if (is_null($room)) {
                throw new BadRequestHttpException(self::ERROR_ROOM_INVALID);
            }
        }

        $attachments = $event->getAttachments();
        $dates = $event->getDates();
        $eventForms = $event->getForms();
        $cityId = $event->getCityId();
        $buildingId = $event->getBuildingId();
        $limitNumber = (int) $event->getLimitNumber();
        $registrationStartDate = new \DateTime($event->getRegistrationStartDate());
        $registrationEndDate = new \DateTime($event->getRegistrationEndDate());

        $registrationStartDate->setTime(00, 00, 00);
        $registrationEndDate->setTime(23, 59, 59);

        // check registration date is valid
        if ($registrationStartDate > $registrationEndDate) {
            return $this->customErrorView(
                400,
                self::ERROR_INVALID_REGISTRATION_DATE_CODE,
                self::ERROR_INVALID_REGISTRATION_DATE_MESSAGE
            );
        }

        // check limit number is valid
        if ($limitNumber < 0) {
            return $this->customErrorView(
                400,
                self::ERROR_INVALID_LIMIT_NUMBER_CODE,
                self::ERROR_INVALID_LIMIT_NUMBER_MESSAGE
            );
        }

        // modify event
        $this->modifyEvents(
            $event,
            $cityId,
            $buildingId,
            $registrationStartDate,
            $registrationEndDate,
            $dates,
            $submit
        );

        // modify event attachments
        $this->modifyEventAttachments(
            $event,
            $attachments
        );

        // modify event dates
        $this->modifyEventDates(
            $event,
            $dates
        );
        // modify event forms
        $this->modifyEventForms(
            $event,
            $eventForms
        );

        return new View();
    }

    /**
     * Modify events.
     *
     * @param Event     $event
     * @param int       $cityId
     * @param int       $buildingId
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param array     $dates
     * @param           $submit
     */
    private function modifyEvents(
        $event,
        $cityId,
        $buildingId,
        $startDate,
        $endDate,
        $dates,
        $submit
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        $city = $this->getRepo('Room\RoomCity')->find($cityId);

        if (!is_null($buildingId)) {
            $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
            if (!is_null($building)) {
                $buildingId = $building->getId();
            }
        }

        $eventStartDate = $this->getEventStartDate($dates);
        $eventEndDate = $this->getEventEndDate($dates);

        $event->setCity($city);
        $event->setBuildingId($buildingId);
        $event->setRegistrationStartDate($startDate);
        $event->setRegistrationEndDate($endDate);
        $event->setEventStartDate($eventStartDate);
        $event->setEventEndDate($eventEndDate);
        $event->setModificationDate($now);

        // set visible & isSaved
        if ($submit) {
            $event->setVisible(true);
            $event->setIsSaved(false);
            $event->setStatus(Event::STATUS_PREHEATING);
        } else {
            $event->setVisible(false);
            $event->setIsSaved(true);
        }

        $em->flush();
    }

    /**
     * Modify events attachments.
     *
     * @param Event $event
     * @param array $attachments
     */
    private function modifyEventAttachments(
        $event,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();

        // remove old data from db
        if (!is_null($attachments) || !empty($attachments)) {
            $eventAttachments = $this->getRepo('Event\EventAttachment')->findByEvent($event);
            foreach ($eventAttachments as $eventAttachment) {
                $em->remove($eventAttachment);
            }

            $this->addEventAttachments(
                $event,
                $attachments
            );
        }
    }

    /**
     * Modify event dates.
     *
     * @param Event $event
     * @param array $dates
     */
    private function modifyEventDates(
        $event,
        $dates
    ) {
        $em = $this->getDoctrine()->getManager();

        if (!is_null($dates) || !empty($dates)) {
            $eventDates = $this->getRepo('Event\EventDate')->findByEvent($event);
            foreach ($eventDates as $eventDate) {
                $em->remove($eventDate);
            }

            $this->addEventDates(
                $event,
                $dates
            );
        }
    }

    /**
     * Modify event forms.
     *
     * @param Event $event
     * @param array $eventForms
     */
    private function modifyEventForms(
        $event,
        $eventForms
    ) {
        $em = $this->getDoctrine()->getManager();

        // check if is valid to modify
        if (new \DateTime('now') >= $event->getRegistrationStartDate()) {
            $em->flush();

            return;
        }

        if (
            Event::REGISTRATION_METHOD_ONLINE == $event->getRegistrationMethod()
            && (!is_null($eventForms) || !empty($eventForms))
        ) {
            $eventFormsArray = $this->getRepo('Event\EventForm')->findByEvent($event);
            foreach ($eventFormsArray as $eventForm) {
                $em->remove($eventForm);
            }

            $this->addEventForms(
                $event,
                $eventForms
            );
        }

        $em->flush();
    }

    /**
     * Save events to db.
     *
     * @param Event     $event
     * @param int       $cityId
     * @param int       $buildingId
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param array     $dates
     * @param           $submit
     * @param \DateTime $eventStartDate
     */
    private function addEvents(
        $event,
        $cityId,
        $buildingId,
        $startDate,
        $endDate,
        $dates,
        $submit,
        $eventStartDate
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        $city = $this->getRepo('Room\RoomCity')->find($cityId);

        if (!is_null($buildingId)) {
            $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
            if (!is_null($building)) {
                $buildingId = $building->getId();
            }
        }

        $eventEndDate = $this->getEventEndDate($dates);

        // set price
        if (!$event->isCharge()) {
            $event->setPrice(0.00);
        } else {
            if ($event->getPrice() == '0') {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
        }

        $event->setCity($city);
        $event->setBuildingId($buildingId);
        $event->setRegistrationStartDate($startDate);
        $event->setRegistrationEndDate($endDate);
        $event->setEventStartDate($eventStartDate);
        $event->setEventEndDate($eventEndDate);
        $event->setIsCharge(true);
        $event->setCreationDate($now);
        $event->setModificationDate($now);

        // set visible & isSaved
        if ($submit) {
            $event->setVisible(true);
            $event->setIsSaved(false);
            $event->setStatus(Event::STATUS_PREHEATING);
        } else {
            $event->setVisible(false);
            $event->setIsSaved(true);
            $event->setStatus(Event::STATUS_SAVED);
        }

        // no verify if not free
        if (!is_null($event->getPrice()) && 0 != $event->getPrice()) {
            $event->setVerify(false);
        }

        $em->persist($event);
    }

    /**
     * Save eventAttachments to db.
     *
     * @param Event $event
     * @param array $attachments
     */
    private function addEventAttachments(
        $event,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();

        if (!is_null($attachments) && !empty($attachments)) {
            foreach ($attachments as $attachment) {
                $eventAttachment = new EventAttachment();
                $eventAttachment->setEvent($event);
                $eventAttachment->setContent($attachment['content']);
                $eventAttachment->setAttachmentType($attachment['attachment_type']);
                $eventAttachment->setFilename($attachment['filename']);
                $eventAttachment->setPreview($attachment['preview']);
                $eventAttachment->setSize($attachment['size']);
                $em->persist($eventAttachment);
            }
        }
    }

    /**
     * Save eventDates to db.
     *
     * @param Event $event
     * @param array $dates
     */
    private function addEventDates(
        $event,
        $dates
    ) {
        $em = $this->getDoctrine()->getManager();

        if (!is_null($dates) && !empty($dates)) {
            foreach ($dates as $date) {
                $eventDate = new EventDate();
                $eventDate->setEvent($event);
                $eventDate->setDate(new \DateTime($date['date']));
                $em->persist($eventDate);

                // add events times
                if (!is_null($date['times']) && !empty($date['times'])) {
                    foreach ($date['times'] as $time) {
                        $eventTime = new EventTime();
                        $eventTime->setDate($eventDate);
                        $eventTime->setStartTime(new \DateTime($date['date'].' '.$time['start_time']));
                        $eventTime->setEndTime(new \DateTime($date['date'].' '.$time['end_time']));
                        $eventTime->setDescription($time['description']);
                        $em->persist($eventTime);
                    }
                }
            }
        }
    }

    /**
     * Save eventForms to db.
     *
     * @param Event $event
     * @param array $forms
     */
    private function addEventForms(
        $event,
        $forms
    ) {
        $em = $this->getDoctrine()->getManager();

        if (
            Event::REGISTRATION_METHOD_ONLINE == $event->getRegistrationMethod()
            && !is_null($forms)
            && !empty($forms)
        ) {
            foreach ($forms as $form) {
                $eventForm = new EventForm();
                $eventForm->setEvent($event);
                $eventForm->setTitle($form['title']);
                $eventForm->setType($form['type']);
                $em->persist($eventForm);

                if (
                    isset($form['options'])
                    && !is_null($form['options'])
                    && !empty($form['options'])
                    && in_array($form['type'], array(EventForm::TYPE_CHECKBOX, EventForm::TYPE_RADIO))
                ) {
                    foreach ($form['options'] as $option) {
                        $eventFormOption = new EventFormOption();
                        $eventFormOption->setForm($eventForm);
                        $eventFormOption->setContent($option['content']);
                        $em->persist($eventFormOption);
                    }
                }
            }
        }
        $em->flush();
    }

    /**
     * @param $dates
     *
     * @return \Datetime
     */
    private function getEventStartDate(
        $dates
    ) {
        if (!is_null($dates) && !empty($dates)) {
            $minDate = min($dates);

            $timeArray = array();
            foreach ($minDate['times'] as $time) {
                array_push($timeArray, $time['start_time']);
            }
            $minTime = min($timeArray);
            $eventStartDate = new \DateTime($minDate['date'].' '.$minTime);

            return $eventStartDate;
        }

        return new \DateTime();
    }

    /**
     * Get event end date.
     *
     * @param $dates
     *
     * @return \Datetime
     */
    private function getEventEndDate(
        $dates
    ) {
        if (!is_null($dates) && !empty($dates)) {
            $maxDate = max($dates);

            $timeArray = array();
            foreach ($maxDate['times'] as $time) {
                array_push($timeArray, $time['end_time']);
            }
            $maxTime = max($timeArray);
            $eventEndDate = new \DateTime($maxDate['date'].' '.$maxTime);

            return $eventEndDate;
        }

        return new \DateTime();
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
