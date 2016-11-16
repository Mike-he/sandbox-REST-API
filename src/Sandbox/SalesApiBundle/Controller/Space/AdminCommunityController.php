<?php

namespace Sandbox\SalesApiBundle\Controller\Space;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AdminCommunityController.
 */
class AdminCommunityController extends SalesRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/administrative_region")
     * @Method({"GET"})
     *
     * @Annotations\QueryParam(
     *    name="parent",
     *    default=null,
     *    nullable=false,
     *    description="parent id"
     * )
     *
     * @return View
     */
    public function getAdministrativeRegionsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $parentId = $paramFetcher->get('parent');

        $regions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findBy(array(
                'parentId' => $parentId,
            ));

        $response = array();
        foreach ($regions as $region) {
            array_push($response, array(
                'id' => $region->getId(),
                'name' => $region->getName(),
            ));
        }

        return new View($response);
    }
}
