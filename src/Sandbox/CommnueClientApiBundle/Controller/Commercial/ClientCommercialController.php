<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Commercial;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\Advertising\AdvertisingController;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;

class ClientCommercialController extends AdvertisingController
{
    /**
     * Get Banners
     *
     * @param Request $request
     *
     * @Route("/commercial/banners")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBannersAction(
        Request $request
    ) {
        $parameter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array(
                'key'=>Parameter::KEY_COMMNUE_BANNER
            ));
        $limit = $parameter->getValue();
        $banners = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Banner\CommnueBanner')
            ->getClientBanner($limit);

        return new View($banners);
    }

    /**
     * Get Banner By Id
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/commercial/banners/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBannerByIdAction(
        Request $request,
        $id
    ) {
        $banner = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Banner\CommnueBanner')
            ->find($id);

        $this->throwNotFoundIfNull($banner,self::NOT_FOUND_MESSAGE);

        return new View($banner);
    }

    /**
     * Get advertising middles
     *
     * @param Request $request
     *
     * @Route("/commercial/middles")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMiddlesAction(
        Request $request
    ) {
        $parameter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array(
                'key'=>Parameter::KEY_COMMNUE_ADVERTISING_MIDDLE
            ));
        $limit = $parameter->getValue();
        $middles = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMiddle')
            ->getClientMiddle($limit);

        return new View($middles);
    }

    /**
     * Get Advertising Middle By Id
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/commercial/middles/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMiddlesByIdAction(
        Request $request,
        $id
    ) {
        $middle = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMiddle')
            ->find($id);

        $this->throwNotFoundIfNull($middle,self::NOT_FOUND_MESSAGE);

        return new View($middle);
    }

    /**
     * Get advertising micros
     *
     * @param Request $request
     *
     * @Route("/commercial/micros")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMicrosAction(
        Request $request
    ) {
        $parameter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array(
                'key'=>Parameter::KEY_COMMNUE_ADVERTISING_MICRO
            ));
        $limit = $parameter->getValue();
        $micros = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMicro')
            ->getClientMicro($limit);

        return new View($micros);
    }

    /**
     * Get Advertising Micro By Id
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/commercial/micros/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMicrosByIdAction(
        Request $request,
        $id
    ) {
        $micro = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMicro')
            ->find($id);

        $this->throwNotFoundIfNull($micro,self::NOT_FOUND_MESSAGE);

        return new View($micro);
    }
}