<?php

namespace Sandbox\AdminApiBundle\Controller\Event;

use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventForm;
use Sandbox\ApiBundle\Form\Event\EventRegistrationPatchType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class AdminEventRegistrationController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminEventRegistrationController extends SandboxRestController
{
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
        $this->checkAdminEventRegistrationPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $ids = $paramFetcher->get('id');
        foreach ($ids as $id) {
            $registration = $this->getRepo('Event\EventRegistration')->find($id);

            // check event registration
            if (is_null($registration) || empty($registration)) {
                continue;
            }

            // check registration user's banned and authorized status
            $user = $this->getRepo('User\User')->find($registration->getUser());
            if ($user->isBanned() || !$user->isAuthorized()) {
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
        }

        return new View();
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
     *    requirements="(accepted|refused)",
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
        $this->checkAdminEventRegistrationPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $status = $paramFetcher->get('status');
        $query = $paramFetcher->get('query');

        // get query result
        $queryResults = $this->getRepo('Event\EventRegistration')->getEventRegistrations(
            $event_id,
            $status,
            $query
        );

        $registrationsArray = $this->generateRegistrationsArray($queryResults);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $registrationsArray,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param array $queryResults
     *
     * @return array
     */
    private function generateRegistrationsArray(
        $queryResults
    ) {
        if (!is_null($queryResults) && !empty($queryResults)) {
            $registrationsArray = array();
            foreach ($queryResults as $result) {
                // get form option results
                $formsArray = $this->getEventRegistrationFormOptions($result['id']);
                $results = array_merge($result, array('forms' => $formsArray));
                array_push($registrationsArray, $results);
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

        if (!is_null($registrationForms) && !empty($registrationForms)) {
            $formsArray = array();
            foreach ($registrationForms as $registrationForm) {
                $inputResult = null;

                // text string result
                if (in_array($registrationForm->getForm()->getType(), array(
                    EventForm::TYPE_TEXT,
                    EventForm::TYPE_EMAIL,
                    EventForm::TYPE_PHONE,
                ))
                ) {
                    $inputResult = $registrationForm->getUserInput();
                }
                // radio result
                elseif ($registrationForm->getForm()->getType() == EventForm::TYPE_RADIO) {
                    $option = $this->getRepo('Event\EventFormOption')->findOneBy(array(
                        'id' => (int) $registrationForm->getUserInput(),
                        'formId' => $registrationForm->getForm()->getId(),
                    ));
                    $inputResult = $option->getContent();
                }
                // check box result
                elseif ($registrationForm->getForm()->getType() == EventForm::TYPE_CHECKBOX) {
                    $delimiter = ',';
                    $optionIds = explode($delimiter, $registrationForm->getUserInput());

                    $inputResult = $this->getRepo('Event\EventFormOption')->getEventFormOptionCheckbox(
                        $optionIds,
                        $registrationForm->getForm()->getId()
                    );
                }

                $formArray = array(
                    'id' => $registrationForm->getId(),
                    'title' => $registrationForm->getForm()->getTitle(),
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_EVENT,
            $opLevel
        );
    }
}
