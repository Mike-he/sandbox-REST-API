<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\Admin\AdminLogoutController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Logout controller.
 *
 * @category Sandbox
 *
 * @author   Albert Feng
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class AdminUserLogoutController extends AdminLogoutController
{
    /**
     * Logout.
     *
     * @param Request $request the request object
     *
     * @Route("/logout")
     * @Method({"POST"})
     *
     * @return string
     *
     * @throws \Exception
     */
    public function postAdminUserLogoutAction(
        Request $request
    ) {
        $adminId = $this->getAdminId();
        $clientId = $this->getUser()->getClientId();

        //delete Admin token
        $this->getRepo('User\UserToken')->deleteUserToken(
            $adminId,
            $clientId
        );

        setrawcookie(self::ADMIN_COOKIE_NAME, '', null, '/', $this->getParameter('top_level_domain'));

        return new View();
    }
}
