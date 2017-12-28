<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;

class ClientServiceController extends SalesRestController
{
    /**
     * Get all client services.
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
        $dictrict = $paramFetcher->get('dictrict');
        $type = $paramFetcher->get('type');
        $sort = $paramFetcher->get('sort');

        $servicesArray = array();
        $services = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\Service')
            ->getClientServices(
                $country,
                $province,
                $city,
                $dictrict,
                $type,
                $sort,
                $limit,
                $offset
            );

        foreach ($services as $serviceArray) {
            $service = $serviceArray['service'];
            $attachments = $this->getRepo('Service\ServiceAttachment')->findByService($service);
            $times = $this->getRepo('Service\ServiceTime')->findByService($service);
            $forms = $this->getRepo('Service\ServiceForm')->findByService($service);

            $city = $this->getRepo('Room\RoomCity')->find($service->getCityId())->getName();
            $country = $this->getRepo('Room\RoomCity')->find($service->getCountryId())->getName();
            $province = $this->getRepo('Room\RoomCity')->find($service->getProvinceId())->getName();
            $district = $this->getRepo('Room\RoomCity')->find($service->getDistrictId())->getName();
            $addresss = $country.$province.$city.$district;
            $service->setAttachments($attachments);
            $service->setTimes($times);
            $service->setForms($forms);
            $service->setAddress($addresss);

            array_push($servicesArray, $service);
        }

        return new View($services);
    }
}