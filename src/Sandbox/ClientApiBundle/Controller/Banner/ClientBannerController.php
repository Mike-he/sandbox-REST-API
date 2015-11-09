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
        $banners = $this->getRepo('Banner\Banner')->getBannerList();

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_list']));
        $view->setData($banners);

        return $view;
    }
}