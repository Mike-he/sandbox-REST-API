<?php

namespace Sandbox\AdminApiBundle\Controller\Auth;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\AdminApiBundle\Controller\Traits\HandleAdminLoginDataTrait;
use Sandbox\ApiBundle\Controller\Auth\AuthController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Admin Auth controller.
 *
 * @category Sandbox
 *
 * @author   Albert Feng <albert.f@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminAuthController extends AuthController
{
    use HandleAdminLoginDataTrait;

    /**
     * Token auth.
     *
     * @param Request $request the request object
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
        $adminId = $this->getAdminId();
        $platform = $request->query->get('platform');
        $salesCompanyId = $request->query->get('sales_company_id');

        // response for openfire
        if (is_null($platform)) {
            return new View(array(
                'id' => $this->getUser()->getUserId(),
                'client_id' => $this->getUser()->getClientId(),
                'xmpp_username' => $this->getUser()->getMyUser()->getXmppUsername(),
            ));
        }

        // response my permissions
        if ($platform !== 'official') {
            if (is_null($salesCompanyId)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
        }

        $condition = $this->get('sandbox_api.admin_permission_check_service')
            ->hasSuperAdminPosition(
            $adminId,
            $platform,
            $salesCompanyId
        );

        if ($condition) {
            $permissions = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermission')
                ->findSuperAdminPermissionsByPlatform(
                    $platform,
                    $salesCompanyId
                );
        } else {
            $permissions = $this->getMyAdminPermissions(
                $adminId,
                $platform,
                $salesCompanyId
            );

            // set sales platform monitoring permissions
            $salesPlatformMonitoringPermissions = $this->getSalesPlatformMonitoringPermissions(
                $platform,
                $salesCompanyId
            );

            if (!is_null($salesPlatformMonitoringPermissions) && !empty($salesPlatformMonitoringPermissions)) {
                $permissions = array_merge($permissions, $salesPlatformMonitoringPermissions);
            }
        }

        $admin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->find($adminId);

        // response
        return new View(
            array(
                'permissions' => $this->remove_duplicate($permissions),
                'admin' => [
                    'id' => $admin->getId(),
                    'name' => $admin->getName(),
                    'phone' => $admin->getPhone(),
                    'is_super_admin' => $condition,
                    'client_id' => $this->getUser()->getClientId(),
                    'xmpp_username' => $this->getUser()->getMyUser()->getXmppUsername(),
                ],
            )
        );
    }

    /**
     * GET positions of platform when admin refresh login page.
     *
     * @Route("/platform")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdminAuthPlatformAction()
    {
        $myAdminId = $this->getAdminId();

        $positions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->findPositionByAdmin($myAdminId);

        $platform = $this->handlePositionData($positions);

        $companyInfo = $this->handleCompanyData($positions);

        // response
        $view = new View();

        return $view->setData(
            array(
                'platform' => $platform,
                'company' => $companyInfo,
            )
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/groups")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdminPermissionGroupsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminId = $this->getAdminId();

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $platform = $adminPlatform['platform'];

        $isSuper = $this->hasSuperAdminPosition(
            $adminId,
            $platform,
            $salesCompanyId
        );

        // check permission by sales monitoring permission
        $hasSalesMonitoringPermission = $this->checkSalesMonitoringPermission(
            $platform
        );

        if ($isSuper || $hasSalesMonitoringPermission) {
            $groups = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
                ->getPermissionGroupByPlatform(
                    $platform,
                    $salesCompanyId
                );
        } else {
            $groups = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
                ->getMyPermissionGroups(
                    $adminId,
                    $platform,
                    $salesCompanyId
                );
        }

        return new View($groups);
    }
}
