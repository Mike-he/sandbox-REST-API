<?php

namespace Sandbox\AdminShopApiBundle\Controller\Admin;

use Sandbox\AdminShopApiBundle\Controller\ShopRestController;
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
 * @author   Mike He  <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminUserLogoutController extends ShopRestController
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
        $this->getRepo('Shop\ShopAdminToken')->deleteShopAdminToken(
            $adminId,
            $clientId
        );
    }
}
