<?php

namespace Sandbox\SalesApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Logout controller.
 *
 * @category Sandbox
 *
 * @author   Mike He  <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminUserLogoutController extends SalesRestController
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
        $this->getRepo('SalesAdmin\SalesAdminToken')->deleteSalesAdminToken(
            $adminId,
            $clientId
        );
    }
}
