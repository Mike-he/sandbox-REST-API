<?php

namespace Sandbox\SalesApiBundle\Controller\GenericList;

use FOS\RestBundle\View\View;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;

class AdminGenericListController extends SalesRestController
{
    /**
     * Get Lease Clues.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="object",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    description="object name"
     * )
     *
     * @Route("/generic/lists")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getCluesListsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];

        $object = $paramFetcher->get('object');

        $lists = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:GenericList\GenericList')
            ->findBy(
              array(
                  'object' => $object,
                  'platform' => $platform,
              )
            );

        return new View($lists);
    }
}
