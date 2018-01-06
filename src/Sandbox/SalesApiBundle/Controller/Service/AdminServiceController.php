<?php

namespace Sandbox\SalesApiBundle\Controller\Service;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Service\Service;
use Sandbox\ApiBundle\Entity\Service\ServiceAttachment;
use Sandbox\ApiBundle\Entity\Service\ServiceForm;
use Sandbox\ApiBundle\Entity\Service\ServiceFormOption;
use Sandbox\ApiBundle\Entity\Service\ServiceOrder;
use Sandbox\ApiBundle\Entity\Service\ServiceTime;
use Sandbox\ApiBundle\Form\Service\ServicePatchType;
use Sandbox\ApiBundle\Form\Service\ServicePostType;
use Sandbox\ApiBundle\Form\Service\ServicePutType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Controller\Annotations;
use Rs\Json\Patch;

class AdminServiceController extends SalesRestController
{
    const ERROR_NOT_ALLOWED_MODIFY_CODE = 400001;
    const ERROR_NOT_ALLOWED_MODIFY_MESSAGE = 'Not allowed to modify - 不允许被修改';
    const ERROR_NOT_ALLOWED_DELETE_CODE = 400002;
    const ERROR_NOT_ALLOWED_DELETE_MESSAGE = 'Not allowed to delete - 不允许被删除';
    const ERROR_INVALID_LIMIT_NUMBER_CODE = 400003;
    const ERROR_INVALID_LIMIT_NUMBER_MESSAGE = 'Invalid limit number';
    const ERROR_INVALID_SERVICE_TIME_CODE = 400006;
    const ERROR_INVALID_SERVICE_TIME_MESSAGE = 'Service start time should before service end time';
    const ERROR_INVALID_SERVICE_PRICE_CODE = 400007;
    const ERROR_INVALID_SERVICE_PRICE_MESSAGE = 'Service can not be null while need charge';
    const ERROR_INVALID_PATCH_CODE = 400008;
    const ERROR_INVALID_PATCH_MESSAGE = 'Have uncompleted service order';
    const ERROR_SERVICE_STSRTDATE_CODE = 400009;
    const ERROR_SERVICE_STSRTDATE_MESSAGE = 'Service startDate should later than now';

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
     *    strict=true,
     *    description="services type"
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

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $services = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\Service')
            ->getSalesServices(
                $type,
                $visible,
                $salesCompanyId,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\Service')
            ->getSalesServiceCount(
                $type,
                $visible,
                $salesCompanyId
        );

        foreach ($services as $serviceArray) {

            /** @var Service $service */
            $service = $serviceArray['service'];

            $this->handleServiceInfo($service);
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $services,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/services/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getServicesByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkSalesAdminServicePermission(AdminPermission::OP_LEVEL_VIEW);

        $service = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\Service')
            ->findOneBy(array(
                'id' => $id,
                'salesCompanyId' => $this->getSalesCompanyId(),
            ));

        $this->throwNotFoundIfNull($service, self::NOT_FOUND_MESSAGE);

        $service = $this->handleServiceInfo($service);

        return new View($service);
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
    ) {
        $types = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceTypes')
            ->findAll();

        return new View($types);
    }

    /**
     * Create Service.
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
    ) {
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
     * @param Request $request
     * @param $id
     *
     * @Route("/services/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putServiceAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkSalesAdminServicePermission(AdminPermission::OP_LEVEL_EDIT);

        $service = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\Service')
            ->findOneBy(array(
                'id' => $id,
                'salesCompanyId' => $this->getSalesCompanyId(),
            ));

        if (is_null($service)) {
            $this->throwNotFoundIfNull($service, self::NOT_FOUND_MESSAGE);
        }

        // bind form
        $form = $this->createForm(
            new ServicePutType(),
            $service,
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
        if ($service->isCharge()) {
            if (is_null($service->getPrice())) {
                return $this->customErrorView(
                    400,
                    self::ERROR_INVALID_SERVICE_PRICE_CODE,
                    self::ERROR_INVALID_SERVICE_PRICE_MESSAGE
                );
            }
        } else {
            $service->setPrice(null);
        }

        // handle service form
        return $this->handleServicePut(
            $service,
            $submit
        );
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/services/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchServiceAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkSalesAdminServicePermission(AdminPermission::OP_LEVEL_EDIT);

        $service = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\Service')
            ->findOneBy(array(
                'id' => $id,
                'salesCompanyId' => $this->getSalesCompanyId(),
            ));

        $this->throwNotFoundIfNull($service, self::NOT_FOUND_MESSAGE);

        // bind data
        $serviceJson = $this->container->get('serializer')->serialize($service, 'json');
        $patch = new Patch($serviceJson, $request->getContent());
        $serviceJson = $patch->apply();

        $form = $this->createForm(new ServicePatchType(), $service);
        $form->submit(json_decode($serviceJson, true));

        // change save status
        return $this->checkPatchValid($service);
    }

    /**
     * Save service to db.
     *
     * @param Service $service
     * @param bool    $submit
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
        $limitNumber = (int) $service->getLimitNumber();

        // check service start time and end time
        if (!is_null($times) && !empty($times)) {
            foreach ($times as $time) {
                if ($time['start_time'] >= $time['end_time']) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_INVALID_SERVICE_TIME_CODE,
                        self::ERROR_INVALID_SERVICE_TIME_MESSAGE
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
     * @param $submit
     */
    private function addService(
        $service,
        $submit
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        $serviceStartDate = new \DateTime($service->getServiceStartDate());
        $serviceEndDate = new \DateTime($service->getServiceEndDate());

        // set price
        if (!$service->isCharge()) {
            $service->setPrice(0.00);
        }

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
    ) {
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
     * Save service modification to db.
     *
     * @param Service $service
     * @param         $submit
     *
     * @return View
     */
    private function handleServicePut(
        $service,
        $submit
    ) {
        $attachments = $service->getAttachments();
        $times = $service->getTimes();
        $serviceForms = $service->getForms();
        $limitNumber = (int) $service->getLimitNumber();

        // check service start time and end time
        if (!is_null($times) && !empty($times)) {
            foreach ($times as $time) {
                if ($time['start_time'] >= $time['end_time']) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_INVALID_SERVICE_TIME_CODE,
                        self::ERROR_INVALID_SERVICE_TIME_MESSAGE
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

        // modify services
        $this->modifyService(
            $service,
            $submit
        );

        // modify services attachments
        $this->modifyServiceAttachments(
            $service,
            $attachments
        );

        // modify services times
        $this->modifyServiceTimes(
            $service,
            $times
        );

        // modify services forms
        $this->modifyServiceForms(
            $service,
            $serviceForms
        );

        return new View();
    }

    /**
     * @param $service
     * @param $submit
     */
    private function modifyService(
        $service,
        $submit
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        $serviceStartDate = new \DateTime($service->getServiceStartDate());
        $serviceEndDate = new \DateTime($service->getServiceEndDate());

        // set price
        if (!$service->isCharge()) {
            $service->setPrice(0.00);
        }

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
     * @param $service
     * @param $attachments
     */
    private function modifyServiceAttachments(
        $service,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();

        if (!is_null($attachments) || !empty($attachments)) {
            $serviceAttachments = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Service\ServiceAttachment')
                ->findBy(['service' => $service]);
            foreach ($serviceAttachments as $serviceAttachment) {
                $em->remove($serviceAttachment);
            }

            $this->addServiceAttachments(
                $service,
                $attachments
            );
        }
    }

    /**
     * @param $service
     * @param $times
     */
    private function modifyServiceTimes(
        $service,
        $times
    ) {
        $em = $this->getDoctrine()->getManager();

        if (!is_null($times) || !empty($times)) {
            $serviceTimes = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Service\ServiceTime')
                ->findBy(['service' => $service]);
            foreach ($serviceTimes as $serviceTime) {
                $em->remove($serviceTime);
            }

            $this->addServiceTimes(
                $service,
                $times
            );
        }
    }

    /**
     * @param $service
     * @param $serviceForms
     */
    private function modifyServiceForms(
        $service,
        $serviceForms
    ) {
        $em = $this->getDoctrine()->getManager();

        // check if is valid to modify
        if (new \DateTime('now') >= $service->getServiceStartDate()) {
            $em->flush();

            return;
        }

        if (!is_null($serviceForms) || !empty($serviceForms)) {
            $serviceFormArray = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Service\ServiceForm')
                ->findBy(['service' => $service]);
            foreach ($serviceFormArray as $serviceForm) {
                $em->remove($serviceForm);
            }

            $this->addServiceForms(
                $service,
                $serviceForms
            );
        }
    }

    /**
     * @param Service $service
     *
     * @return mixed
     */
    private function handleServiceInfo(
        $service
    ) {
        $attachments = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceAttachment')
            ->findBy(['service' => $service]);
        $times = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceTime')
            ->findBy(['service' => $service]);
        $forms = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceForm')
            ->findBy(['service' => $service]);

        $city = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomCity')
            ->find($service->getCityId());
        $country = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomCity')
            ->find($service->getCountryId());
        $province = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomCity')
            ->find($service->getProvinceId());
        $cityName = $city->getName();
        $countryName = $country->getName();
        $provinceName = $province->getName();
        $districtName = '';
        if ($service->getDistrictId()) {
            $district = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($service->getDistrictId());
            $districtName = $district->getName();
            $service->setDistrict($district);
        }

        $now = new \DateTime();
        $startDate = $service->getServiceStartDate();
        $endDate = $service->getServiceEndDate();
        if ($startDate <= $now && $endDate >= $now) {
            $service->setStatus('ongoing');
        } elseif ($endDate < $now) {
            $service->setStatus('end');
        }

        $addresss = $countryName.$provinceName.$cityName.$districtName;
        $service->setAttachments($attachments);
        $service->setTimes($times);
        $service->setForms($forms);
        $service->setCountry($country);
        $service->setProvince($province);
        $service->setCity($city);
        $service->setAddress($addresss);

        $id = $service->getId();
        $purchaseNumber = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->getServicePurchaseCount($id);
        $service->setPurchaseNumber($purchaseNumber);

        return $service;
    }

    /**
     * @param Service $service
     *
     * @return View
     */
    private function checkPatchValid(
        $service
    ) {
        if ($service->isVisible()) {
            $now = new \DateTime();
            $startDate = $service->getServiceStartDate();
            if ($startDate < $now) {
                return $this->customErrorView(
                    400,
                    self::ERROR_SERVICE_STSRTDATE_CODE,
                    self::ERROR_SERVICE_STSRTDATE_MESSAGE
                );
            }

            $service->setIsSaved(false);
        }
        $orders = $this->getDoctrine()->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->findOneBy(array(
                'serviceId' => $service->getId(),
                'status' => ServiceOrder::STATUS_PAID,
            ));
        if (!is_null($orders)) {
            return $this->customErrorView(
                    400,
                    self::ERROR_INVALID_PATCH_CODE,
                    self::ERROR_INVALID_PATCH_MESSAGE
                );
        }

        $em = $this->getDoctrine()->getManager();
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
