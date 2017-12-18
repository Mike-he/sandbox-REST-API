<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Commercial;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\Advertising\AdvertisingController;
use Sandbox\ApiBundle\Entity\Banner\CommnueBanner;
use Sandbox\ApiBundle\Entity\Material\CommnueMaterial;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;
use FOS\RestBundle\Controller\Annotations;

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

        $results = [];
        foreach($banners as $banner){
            $results[] = $this->handleSource($banner);
        }

        return new View($results);
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

        $results = [];
        foreach ($middles as $middle){
            $results[] = $this->handleSource($middle);
        }

        return new View($results);
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

    /**
     * Get Material By Id
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/commercial/material/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMaterialByIdAction(
        Request $request,
        $id
    ) {
        $material = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Material\CommnueMaterial')
            ->find($id);

        $this->throwNotFoundIfNull($material,self::NOT_FOUND_MESSAGE);

        return new View($material);
    }

    /**
     * Get Advertising Screen
     *
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="height",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="height"
     * )
     *
     * @Annotations\QueryParam(
     *    name="width",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="width"
     * )
     *
     * @Route("/commercial/screens")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdvertisingScreenAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $parameter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array(
                'key'=>Parameter::KEY_COMMNUE_ADVERTISING_SCREEN
            ));
        $limit = $parameter->getValue();

        $height = $paramFetcher->get('height');
        $width = $paramFetcher->get('width');

        $screens = $this->getDoctrine()
            ->getRepository("SandboxApiBundle:Advertising\CommnueAdvertisingScreen")
            ->getVisibleScreens($limit);

        if (!is_null($screens)) {
            foreach ($screens as $screen){
                $attachment = $this->getDoctrine()->getRepository("SandboxApiBundle:Advertising\CommnueScreenAttachment")->findAttachment($screen, $height, $width);
                $screen->setAttachments($attachment);
            }
        }

        $view = new View($screens);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('client_list'))
        );

        return $view;
    }

    /**
     * GET Advertising Screen By Id
     *
     * @param $id
     *
     * @Route("/commercial/screens/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getScreenByIdAction(
        $id
    ) {
        $screen = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingScreen')
            ->find($id);

        $this->throwNotFoundIfNull($screen,self::NOT_FOUND_MESSAGE);

        $attachments = $this->getDoctrine()->getRepository('SandboxApiBundle:Advertising\CommnueScreenAttachment')->findByScreen($screen);
        $screen->setAttachments($attachments);

        $view = new View($screen);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('main'))
        );

        return $view;
    }

    /**
     * @param $item
     * @return array
     */
    private function handleSource(
        $item
    ) {
        $data = [];

        $data['id'] = $item->getId();
        $data['title'] = $item->getTitle();
        $data['source'] = $item->getSource();
        $data['cover'] = $item->getCover();

        $source = $item->getSource();

        switch ($source){
            case 'material':
                $sourceId = $item->getSourceId();
                $url = $this->getParameter('mobile_url');
                $data['url'] = $url.'/materials?ptype=detail&source_id='.$sourceId;
                break;
            case 'event':
                $sourceId = $item->getSourceId();
                $url = $this->getParameter('mobile_url');
                $data['url'] = $url.'/event?ptype=detail&id='.$sourceId;
                break;
            default:
                $data['url'] = $item->getContent();
                break;
        }

        return $data;
    }
}