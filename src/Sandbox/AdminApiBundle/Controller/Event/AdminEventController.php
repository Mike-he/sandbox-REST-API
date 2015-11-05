<?php

namespace Sandbox\AdminApiBundle\Controller\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventAttachment;
use Sandbox\ApiBundle\Entity\Event\EventDate;
use Sandbox\ApiBundle\Entity\Event\EventForm;
use Sandbox\ApiBundle\Entity\Event\EventFormOption;
use Sandbox\ApiBundle\Entity\Event\EventTime;
use Sandbox\ApiBundle\Form\Event\EventPostType;
use Sandbox\ApiBundle\Form\Event\EventPutType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
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
 * @link     http://www.Sandbox.cn/
 */
class AdminEventController extends SandboxRestController
{
    const ERROR_NOT_ALLOWED_MODIFY_CODE = 400001;
    const ERROR_NOT_ALLOWED_MODIFY_MESSAGE = 'Not allowed to modify - 不允许被修改';
    const ERROR_NOT_ALLOWED_DELETE_CODE = 400002;
    const ERROR_NOT_ALLOWED_DELETE_MESSAGE = 'Not allowed to delete - 不允许被删除';
    const ERROR_INVALID_LIMIT_NUMBER_CODE = 400003;
    const ERROR_INVALID_LIMIT_NUMBER_MESSAGE = 'Invalid limit number';
    const ERROR_INVALID_REGISTRATION_DATE_CODE = 400004;
    const ERROR_INVALID_REGISTRATION_DATE_MESSAGE = 'Invalid registration date';

    const ERROR_ROOM_INVALID = 'Invalid room';

    /**
     * Get Events.
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
     *    requirements="(ongoing|end)",
     *    strict=true,
     *    description="event status"
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
        $this->checkAdminEventPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $status = $paramFetcher->get('status');

        $query = $this->getRepo('Event\Event')->getEvents($status);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $query,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
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
    public function getEventAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminEventPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // get an event
        $event = $this->getRepo('Event\Event')->find($id);
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

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
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
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
        $this->checkAdminEventPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $event = new Event();

        $form = $this->createForm(new EventPostType(), $event);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleEventPost(
            $event,
            $request
        );
    }

    /**
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
        $this->checkAdminEventPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $event = $this->getRepo('Event\Event')->find($id);

        // check if is valid to modify
        if (new \DateTime('now') >= $event->getRegistrationStartDate()) {
            return $this->customErrorView(
                400,
                self::ERROR_NOT_ALLOWED_MODIFY_CODE,
                self::ERROR_NOT_ALLOWED_MODIFY_MESSAGE
            );
        }

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

        // handle event form
        return $this->handleEventPut(
            $event,
            $request
        );
    }

    /**
     * Delete a event.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "OK"
     *  }
     * )
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
        $this->checkAdminEventPermission(AdminPermissionMap::OP_LEVEL_EDIT);

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
        $event->setVisible(false);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Save event to db.
     *
     * @param Event   $event
     * @param Request $request
     *
     * @return View
     */
    private function handleEventPost(
        $event,
        Request $request
    ) {
        // check room is valid
        if (!is_null($event->getRoomId())) {
            $room = $this->getRepo('Room\Room')->find($event->getRoomId());
            if (is_null($room)) {
                throw new BadRequestHttpException(self::ERROR_ROOM_INVALID);
            }
        }

        $requestContent = $request->getContent();
        $eventArray = json_decode($requestContent, true);

        $attachments = null;
        if (array_key_exists('event_attachments', $eventArray)) {
            $attachments = $eventArray['event_attachments'];
        }

        $dates = null;
        if (array_key_exists('event_dates', $eventArray)) {
            $dates = $eventArray['event_dates'];
        }

        $eventForms = null;
        if (array_key_exists('event_forms', $eventArray)) {
            $eventForms = $eventArray['event_forms'];
        }

        $registrationStartDate = $event->getRegistrationStartDate();
        $registrationEndDate = $event->getRegistrationEndDate();

        $registrationStartDate = new \DateTime($registrationStartDate);
        $registrationEndDate = new \DateTime($registrationEndDate);
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

        $cityId = $event->getCityId();

        $buildingId = $event->getBuildingId();

        $limitNumber = (int) $event->getLimitNumber();

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
            $dates
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

        $response = array(
            'id' => $event->getId(),
        );

        return new View($response);
    }

    /**
     * Save event modification to db.
     *
     * @param Request $request
     * @param Event   $event
     *
     * @return View
     */
    private function handleEventPut(
        $event,
        $request
    ) {
        // check room is valid
        if (!is_null($event->getRoomId())) {
            $room = $this->getRepo('Room\Room')->find($event->getRoomId());
            if (is_null($room)) {
                throw new BadRequestHttpException(self::ERROR_ROOM_INVALID);
            }
        }

        $requestContent = $request->getContent();
        $eventArray = json_decode($requestContent, true);

        $attachments = null;
        if (array_key_exists('event_attachments', $eventArray)) {
            $attachments = $eventArray['event_attachments'];
        }

        $dates = null;
        if (array_key_exists('event_dates', $eventArray)) {
            $dates = $eventArray['event_dates'];
        }

        $eventForms = null;
        if (array_key_exists('event_forms', $eventArray)) {
            $eventForms = $eventArray['event_forms'];
        }

        $registrationStartDate = $event->getRegistrationStartDate();
        $registrationEndDate = $event->getRegistrationEndDate();

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

        $cityId = $event->getCityId();

        $buildingId = $event->getBuildingId();

        $limitNumber = (int) $event->getLimitNumber();

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
            $dates
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
     * @param Array     $dates
     */
    private function modifyEvents(
        $event,
        $cityId,
        $buildingId,
        $startDate,
        $endDate,
        $dates
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        $city = $this->getRepo('Room\RoomCity')->find($cityId);
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);

        $eventEndDate = $this->getEventEndDate($dates);

        $event->setCity($city);
        $event->setBuilding($building);
        $event->setRegistrationStartDate($startDate);
        $event->setRegistrationEndDate($endDate);
        $event->setEventEndDate($eventEndDate);
        $event->setCreationDate($now);
        $event->setModificationDate($now);

        $em->flush();
    }

    /**
     * Modify events attachments.
     *
     * @param Event $event
     * @param Array $attachments
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
     * @param Array $dates
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
     * @param Array $eventForms
     */
    private function modifyEventForms(
        $event,
        $eventForms
    ) {
        $em = $this->getDoctrine()->getManager();

        if (
            $event->getRegistrationMethod() == Event::REGISTRATION_METHOD_ONLINE
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
    }

    /**
     * Save events to db.
     *
     * @param Event     $event
     * @param int       $cityId
     * @param int       $buildingId
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param Array     $dates
     */
    private function addEvents(
        $event,
        $cityId,
        $buildingId,
        $startDate,
        $endDate,
        $dates
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        $city = $this->getRepo('Room\RoomCity')->find($cityId);
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);

        $eventEndDate = $this->getEventEndDate($dates);

        $event->setCity($city);
        $event->setBuilding($building);
        $event->setRegistrationStartDate($startDate);
        $event->setRegistrationEndDate($endDate);
        $event->setEventEndDate($eventEndDate);
        $event->setCreationDate($now);
        $event->setModificationDate($now);

        $em->persist($event);
    }

    /**
     * Save eventAttachments to db.
     *
     * @param Event $event
     * @param Array $attachments
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
     * @param Array $dates
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
     * @param Array $forms
     */
    private function addEventForms(
        $event,
        $forms
    ) {
        $em = $this->getDoctrine()->getManager();

        if (
            $event->getRegistrationMethod() == Event::REGISTRATION_METHOD_ONLINE
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_EVENT,
            $opLevel
        );
    }
}
