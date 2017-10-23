<?php
namespace Sandbox\ClientPropertyApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ClientAdminLogoutController extends SandboxRestController
{
    /**
     * Logout.
     *
     * @param Request $request the request object
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
    }
}