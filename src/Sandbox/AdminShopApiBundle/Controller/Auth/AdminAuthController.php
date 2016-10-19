<?php

namespace Sandbox\AdminShopApiBundle\Controller\Auth;

use Sandbox\ApiBundle\Controller\Auth\AuthController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Admin Auth controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminAuthController extends AuthController
{
    /**
     * Token auth.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/me")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminAuthMeAction(
        Request $request
    ) {
        $myAdminId = $this->getAdminId();

        // response
        $view = new View(array(
            'id' => $myAdminId,
            'client_id' => $this->getUser()->getClientId(),
        ));

        return $view;
    }
}
