<?php

namespace Sandbox\ClientApiBundle\Controller\Event;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventForm;
use Sandbox\ApiBundle\Entity\Event\EventRegistration;
use Sandbox\ApiBundle\Entity\Event\EventRegistrationForm;
use Sandbox\ApiBundle\Entity\User\User;
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
 * @link     http://www.Sandbox.cn/
 */
class ClientEventRegistrationController extends SandboxRestController
{
    const ERROR_EVENT_INVALID = 'Invalid event';
    const ERROR_EVENT_FORM_INVALID = 'Invalid event form';

    const ERROR_EXIST_EVENT_REGISTRATION_CODE = '400001';
    const ERROR_EXIST_EVENT_REGISTRATION_MESSAGE = 'You have already registered';
    const ERROR_INVALID_PHONE_CODE = '400002';
    const ERROR_INVALID_PHONE_MESSAGE = 'Invalid phone';
    const ERROR_INVALID_EMAIL_CODE = '400003';
    const ERROR_INVALID_EMAIL_MESSAGE = 'Invalid email';
    const ERROR_INVALID_RADIO_CODE = '400004';
    const ERROR_INVALID_RADIO_MESSAGE = 'Invalid radio';
    const ERROR_INVALID_CHECKBOX_CODE = '400005';
    const ERROR_INVALID_CHECKBOX_MESSAGE = 'Invalid checkbox';

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
        $eventRegistration = new EventRegistration();

        $form = $this->createForm(new EventRegistrationPostType(), $eventRegistration);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleEventRequest(
            $eventRegistration,
            $event_id,
            $request
        );
    }

    /**
     * @param EventRegistration $eventRegistration
     * @param int               $event_id
     * @param Request           $request
     *
     * @return View
     */
    private function handleEventRequest(
        $eventRegistration,
        $event_id,
        $request
    ) {
        $userId = $this->getUserId();

        // check event is valid
        $event = $this->getRepo('Event\Event')->find($event_id);
        if (is_null($event) || $event->getVisible() == false) {
            throw new BadRequestHttpException(self::ERROR_EVENT_INVALID);
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

        // add event registration
        $this->addEventRegistration(
            $eventRegistration,
            $event,
            $userId
        );

        // add event registration forms
        $em = $this->getDoctrine()->getManager();

        if (!is_null($forms) && !empty($forms)) {
            foreach ($forms as $form) {
                $userInput = $form['user_input'];

                $eventForm = $this->getRepo('Event\EventForm')->find($form['id']);
                if (is_null($eventForm)) {
                    throw new BadRequestHttpException(self::ERROR_EVENT_INVALID);
                }

                // check if user input is legal
                $formType = $eventForm->getType();
                $formId = $eventForm->getId();
                if ($formType == EventForm::TYPE_PHONE) {
                    if (is_null($userInput) || !is_numeric($userInput)) {
                        $em->remove($eventRegistration);
                        $em->flush();

                        return $this->customErrorView(
                            400,
                            self::ERROR_INVALID_PHONE_CODE,
                            self::ERROR_INVALID_PHONE_MESSAGE
                        );
                    }
                } elseif ($formType == EventForm::TYPE_EMAIL) {
                    if (is_null($userInput) ||
                        !filter_var($userInput, FILTER_VALIDATE_EMAIL)) {
                        $em->remove($eventRegistration);
                        $em->flush();

                        return $this->customErrorView(
                            400,
                            self::ERROR_INVALID_EMAIL_CODE,
                            self::ERROR_INVALID_EMAIL_MESSAGE
                        );
                    }
                } elseif ($formType == EventForm::TYPE_RADIO) {
                    $formOption = $this->getRepo('Event\EventFormOption')->findOneBy(array(
                        'id' => (int) $userInput,
                        'formId' => $formId,
                    ));
                    if (is_null($formOption)) {
                        $em->remove($eventRegistration);
                        $em->flush();

                        return $this->customErrorView(
                            400,
                            self::ERROR_INVALID_RADIO_CODE,
                            self::ERROR_INVALID_RADIO_MESSAGE
                        );
                    }
                } elseif ($formType == EventForm::TYPE_CHECKBOX) {
                    $delimiter = ',';
                    $ids = explode($delimiter, $userInput);

                    foreach ($ids as $id) {
                        $formOption = $this->getRepo('Event\EventFormOption')->findOneBy(array(
                            'id' => (int) $id,
                            'formId' => $formId,
                        ));
                        if (is_null($formOption)) {
                            $em->remove($eventRegistration);
                            $em->flush();

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
                $em->flush();
            }
        }

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

        $em->persist($eventRegistration);
        $em->flush();
    }
}
