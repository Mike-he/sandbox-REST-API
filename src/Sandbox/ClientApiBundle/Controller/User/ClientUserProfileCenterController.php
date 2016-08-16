<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Rest controller for UserProfileCenter.
 *
 * @category Sandbox
 *
 * @author   FENG LI <feng.li@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientUserProfileCenterController extends UserProfileController
{
    /**
     * Get User Profile Center.
     *
     * @Route("/profile/center")
     * @Method({"GET"})
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function getProfileCenterAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $language = $request->getPreferredLanguage();

        $userCenterRepo = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserProfileCenter');
        $center = $userCenterRepo->findCenter();

        $userCenter = array();
        foreach ($center as $c) {
            $userCenter[] = array(
                'name' => $this->get('translator')->trans('client.profile.'.$c->getName(), array(), null, $language),
                'icons' => $c->getIcons(),
                'url' => $c->getUrl(),
            );
        }

        $view = new View();
        $view->setData($userCenter);

        return $view;
    }
}
