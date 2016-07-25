<?php

namespace Sandbox\SalesApiBundle\Controller\Auth;

use Sandbox\ApiBundle\Controller\Auth\AuthController;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\Serializer\SerializationContext;

/**
 * Admin Auth controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
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
        $myAdmin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
            ->find($myAdminId);

        // select super admin permissions without auto ORM
        if ($myAdmin->getType()->getKey() == SalesAdminType::KEY_SUPER) {
            $permissions = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminPermission')
                ->getSalesAdminPermissions($myAdmin->getCompanyId());

            $adminJson = $this->container->get('serializer')->serialize($myAdmin, 'json');
            $adminArray = json_decode($adminJson, true);

            foreach ($permissions as $permission) {
                $adminArray['permissions'] = array('permission' => $permission);
            }

            $myAdmin = $adminArray;
        }

        // response
        $view = new View($myAdmin);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('auth')));

        return $view;
    }
}
