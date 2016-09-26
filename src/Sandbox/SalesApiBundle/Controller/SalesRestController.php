<?php

namespace Sandbox\SalesApiBundle\Controller;

use Sandbox\AdminApiBundle\Controller\Admin\AdminPlatformController;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermissionMap;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminType;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;

class SalesRestController extends SandboxRestController
{
    const SALES_BUILDING_PERMISSION_PREFIX = 'sales.building';

    //-------------------- check sales admin permission --------------------//

    /**
     * Check sales admin's permission, is allowed to operate.
     *
     * @param int    $adminId
     * @param string $typeKey
     * @param array  $permissionKeys
     * @param int    $opLevel
     * @param int    $buildingId
     *
     * @throws AccessDeniedHttpException
     */
    protected function throwAccessDeniedIfSalesAdminNotAllowed(
        $adminId,
        $typeKey,
        $permissionKeys = null,
        $opLevel = SalesAdminPermissionMap::OP_LEVEL_VIEW,
        $buildingId = null
    ) {
        $myPermission = null;

        // get admin
        $admin = $this->getRepo('SalesAdmin\SalesAdmin')->find($adminId);
        $type = $admin->getType();

        // first check if user is super admin, no need to check others
        if (SalesAdminType::KEY_SUPER === $type->getKey()) {
            return;
        }

        // if admin type doesn't match, then throw exception
        if ($typeKey != $type->getKey()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // check permission key array
        if (is_null($permissionKeys) || empty($permissionKeys) || !is_array($permissionKeys)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        foreach ($permissionKeys as $permissionKey) {
            $permission = $this->getRepo('SalesAdmin\SalesAdminPermission')->findOneByKey($permissionKey);
            if (is_null($permission)) {
                continue;
            }

            $filters = array(
                'adminId' => $adminId,
                'permissionId' => $permission->getId(),
            );

            $key = $permission->getKey();
            $keyArray = explode(self::SALES_BUILDING_PERMISSION_PREFIX, $key);
            if (count($keyArray) > 1 && !is_null($buildingId)) {
                // judge by global permission and building permission
                $filters['buildingId'] = $buildingId;
            }

            // check user's permission
            $myPermission = $this->getRepo('SalesAdmin\SalesAdminPermissionMap')
                ->findOneBy($filters);
            if (!is_null($myPermission) && $myPermission->getOpLevel() >= $opLevel) {
                return;
            }
        }

        throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
    }

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
