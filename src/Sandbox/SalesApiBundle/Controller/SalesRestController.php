<?php

namespace Sandbox\SalesApiBundle\Controller;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermissionMap;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminType;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;

class SalesRestController extends SandboxRestController
{
    //-------------------- check sales admin permission --------------------//

    /**
     * Check sales admin's permission, is allowed to operate.
     *
     * @param int          $adminId
     * @param string       $typeKey
     * @param string|array $permissionKeys
     * @param int          $opLevel
     * @param int          $buildingId
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

            // judge by global permission and building permission
            $filters = array(
                'adminId' => $adminId,
                'permissionId' => $permission->getId(),
            );
            if (!is_null($buildingId)) {
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
     * @param $permissionKeyArray
     * @param $opLevel
     *
     * @return array
     */
    protected function getMySalesBuildingIds(
        $adminId,
        $permissionKeyArray,
        $opLevel = SalesAdminPermissionMap::OP_LEVEL_VIEW
    ) {
        // get admin
        $admin = $this->getRepo('SalesAdmin\SalesAdmin')->find($adminId);
        $type = $admin->getType();

        // get permission
        if (empty($permissionKeyArray)) {
            return array();
        }

        $permissions = array();
        if (is_array($permissionKeyArray)) {
            foreach ($permissionKeyArray as $key) {
                $permission = $this->getRepo('SalesAdmin\SalesAdminPermission')->findOneByKey($key);

                if (!is_null($permission)) {
                    array_push($permissions, $permission->getId());
                }
            }
        }

        if (SalesAdminType::KEY_SUPER === $type->getKey()) {
            // if user is super admin, get all buildings
            $myBuildings = $this->getRepo('Room\RoomBuilding')->getBuildingsByCompany($admin->getCompanyId());
        } else {
            // platform admin get binding buildings
            $myBuildings = $this->getRepo('SalesAdmin\SalesAdminPermissionMap')->getMySalesBuildings(
                $adminId,
                $permissions,
                $opLevel
            );
        }

        if (empty($myBuildings)) {
            return $myBuildings;
        }

        $ids = array();
        foreach ($myBuildings as $building) {
            array_push($ids, $building['id']);
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
        $auth = $this->getSandboxAuthorization();

        $admin = $this->getRepo('SalesAdmin\SalesAdmin')->findOneBy(array(
            'username' => $auth->getUsername(),
            'password' => $auth->getPassword(),
        ));

        if (is_null($admin)) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        return $admin;
    }
}
