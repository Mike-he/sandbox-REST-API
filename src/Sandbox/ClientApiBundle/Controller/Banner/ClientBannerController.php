<?php

namespace Sandbox\ClientApiBundle\Controller\Banner;

use Sandbox\ApiBundle\Controller\Banner\BannerController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;

/**
 * Rest controller for Client Banner.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientBannerController extends BannerController
{
    /**
     * Get list of banners.
     *
     * @Route("/banners")
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function getBannersAction(
        Request $request
    ) {
        $banners = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Banner\Banner')
            ->getClientBannerList();

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_list']));
        $view->setData($banners);

        return $view;
    }

    /**
     * Get list of banners.
     *
     * @Route("/banners/carousel")
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function getBannerCarouselAction(
        Request $request
    ) {
        $bannerTop = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array('key' => 'banner_top'));
        $limit = $bannerTop->getValue();

        $banners = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Banner\Banner')
            ->getLimitList($limit, 0);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_list']));
        $view->setData($banners);

        return $view;
    }

    /**
     * Get Banner By Id.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/banners/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBannerByIdAction(
        Request $request,
        $id
    ) {
        // get banner
        $banner = $this->getRepo('Banner\Banner')->find($id);
        $this->throwNotFoundIfNull($banner, self::NOT_FOUND_MESSAGE);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_list']));
        $view->setData($banner);

        return $view;
    }
}
