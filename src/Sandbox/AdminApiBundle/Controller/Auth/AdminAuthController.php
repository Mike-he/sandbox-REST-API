<?php

namespace Sandbox\AdminApiBundle\Controller\Auth;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\AdminApiBundle\Controller\Traits\HandleAdminLoginDataTrait;
use Sandbox\ApiBundle\Constants\PlatformConstants;
use Sandbox\ApiBundle\Controller\Auth\AuthController;
use Sandbox\ApiBundle\Entity\Admin\AdminPlatform;
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
 * @see     http://www.Sandbox.cn/
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

        $etag = $request->headers->get('etag');

        // response for openfire
        if (is_null($platform)) {
            return new View(array(
                'id' => $this->getUser()->getUserId(),
                'client_id' => $this->getUser()->getClientId(),
                'xmpp_username' => $this->getUser()->getMyUser()->getXmppUsername(),
            ));
        }

        // response my permissions
        if ($platform !== 'official' && $platform !== 'commnue') {
            if (is_null($salesCompanyId)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
        }

        $condition = $this->hasSuperAdminPosition(
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

            if ($platform == PlatformConstants::PLATFORM_SALES) {
                // set sales platform monitoring permissions
                $salesPlatformMonitoringPermissions = $this->getSalesPlatformMonitoringPermissions(
                    $platform,
                    $salesCompanyId
                );

                if (!is_null($salesPlatformMonitoringPermissions) && !empty($salesPlatformMonitoringPermissions)) {
                    $permissions = array_merge($permissions, $salesPlatformMonitoringPermissions);
                }
            }
        }

        $admin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->find($adminId);

        $salesAdmin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
            ->findOneBy(array('userId' => $adminId));

        $adminProfile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
            ->findOneBy([
                'userId' => $adminId,
                'salesCompanyId' => $salesCompanyId,
            ]);

        if (is_null($adminProfile)) {
            $adminProfile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                ->findOneBy([
                    'userId' => $adminId,
                    'salesCompanyId' => null,
                ]);
        }

        $name = !is_null($adminProfile) ? $adminProfile->getNickname() : '';

        $data = array(
            'permissions' => $this->remove_duplicate($permissions),
            'admin' => [
                'id' => $admin->getId(),
                'name' => $name,
                'phone' => $admin->getPhone(),
                'is_super_admin' => $condition,
                'client_id' => $this->getUser()->getClientId(),
                'xmpp_username' => $salesAdmin->getXmppUsername(),
                'xmpp_code' => $this->get('sandbox_api.des_encrypt')->encrypt($salesAdmin->getPassword()),
            ],
        );

        // return view
        $view = new View();

        $dataHash = hash('sha256', json_encode($data));

        // check hash
        if ($etag == $dataHash) {
            $view->setStatusCode(304);

            return $view;
        }

        $view->setHeader('etag', $dataHash);
        $view->setData($data);
        // response
        return $view;
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

        if ($isSuper || ($hasSalesMonitoringPermission && $platform == PlatformConstants::PLATFORM_SALES)) {
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
