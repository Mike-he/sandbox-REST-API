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
    const CLIENT_PROFILE_PREFIX = 'client.profile.';

    /**
     * Get User Profile Center.
     *
     * @Route("/profile/myorder")
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

        $center = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserProfileCenter')->findCenter();

        foreach ($center as $c) {
            $serviceText = $this->get('translator')->trans(
                self::CLIENT_PROFILE_PREFIX.$c->getName(),
                array(),
                null,
                $language
            );
            $c->setName($serviceText);
        }

        $view = new View();
        $view->setData($center);

        return $view;
    }
}
