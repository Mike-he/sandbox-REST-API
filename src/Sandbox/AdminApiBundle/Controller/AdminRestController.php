<?php

namespace Sandbox\AdminApiBundle\Controller;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Sandbox\ApiBundle\Entity\Admin\Admin;

class AdminRestController extends SandboxRestController
{
    /**
     * @return Admin
     *
     * @throws UnauthorizedHttpException
     */
    protected function checkAdminLoginSecurity()
    {
        $auth = $this->getSandboxAuthorization();

        $admin = $this->getRepo('Admin\Admin')->findOneBy(array(
            'username' => $auth->getUsername(),
            'password' => $auth->getPassword(),
        ));

        if (is_null($admin)) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        return $admin;
    }
}
