<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * User Account controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientUserAccountController extends SandboxRestController
{
    /**
     * Get my user account.
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
     * @Method({"GET"})
     * @Route("/account")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getUserAccountAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $userView = $this->getRepo('User\UserView')->find($userId);
        $view = new View();
        $view->setData(
            array(
                'id' => $userId,
                'name' => $userView->getName(),
                'email' => $userView->getEmail(),
                'phone' => $userView->getPhone(),
            )
        );

        return $view;
    }
}
