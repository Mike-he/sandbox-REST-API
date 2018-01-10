<?php

namespace Sandbox\AdminApiBundle\Controller\Event;

use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Event\EventForm;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Event\EventRegistration;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Sandbox\ApiBundle\Form\Event\EventRegistrationPatchType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Traits\EventNotification;

/**
 * Class AdminEventRegistrationController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class AdminEventRegistrationController extends SandboxRestController
{
    use EventNotification;
    const ERROR_ACCOUNT_BANNED_CODE = 401001;
    const ERROR_ACCOUNT_BANNED_MESSAGE = 'Registration user is banned or unauthorized. - 报名用户已经被冻结或未认证.';

    /**
     * Patch events registrations status.
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
     *    name="id",
     *    array=true,
     *    default="1",
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description="event registration id"
     * )
     *
     * @Route("/events/registrations")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchEventRegistrationsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminEventRegistrationPermission(AdminPermission::OP_LEVEL_EDIT);

        $ids = $paramFetcher->get('id');
        foreach ($ids as $id) {
            $registration = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Event\EventRegistration')
                ->find($id);

            // check event registration
            if (is_null($registration) || empty($registration)) {
                continue;
            }

            // check registration user's banned and authorized status
            $user = $registration->getUser();
            if ($user->isBanned()) {
                return $this->customErrorView(
                    401,
                    self::ERROR_ACCOUNT_BANNED_CODE,
                    self::ERROR_ACCOUNT_BANNED_MESSAGE
                );
            }

            $registrationJson = $this->container->get('serializer')->serialize($registration, 'json');
            $patch = new Patch($registrationJson, $request->getContent());
            $registrationJson = $patch->apply();

            $form = $this->createForm(new EventRegistrationPatchType(), $registration);
            $form->submit(json_decode($registrationJson, true));

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            if ($registration->getStatus() == EventRegistration::STATUS_ACCEPTED) {
                // generate event order
                $userId = $registration->getUserId();
                $eventId = $registration->getEventId();
                $event = $registration->getEvent();

                $eventOrder = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Event\EventOrder')
                    ->findOneBy([
                        'userId' => $userId,
                        'eventId' => $eventId,
                        'status' => [EventOrder::STATUS_PAID, EventOrder::STATUS_COMPLETED],
                    ]);

                if (is_null($eventOrder)) {
                    $eventOrder = new EventOrder();

                    // generate order number
                    $orderNumber = $this->getOrderNumber(EventOrder::LETTER_HEAD);

                    // set order
                    $eventOrder->setEvent($event);
                    $eventOrder->setUserId($userId);
                    $eventOrder->setPrice($event->getPrice());
                    $eventOrder->setOrderNumber($orderNumber);

                    if ($event->getSalesCompanyId()) {
                        $customerId = $this->get('sandbox_api.sales_customer')->createCustomer(
                            $userId,
                            $event->getSalesCompanyId()
                        );
                        $eventOrder->setCustomerId($customerId);
                    }
                    $eventOrder->setStatus(EventOrder::STATUS_PAID);

                    // set service fee
                    $serviceInfo = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
                        ->findOneBy(array(
                            'tradeTypes' => SalesCompanyServiceInfos::TRADE_TYPE_ACTIVITY,
                            'company' => $event->getSalesCompanyId(),
                            'status' => true,
                        ));
                    if (!is_null($serviceInfo)) {
                        $eventOrder->setServiceFee($serviceInfo->getServiceFee());
                    }

                    $em->persist($eventOrder);
                    $em->flush();
                }

                $contentArray = array(
                    'type' => 'event',
                    'action' => EventRegistration::ACTION_ACCEPT,
                    'event' => array(
                        'id' => $registration->getEvent()->getId(),
                        'name' => $registration->getEvent()->getName(),
                    ),
                );

                $zhData = $this->getJpushData(
                    [$user->getId()],
                    ['lang_zh'],
                    '',
                    '创合秒租',
                    $contentArray
                );

                $enData = $this->getJpushData(
                    [$user->getId()],
                    ['lang_en'],
                    '',
                    'Sandbox3',
                    $contentArray
                );

                $this->sendJpushNotification($zhData);
                $this->sendJpushNotification($enData);
            }
        }

        return new View();
    }

    /**
     * @param $letter
     *
     * @return string
     */
    public function getOrderNumber(
        $letter
    ) {
        $datetime = new \DateTime();
        $now = clone $datetime;
        $now->setTime(00, 00, 00);
        $date = round(microtime(true) * 1000);
        $counter = $this->getRepo('Order\OrderCount')->findOneBy(['orderDate' => $now]);
        if (is_null($counter)) {
            $count = 1;
            $this->setOrderCount($count, $now);
        } else {
            $count = $counter->getCount() + 1;
            $counter->setCount($count);
            $em = $this->getDoctrine()->getManager();
            $em->persist($counter);
            $em->flush();
        }

        $serverId = $this->getGlobal('server_order_id');
        $orderNumber = $letter."$date"."$count"."$serverId";

        return $orderNumber;
    }

    /**
     * @param $count
     * @param $now
     */
    public function setOrderCount(
        $count,
        $now
    ) {
        $counter = new OrderCount();
        $counter->setCount($count);
        $counter->setOrderDate($now);
        $em = $this->getDoctrine()->getManager();
        $em->persist($counter);
        $em->flush();
    }

    /**
     * Get event registrations list.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $event_id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
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
     *    requirements="(pending|accepted|rejected)",
     *    strict=true,
     *    description="event status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Route("/events/{event_id}/registrations")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throw \Exception
     */
    public function getAdminEventRegistrationsAction(
        Request $request,
        ParamFetcherInterface  $paramFetcher,
        $event_id
    ) {
        // check user permission
        $this->checkAdminEventRegistrationPermission(AdminPermission::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $status = $paramFetcher->get('status');
        $query = $paramFetcher->get('query');
        $event = $this->getRepo('Event\Event')->find($event_id);
        $registrationCounts = $this->getRepo('Event\EventRegistration')
            ->getRegistrationCounts($event_id);
        $customParameters['registration_person_number'] = (int) $registrationCounts;

        // set accepted person number
        if ($event->isVerify()) {
            $acceptedCounts = $this->getRepo('Event\EventRegistration')
                ->getAcceptedPersonNumber($event_id);
            $customParameters['accepted_person_number'] = (int) $acceptedCounts;
        }

        // get query result
        $eventRegistrations = $this->getRepo('Event\EventRegistration')->getEventRegistrations(
            $event_id,
            $status,
            $query
        );

        $registrationsArray = $this->generateRegistrationsArray($eventRegistrations);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $registrationsArray,
            $pageIndex,
            $pageLimit
        );
        $pagination->setCustomParameters($customParameters);

        return new View($pagination);
    }

    /**
     * Get definite id of event registration.
     *
     * @param Request $request
     * @param int     $event_id
     * @param int     $registration_id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Route("/events/{event_id}/registrations/{registration_id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throw \Exception
     */
    public function getAdminEventRegistrationAction(
        Request $request,
        $event_id,
        $registration_id
    ) {
        // check user permission
        $this->checkAdminEventRegistrationPermission(AdminPermission::OP_LEVEL_VIEW);

        // get query result
        $eventRegistrationArray = $this->getRepo('Event\EventRegistration')->getEventRegistration(
            $event_id,
            $registration_id
            );
        if (is_null($eventRegistrationArray)) {
            return new View(array());
        }

        $formsArray = $this->getEventRegistrationFormOptions($registration_id);

        $registration = array_merge($eventRegistrationArray, array('forms' => $formsArray));

        return new View($registration);
    }

    /**
     * @param array $eventRegistrations
     *
     * @return array
     */
    private function generateRegistrationsArray(
        $eventRegistrations
    ) {
        if (!is_null($eventRegistrations) && !empty($eventRegistrations)) {
            $registrationsArray = array();
            foreach ($eventRegistrations as $registration) {
                // get form option results
                $formsArray = $this->getEventRegistrationFormOptions($registration['id']);
                $registrations = array_merge($registration, array('forms' => $formsArray));
                array_push($registrationsArray, $registrations);
            }

            return $registrationsArray;
        }

        return array();
    }

    /**
     * Get option results.
     *
     * @param $registrationId
     *
     * @return array
     */
    private function getEventRegistrationFormOptions(
        $registrationId
    ) {
        $registrationForms = $this->getRepo('Event\EventRegistrationForm')
            ->findByRegistrationId($registrationId);

        $typeArray = array(
            EventForm::TYPE_TEXT,
            EventForm::TYPE_EMAIL,
            EventForm::TYPE_PHONE,
        );

        if (!is_null($registrationForms) && !empty($registrationForms)) {
            $formsArray = array();
            foreach ($registrationForms as $registrationForm) {
                $userInput = $registrationForm->getUserInput();
                $form = $registrationForm->getForm();
                $formType = $form->getType();
                $formId = $form->getId();
                $formTitle = $form->getTitle();

                $inputResult = null;

                if (in_array($formType, $typeArray)
                ) {
                    // text string result
                    $inputResult = $userInput;
                } elseif (EventForm::TYPE_RADIO == $formType) {
                    // radio result
                    $option = $this->getRepo('Event\EventFormOption')->findOneBy(array(
                        'id' => (int) $userInput,
                        'formId' => $formId,
                    ));
                    $inputResult = $option->getContent();
                } elseif (EventForm::TYPE_CHECKBOX == $formType) {
                    // check box result
                    $delimiter = ',';
                    $optionIds = explode($delimiter, $userInput);

                    $inputResult = $this->getRepo('Event\EventFormOption')->getEventFormOptionCheckbox(
                        $optionIds,
                        $formId
                    );
                }

                $formArray = array(
                    'id' => $formId,
                    'title' => $formTitle,
                    'user_input' => $inputResult,
                );
                array_push($formsArray, $formArray);
            }

            return $formsArray;
        }

        return array();
    }

    /**
     * Check user permission.
     *
     * @param $opLevel
     */
    private function checkAdminEventRegistrationPermission(
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
