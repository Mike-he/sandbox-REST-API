<?php

namespace Sandbox\ClientApiBundle\Controller\Event;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Event\EventController;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventForm;
use Sandbox\ApiBundle\Entity\Event\EventRegistration;
use Sandbox\ApiBundle\Entity\Event\EventRegistrationCheck;
use Sandbox\ApiBundle\Entity\Event\EventRegistrationForm;
use Sandbox\ApiBundle\Entity\Service\ViewCounts;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Form\Event\EventRegistrationPatchType;
use Sandbox\ApiBundle\Form\Event\EventRegistrationPostType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ClientEventRegistrationController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class ClientEventRegistrationController extends EventController
{
    const ERROR_EVENT_INVALID = 'Invalid event';
    const ERROR_EVENT_FORM_INVALID = 'Invalid event form';

    const ERROR_EXIST_EVENT_REGISTRATION_CODE = 400001;
    const ERROR_EXIST_EVENT_REGISTRATION_MESSAGE = 'You have already registered';
    const ERROR_INVALID_PHONE_CODE = 400002;
    const ERROR_INVALID_PHONE_MESSAGE = 'Invalid phone';
    const ERROR_INVALID_EMAIL_CODE = 400003;
    const ERROR_INVALID_EMAIL_MESSAGE = 'Invalid email';
    const ERROR_INVALID_RADIO_CODE = 400004;
    const ERROR_INVALID_RADIO_MESSAGE = 'Invalid radio';
    const ERROR_INVALID_CHECKBOX_CODE = 400005;
    const ERROR_INVALID_CHECKBOX_MESSAGE = 'Invalid checkbox';
    const ERROR_MISSING_USER_INPUT_CODE = 400006;
    const ERROR_MISSING_USER_INPUT_MESSAGE = 'Missing user input';
    const ERROR_OVER_LIMIT_NUMBER_CODE = 400007;
    const ERROR_OVER_LIMIT_NUMBER_MESSAGE = 'Over registration limit number';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
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
     * @Route("/events/{id}/registrations")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getRegistrationsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // filters
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

        // get event
        $event = $this->getRepo('Event\Event')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        $registrations = $this->getRepo('Event\EventRegistration')->getClientEventRegistrations(
            $event->getId(),
            $limit,
            $offset
        );

        return new View($registrations);
    }

    /**
     * Post registrations.
     *
     * @param Request $request
     * @param int     $event_id
     *
     * @Route("/events/{event_id}/registration")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throw \Exception
     */
    public function postRegistrationAction(
        Request $request,
        $event_id
    ) {
        $userId = $this->getUserId();

        // check event is valid
        $event = $this->getRepo('Event\Event')->findOneBy(array(
            'id' => $event_id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        if ($event->getSalesCompanyId()) {
            $customerId = $this->get('sandbox_api.sales_customer')->createCustomer(
                $userId,
                $event->getSalesCompanyId()
            );
        }

        $eventRegistration = new EventRegistration();

        $form = $this->createForm(new EventRegistrationPostType(), $eventRegistration);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleEventRequest(
            $eventRegistration,
            $event,
            $request
        );
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/events/{id}/registration")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchRegistrationAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        $registration = $this->getRepo('Event\EventRegistration')->findOneBy(array(
            'eventId' => $id,
            'userId' => $userId,
        ));
        $this->throwNotFoundIfNull($registration, self::NOT_FOUND_MESSAGE);

        // bind data
        $registrationJson = $this->container->get('serializer')->serialize($registration, 'json');
        $patch = new Patch($registrationJson, $request->getContent());
        $registrationJson = $patch->apply();

        $form = $this->createForm(new EventRegistrationPatchType(), $registration);
        $form->submit(json_decode($registrationJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param EventRegistration $eventRegistration
     * @param Event             $event
     * @param Request           $request
     *
     * @return View
     */
    private function handleEventRequest(
        $eventRegistration,
        $event,
        $request
    ) {
        $userId = $this->getUserId();

        // check if registration over limit number
        $isOverLimitNumber = $this->checkIfOverLimitNumber($event);
        if ($isOverLimitNumber) {
            return $this->customErrorView(
                400,
                self::ERROR_OVER_LIMIT_NUMBER_CODE,
                self::ERROR_OVER_LIMIT_NUMBER_MESSAGE
            );
        }

        // check if the user is already registered
        $eventRegistrationRecord = $this->getRepo('Event\EventRegistration')->findOneBy(array(
            'eventId' => $event->getId(),
            'userId' => $userId,
        ));
        if (!is_null($eventRegistrationRecord)) {
            return $this->customErrorView(
                400,
                self::ERROR_EXIST_EVENT_REGISTRATION_CODE,
                self::ERROR_EXIST_EVENT_REGISTRATION_MESSAGE
            );
        }

        // get request content
        $requestContent = $request->getContent();
        $eventArray = json_decode($requestContent, true);

        $forms = null;
        if (array_key_exists('forms', $eventArray)) {
            $forms = $eventArray['forms'];
        }

        // add event registration forms
        $result = $this->addEventRegistrationForm(
            $eventRegistration,
            $forms
        );

        // return exception
        if (!is_null($result->getData())) {
            return $result;
        }

        // add event registration
        $this->addEventRegistration(
            $eventRegistration,
            $event,
            $userId
        );

        $this->get('sandbox_api.view_count')->autoCounting(
            ViewCounts::OBJECT_EVENT,
             $event->getId(),
            ViewCounts::TYPE_REGISTERING
        );

        $response = array(
            'id' => $eventRegistration->getId(),
        );

        return new View($response);
    }

    /**
     * @param EventRegistration $eventRegistration
     * @param Event             $event
     * @param int               $userId
     */
    private function addEventRegistration(
        $eventRegistration,
        $event,
        $userId
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');
        $user = $this->getRepo('User\User')->find($userId);

        $eventRegistration->setUser($user);
        $eventRegistration->setEvent($event);
        $eventRegistration->setCreationDate($now);
        $eventRegistration->setModificationDate($now);

        // set status
        if (!$event->isVerify()) {
            $eventRegistration->setStatus(EventRegistration::STATUS_ACCEPTED);
        }

        $em->persist($eventRegistration);
        $em->flush();
    }

    /**
     * @param EventRegistration $eventRegistration
     * @param array             $forms
     *
     * @return View
     */
    private function addEventRegistrationForm(
        $eventRegistration,
        $forms
    ) {
        $em = $this->getDoctrine()->getManager();

        if (is_null($forms) || empty($forms)) {
            return new View();
        }

        foreach ($forms as $form) {
            $userInput = $form['user_input'];
            if (is_null($userInput)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_MISSING_USER_INPUT_CODE,
                    self::ERROR_MISSING_USER_INPUT_MESSAGE
                );
            }

            $eventForm = $this->getRepo('Event\EventForm')->find($form['id']);
            if (is_null($eventForm)) {
                throw new BadRequestHttpException(self::ERROR_EVENT_INVALID);
            }

            // check if user input is legal
            $formType = $eventForm->getType();
            $formId = $eventForm->getId();

            if (EventForm::TYPE_PHONE == $formType) {
                if (!is_numeric($userInput)) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_INVALID_PHONE_CODE,
                        self::ERROR_INVALID_PHONE_MESSAGE
                    );
                }
            } elseif (EventForm::TYPE_EMAIL == $formType) {
                if (!filter_var($userInput, FILTER_VALIDATE_EMAIL)) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_INVALID_EMAIL_CODE,
                        self::ERROR_INVALID_EMAIL_MESSAGE
                    );
                }
            } elseif (EventForm::TYPE_RADIO == $formType) {
                $formOption = $this->getRepo('Event\EventFormOption')->findOneBy(array(
                    'id' => (int) $userInput,
                    'formId' => $formId,
                ));
                if (is_null($formOption)) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_INVALID_RADIO_CODE,
                        self::ERROR_INVALID_RADIO_MESSAGE
                    );
                }
            } elseif (EventForm::TYPE_CHECKBOX == $formType) {
                $delimiter = ',';
                $ids = explode($delimiter, $userInput);

                foreach ($ids as $id) {
                    $formOption = $this->getRepo('Event\EventFormOption')->findOneBy(array(
                        'id' => (int) $id,
                        'formId' => $formId,
                    ));
                    if (is_null($formOption)) {
                        return $this->customErrorView(
                            400,
                            self::ERROR_INVALID_CHECKBOX_CODE,
                            self::ERROR_INVALID_CHECKBOX_MESSAGE
                        );
                    }
                }
            }

            $registrationForm = new EventRegistrationForm();

            $registrationForm->setRegistration($eventRegistration);
            $registrationForm->setForm($eventForm);
            $registrationForm->setUserInput($userInput);

            $em->persist($registrationForm);
        }

        return new View();
    }

    /**
     * Check if is over limit number.
     *
     * @param Event $event
     *
     * @return bool
     */
    protected function checkIfOverLimitNumber(
        $event
    ) {
        $limitNumber = $event->getLimitNumber();
        if (0 == $limitNumber) {
            return false;
        }

        $check = new EventRegistrationCheck();
        $check->setEventId($event->getId());

        // in case of duplicate submits
        $em = $this->getDoctrine()->getManager();
        $em->persist($check);
        $em->flush();

        $checkCounts = $this->getRepo('Event\EventRegistrationCheck')
            ->getEventRegistrationCheckCount($event->getId());

        if ($event->isVerify()) {
            $registrations = $this->getRepo('Event\EventRegistration')
                ->getAcceptedPersonNumber($event->getId());
        } else {
            $registrations = $this->getRepo('Event\EventRegistration')
                ->getRegistrationCounts($event->getId());
        }
        $totalCounts = (int) ($registrations + $checkCounts);

        $em->remove($check);
        $em->flush();

        // if not over limit number
        if ($limitNumber < $totalCounts) {
            return true;
        }

        return false;
    }
}
