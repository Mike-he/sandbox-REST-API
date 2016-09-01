<?php

namespace Sandbox\AdminApiBundle\Controller\Banner;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AdminBannerTagController.
 */
class AdminBannerTagController extends AdminBannerController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/banner/tags")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBannerTagsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $tags = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Banner\BannerTag')
            ->findAll();

        foreach ($tags as $tag) {
            $trans = $this->container->get('translator')->trans(self::BANNER_TRANS_PREFIX.$tag->getName());

            $tag->setName($trans);
        }

        return new View($tags);
    }
}
