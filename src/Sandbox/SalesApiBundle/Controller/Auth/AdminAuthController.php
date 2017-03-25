<?php

namespace Sandbox\SalesApiBundle\Controller\Auth;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Auth\AuthController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroups;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/exclude_permissions")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesExcludePermissionsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $salesCompanyId = $adminPlatform['sales_company_id'];

        if ($platform != AdminPermissionGroups::GROUP_PLATFORM_SALES) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $excludePermissions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminExcludePermission')
            ->findBy(array(
                'salesCompanyId' => $salesCompanyId,
            ));

        $response = array(
            'groups' => array(),
            'permissions' => array(),
        );
        foreach ($excludePermissions as $excludePermission) {
            $group = $excludePermission->getGroup();
            $permission = $excludePermission->getPermission();

            if (!is_null($group)) {
                array_push($response['groups'], array(
                    'id' => $group->getId(),
                ));
            }

            if (!is_null($permission)) {
                array_push($response['permissions'], array(
                    'id' => $permission->getId(),
                ));
            }
        }

        return new View($response);
    }

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
