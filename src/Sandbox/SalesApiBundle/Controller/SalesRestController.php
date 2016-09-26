<?php

namespace Sandbox\SalesApiBundle\Controller;

use Sandbox\AdminApiBundle\Controller\Admin\AdminPlatformController;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class SalesRestController extends SandboxRestController
{
    const SALES_BUILDING_PERMISSION_PREFIX = 'sales.building';

    /**
     * @param $adminId
     * @param $permissionKeys
     * @param $opLevel
     *
     * @return array
     */
    protected function getMySalesBuildingIds(
        $adminId,
        $permissionKeys,
        $opLevel = AdminPermission::OP_LEVEL_VIEW
    ) {
        // get permission
        if (empty($permissionKeys)) {
            return array();
        }

        // get platform cookies
        $adminPlatformCookieName = AdminPlatformController::COOKIE_NAME_PLATFORM;
        $salesCompanyCookieName = AdminPlatformController::COOKIE_NAME_SALES_COMPANY;
        $platform = $_COOKIE[$adminPlatformCookieName];
        $salesCompanyId = isset($_COOKIE[$salesCompanyCookieName]) ? $_COOKIE[$salesCompanyCookieName] : null;

        $isSuperAdmin = $this->hasSuperAdminPosition(
            $adminId,
            $platform,
            $salesCompanyId
        );

        if ($isSuperAdmin) {
            // if user is super admin, get all buildings
            $myBuildings = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->getBuildingsByCompany($salesCompanyId);

            if (empty($myBuildings)) {
                return $myBuildings;
            }

            $ids = array();
            foreach ($myBuildings as $building) {
                array_push($ids, $building['id']);
            }

            return $ids;
        }

        // if common admin, than get my permissions list
        $myPermissions = $this->getMyAdminPermissions(
            $adminId,
            $platform,
            $salesCompanyId
        );

        $ids = array();
        foreach ($permissionKeys as $permissionKey) {
            foreach ($myPermissions as $myPermission) {
                if ($permissionKey == $myPermission['key']
                    && $opLevel <= $myPermission['op_level']
                    && !is_null($myPermission['building_id'])
                ) {
                    array_push($ids, $myPermission['building_id']);
                }
            }
        }

        return $ids;
    }

    /**
     * @return SalesAdmin
     *
     * @throws UnauthorizedHttpException
     */
    protected function checkSalesAdminLoginSecurity()
    {
        $auth = $this->getSandboxAuthorization(self::SANDBOX_ADMIN_LOGIN_HEADER);

        $admin = $this->getRepo('SalesAdmin\SalesAdmin')->findOneBy(array(
            'username' => $auth->getUsername(),
            'password' => $auth->getPassword(),
        ));

        if (is_null($admin)) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        return $admin;
    }

    /**
     * @return mixed
     */
    protected function getSalesCompanyId()
    {
        return $this->getUser()->getMyAdmin()->getSalesCompany()->getId();
    }
}
