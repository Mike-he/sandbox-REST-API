<?php

namespace Sandbox\ApiBundle\Controller\Building;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingServices;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingTag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BuildingController.
 */
class BuildingController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/building/services")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBuildingServicesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $services = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingServices')
            ->findAll();

        $language = $request->getPreferredLanguage();

        foreach ($services as $service) {
            $serviceText = $this->get('translator')->trans(
                RoomBuildingServices::TRANS_PREFIX.$service->getName(),
                array(),
                null,
                $language
            );
            $service->setName($serviceText);
        }

        $view = new View($services);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['list']));

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/building/tags")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBuildingTagsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $tags = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingTag')
            ->findAll();

        $language = $request->getPreferredLanguage();

        foreach ($tags as $tag) {
            $serviceText = $this->get('translator')->trans(
                RoomBuildingTag::TRANS_PREFIX.$tag->getName(),
                array(),
                null,
                $language
            );
            $tag->setName($serviceText);
        }

        $view = new View($tags);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['list']));

        return $view;
    }
}
