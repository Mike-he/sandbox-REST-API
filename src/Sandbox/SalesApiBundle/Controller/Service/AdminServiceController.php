<?php

namespace Sandbox\SalesApiBundle\Controller\Service;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Service\Service;
use Sandbox\ApiBundle\Entity\Service\ServiceAttachment;
use Sandbox\ApiBundle\Entity\Service\ServiceForm;
use Sandbox\ApiBundle\Entity\Service\ServiceFormOption;
use Sandbox\ApiBundle\Entity\Service\ServiceTime;
use Sandbox\ApiBundle\Form\Service\ServicePostType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Controller\Annotations;

class AdminServiceController extends SalesRestController
{
    const ERROR_NOT_ALLOWED_MODIFY_CODE = 400001;
    const ERROR_NOT_ALLOWED_MODIFY_MESSAGE = 'Not allowed to modify - 不允许被修改';
    const ERROR_NOT_ALLOWED_DELETE_CODE = 400002;
    const ERROR_NOT_ALLOWED_DELETE_MESSAGE = 'Not allowed to delete - 不允许被删除';
    const ERROR_INVALID_LIMIT_NUMBER_CODE = 400003;
    const ERROR_INVALID_LIMIT_NUMBER_MESSAGE = 'Invalid limit number';
    const ERROR_INVALID_EVENT_TIME_CODE = 400006;
    const ERROR_INVALID_EVENT_TIME_MESSAGE = 'Event start time should before event end time';
    const ERROR_INVALID_EVENT_PRICE_CODE = 400007;
    const ERROR_INVALID_EVENT_PRICE_MESSAGE = 'Event can not be null while need charge';

    const ERROR_ROOM_INVALID = 'Invalid room';

    /**
     * Get Services.
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
     *    description="How many services to return"
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
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    description="services typeId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="visible",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="services visible"
     * )
     *
     * @Route("/services")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getServicesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkSalesAdminServicePermission(AdminPermission::OP_LEVEL_VIEW);

        // get sales company id
        $salesCompanyId = $this->getSalesCompanyId();

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $type = $paramFetcher->get('type');
        $visible = $paramFetcher->get('visible');

        $servicesArray = array();
        $services = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\Service')
            ->getSalesServices(
                $type,
                $visible,
                $salesCompanyId
            );

        foreach ($services as $serviceArray) {
            $service = $serviceArray['service'];
            $attachments = $this->getRepo('Service\ServiceAttachment')->findByService($service);
            $times = $this->getRepo('Service\ServiceTime')->findByService($service);
            $forms = $this->getRepo('Service\ServiceForm')->findByService($service);

            $service->setAttachments($attachments);
            $service->setTimes($times);
            $service->setForms($forms);

            array_push($servicesArray, $service);
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $servicesArray,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param Request $request
     *
     * @Route("/service/types")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getTypesAction(
        Request $request
    ){
        $types = $this->getDoctrine()->getManager()
            ->getRepository('SandboxApiBundle:Service\ServiceType')
            ->findAll();

        return new View($types);
    }
    
    /**
     * Create Service
     *
     * @param Request $request
     *
     * @Route("/services")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postServiceAction(
        Request $request
    ){
        // check user permission
        $this->checkSalesAdminServicePermission(AdminPermission::OP_LEVEL_EDIT);

        $service = new Service();

        $form = $this->createForm(new ServicePostType(), $service);
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

        return $this->handleServicePost(
            $service,
            $submit
        );
    }

    /**
     * Save service to db.
     *
     * @param Service $service
     * @param bool  $submit
     *
     * @return View
     */
    private function handleServicePost(
        $service,
        $submit
    ) {
        $attachments = $service->getAttachments();
        $times = $service->getTimes();
        $serviceForms = $service->getForms();
        $countryId = $service->getCountryId();
        $cityId = $service->getCityId();
        $provinceId = $service->getProvinceId();
        $districtId = $service->getDistrictId();
        $limitNumber = (int) $service->getLimitNumber();

        // check event start time and end time
        if (!is_null($times) && !empty($times)) {

            foreach ($times as $time) {
                if ($time['start_time'] >= $time['end_time']) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_INVALID_EVENT_TIME_CODE,
                        self::ERROR_INVALID_EVENT_TIME_MESSAGE
                    );
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

        // add services
        $this->addService(
            $service,
            $countryId,
            $provinceId,
            $cityId,
            $districtId,
            $submit
        );

        // add services attachments
        $this->addServiceAttachments(
            $service,
            $attachments
        );

        // add services times
        $this->addServiceTimes(
            $service,
            $times
        );

        // add services forms
        $this->addServiceForms(
            $service,
            $serviceForms
        );

        $response = array(
            'id' => $service->getId(),
        );

        return new View($response);
    }

    /**
     * @param Service $service
     * @param $countryId
     * @param $provinceId
     * @param $cityId
     * @param $districtId
     * @param $typeId
     * @param $submit
     */
    private function addService(
        $service,
        $countryId,
        $provinceId,
        $cityId,
        $districtId,
        $submit
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        $province = $this->getRepo('Room\RoomCity')->find($provinceId);
        $country = $this->getRepo('Room\RoomCity')->find($countryId);
        $city = $this->getRepo('Room\RoomCity')->find($cityId);
        $district = $this->getRepo('Room\RoomCity')->find($districtId);
        $serviceStartDate = new \DateTime($service->getServiceStartDate());
        $serviceEndDate = new \DateTime($service->getServiceEndDate());

        // set price
        if (!$service->isCharge()) {
            $service->setPrice(0.00);
        }

        $service->setCountry($country);
        $service->setProvince($province);
        $service->setDistrict($district);
        $service->setCity($city);
        $service->setServiceStartDate($serviceStartDate);
        $service->setServiceEndDate($serviceEndDate);
        $service->setSalesCompanyId($this->getSalesCompanyId());
        $service->setIsCharge(true);
        $service->setCreationDate($now);
        $service->setModificationDate($now);

        // set visible & isSaved
        if ($submit) {
            $service->setVisible(true);
            $service->setIsSaved(false);
            $service->setStatus(Service::STATUS_PREHEATING);
        } else {
            $service->setVisible(false);
            $service->setIsSaved(true);
            $service->setStatus(Service::STATUS_SAVED);
        }

        $em->persist($service);
    }

    /**
     * @param Service $service
     * @param $attachments
     */
    private function addServiceAttachments(
        $service,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();

        if (!is_null($attachments) && !empty($attachments)) {
            foreach ($attachments as $attachment) {
                $serviceAttachment = new ServiceAttachment();
                $serviceAttachment->setService($service);
                $serviceAttachment->setContent($attachment['content']);
                $serviceAttachment->setAttachmentType($attachment['attachment_type']);
                $serviceAttachment->setFilename($attachment['filename']);
                $serviceAttachment->setPreview($attachment['preview']);
                $serviceAttachment->setSize($attachment['size']);
                $em->persist($serviceAttachment);
            }
        }
    }

    /**
     * @param Service $service
     * @param $times
     */
    private function addServiceTimes(
        $service,
        $times
    ){
        if (!is_null($times) && !empty($times)) {
            foreach ($times as $time) {
                $serviceTime = new ServiceTime();
                $serviceTime->setService($service);

                $format = 'H:i:s';
                $start = \DateTime::createFromFormat(
                    $format,
                    $time['start_time']
                );
                $end = \DateTime::createFromFormat(
                    $format,
                    $time['end_time']
                );

                $serviceTime->setStartTime($start);
                $serviceTime->setEndTime($end);
                $serviceTime->setDescription($time['description']);

                $em = $this->getDoctrine()->getManager();
                $em->persist($serviceTime);
            }
        }
    }

    /**
     * @param Service $service
     * @param $forms
     */
    private function addServiceForms(
        $service,
        $forms
    ) {
        $em = $this->getDoctrine()->getManager();

        if (!is_null($forms) && !empty($forms)) {
            foreach ($forms as $form) {
                $serviceForm = new ServiceForm();
                $serviceForm->setService($service);
                $serviceForm->setTitle($form['title']);
                $serviceForm->setType($form['type']);
                $em->persist($serviceForm);

                if (
                    isset($form['options'])
                    && !is_null($form['options'])
                    && !empty($form['options'])
                    && in_array($form['type'], array(ServiceForm::TYPE_CHECKBOX, ServiceForm::TYPE_RADIO))
                ) {
                    foreach ($form['options'] as $option) {
                        $serviceFormOption = new ServiceFormOption();
                        $serviceFormOption->setForm($serviceForm);
                        $serviceFormOption->setContent($option['content']);
                        $em->persist($serviceFormOption);
                    }
                }
            }
        }
        $em->flush();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkSalesAdminServicePermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_SERVICE,
                ),
            ),
            $opLevel
        );
    }
}