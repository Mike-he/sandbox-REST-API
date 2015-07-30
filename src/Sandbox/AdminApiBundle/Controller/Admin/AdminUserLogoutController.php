<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Controller\Admin\AdminLogoutController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Logout controller.
 *
 * @category Sandbox
 *
 * @author   Albert Feng
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminUserLogoutController extends AdminLogoutController
{
    /**
     * Logout.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "NO CONTENT"
     *  }
     * )
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
        $this->getRepo('Admin\AdminToken')->deleteAdminToken(
            $adminId,
            $clientId
        );
    }
}
