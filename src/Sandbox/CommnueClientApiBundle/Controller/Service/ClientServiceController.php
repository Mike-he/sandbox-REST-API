<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Service;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Service\ViewCounts;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
     *    description="services typeId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    description="services typeId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="district",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    description="services typeId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="string",
     *    description="services typeId"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="sort",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="string",
     *    description="services typeId"
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
            $attachments = $this->getRepo('Service\ServiceAttachment')->findByService($service);
            $times = $this->getRepo('Service\ServiceTime')->findByService($service);

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
        $userId = $this->getUserId();

        $service = $this->getDoctrine()->getManager()
            ->getRepository('SandboxApiBundle:Service\Service')
            ->find($id);

        if (is_null($service)) {
            $this->throwNotFoundIfNull($service, self::NOT_FOUND_MESSAGE);
        }

        $result = [];
        $attachment = $this->getRepo('Service\ServiceAttachment')->findByService($service);
        $forms = $this->getRepo('Service\ServiceForm')->findByService($service);
        $times = $this->getRepo('Service\ServiceTime')->findByService($service);

        $city = $this->getRepo('Room\RoomCity')->find($service->getCityId())->getName();
        $district = $this->getRepo('Room\RoomCity')->find($service->getDistrictId())->getName();

        $addresss = $city.$district;

        $service->setAttachments($attachment);
        $service->setForms($forms);
        $service->setTimes($times);
        $service->setAddress($addresss);
        $result['service'] = $service;
        $result['like'] =  $favorite = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserFavorite')
            ->findOneBy(
                [
                    'userId' => $userId,
                    'object' => UserFavorite::OBJECT_SERVICE,
                    'objectId' => $id,
                ]
            );

        $viewCount = $this->getRepo('Service\ViewCounts')->findOneBy(array(
            'object' => ViewCounts::OBJECT_SERVICE,
            'objectId' => $id,
            'type' => ViewCounts::TYPE_VIEW
        ));

        $em = $this->getDoctrine()->getManager();
        if(is_null($viewCount)){
            $viewCount = new ViewCounts();
            $viewCount->setObject(ViewCounts::OBJECT_SERVICE);
            $viewCount->setObjectId($id);
            $viewCount->setType(ViewCounts::TYPE_VIEW);
            $viewCount->setCount(1);
            $em->persist($viewCount);
        }else{
            $count = $viewCount->getCount()+1;
            $viewCount->setCount($count);
        }

        $em->flush();

        return new View($result);
    }
}
