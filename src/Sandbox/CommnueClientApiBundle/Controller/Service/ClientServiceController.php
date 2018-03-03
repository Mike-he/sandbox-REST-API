<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Service;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Service\ViewCounts;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;

class ClientServiceController extends SandboxRestController
{
    /**
     * Get all client services.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     *    name="country",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     * )
     *
     * @Annotations\QueryParam(
     *    name="province",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    description="provinceId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    description="cityId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="district",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    description="districtId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="services typeId"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="sort",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="sort string"
     * )
     *
     * @Route("/services")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throw \Exception
     */
    public function getServicesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $country = $paramFetcher->get('country');
        $city = $paramFetcher->get('city');
        $province = $paramFetcher->get('province');
        $district = $paramFetcher->get('district');
        $type = $paramFetcher->get('type');
        $sort = $paramFetcher->get('sort');

        $services = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\Service')
            ->getClientServices(
                $country,
                $province,
                $city,
                $district,
                $type,
                $sort,
                $limit,
                $offset
            );

        foreach ($services as $service) {
            $salesCompany = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
                ->getCompanyInfo($service->getSalesCompanyId());

            $service->setSalesCompany($salesCompany);

            $attachments = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Service\ServiceAttachment')
                ->findBy(['service' => $service]);
            $times = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Service\ServiceTime')
                ->findBy(['service' => $service]);

            $service->setAttachments($attachments);
            $service->setTimes($times);
        }

        return new View($services);
    }

    /**
     * @param $id
     *
     * @Route("/services/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getServicesByIdAction(
        $id
    ) {
        $service = $this->getDoctrine()->getManager()
            ->getRepository('SandboxApiBundle:Service\Service')
            ->find($id);

        $this->throwNotFoundIfNull($service, self::NOT_FOUND_MESSAGE);

        $result = [];
        $attachments = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceAttachment')
            ->findBy(['service' => $service]);
        $times = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceTime')
            ->findBy(['service' => $service]);
        $forms = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceForm')
            ->findBy(['service' => $service]);

        $province = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomCity')
            ->find($service->getProvinceId())
            ->getName();
        $city = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomCity')
            ->find($service->getCityId())
            ->getName();
        $district = '';
        if ($service->getDistrictId()) {
            $district = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($service->getDistrictId())
                ->getName();
        }

        $addresss = $province.$city.$district;

        $service->setAttachments($attachments);
        $service->setForms($forms);
        $service->setTimes($times);
        $service->setAddress($addresss);
        $result['service'] = $service;

        $salesCompanyId = $service->getSalesCompanyId();
        if($salesCompanyId) {
            $salesCompany = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
                ->find($salesCompanyId);
        }

        $result['sales_company_name'] = $salesCompanyId ? $salesCompany->getName() : '';

        $result['order_id'] = '';
        $result['like'] = false;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
            $order = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Service\ServiceOrder')
                ->getUserLastOrder(
                    $userId,
                    $id
                );
            if (!is_null($order)) {
                $result['order_id'] = $order->getId();
            }

            $favorite = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserFavorite')
                ->findOneBy(
                    [
                        'userId' => $userId,
                        'object' => UserFavorite::OBJECT_SERVICE,
                        'objectId' => $id,
                    ]
                );

            $result['like'] = $favorite ? true : false;
        }

        $serviceMember = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
            ->findOneBy([
                'companyId' => $service->getSalesCompanyId(),
                'tag' => 'service',
            ]);

        $result['service_member'] = $serviceMember ? true : false;

        $orderUrl = $this->getParameter('orders_url');
        $wxShareUrl = $orderUrl.'/service?serviceId='.$service->getId().'&ptype=share&theme=blue';

        $result['wx_share_url'] = $wxShareUrl;

        $this->get('sandbox_api.view_count')->autoCounting(
            ViewCounts::OBJECT_SERVICE,
            $id,
            ViewCounts::TYPE_VIEW
        );

        return new View($result);
    }

    /**
     * @param Request $request
     *
     * @Route("/servicesTypes")
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
}
