<?php

namespace Sandbox\ApiBundle\Service;

use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * Class AdminPermissionCheckService.
 */
class AdminPermissionCheckService
{
    const NOT_ALLOWED_MESSAGE = 'You are not allowed to perform this action';
    const ADMIN_COOKIE_NAME = 'sandbox_admin_token';
    const PRECONDITION_NOT_SET = 'The precondition not set';

    private $container;
    private $doctrine;
    private $user;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
        $this->doctrine = $container->get('doctrine');

        $token = $this->container->get('security.token_storage')->getToken();
        $this->user = isset($token) ? $token->getUser() : null;
    }

    /**
     * @param $adminId
     * @param null $permissionKeys
     * @param int  $opLevel
     * @param null $platform
     * @param null $salesCompanyId
     */
    public function checkPermissions(
        $adminId,
        $permissionKeys = null,
        $opLevel = 0,
        $platform = null,
        $salesCompanyId = null
    ) {
        if (is_null($platform)) {
            // get platform sessions
            $adminPlatform = $this->container->get('service_container')
                ->get('sandbox_api.admin_platform')
                ->getAdminPlatform();
            $platform = $adminPlatform['platform'];
            $salesCompanyId = $adminPlatform['sales_company_id'];
        }

        // super admin
        $isSuperAdmin = $this->hasSuperAdminPosition(
            $adminId,
            $platform,
            $salesCompanyId
        );

        if ($isSuperAdmin) {
            $myPermissions = $this->doctrine
                ->getRepository('SandboxApiBundle:Admin\AdminPermission')
                ->findSuperAdminPermissionsByPlatform(
                    $platform,
                    $salesCompanyId
                );

            // check permissions
            foreach ($permissionKeys as $permissionKey) {
                // check specify resource permission
                $this->checkSpecifyResourcePermissionIfSuperAdmin(
                    $permissionKey,
                    $salesCompanyId
                );

                $pass = false;
                foreach ($myPermissions as $myPermission) {
                    if ($permissionKey['key'] == $myPermission['key']
                        && $opLevel <= $myPermission['op_level']
                    ) {
                        $pass = true;
                    }

                    if ($pass) {
                        return;
                    }
                }
            }
        } else {
            // check permission by sales monitoring permission
            $hasSalesMonitoringPermission = $this->checkSalesMonitoringPermission(
                $platform,
                $adminId
            );

            if ($opLevel == AdminPermission::OP_LEVEL_VIEW && $hasSalesMonitoringPermission) {
                return;
            }

            // if common admin, than get my permissions list
            $myPermissions = $this->getMyAdminPermissions(
                $adminId,
                $platform,
                $salesCompanyId
            );

            // check permissions
            foreach ($permissionKeys as $permissionKey) {
                $buildingId = isset($permissionKey['building_id']) ? $permissionKey['building_id'] : null;
                $shopId = isset($permissionKey['shop_id']) ? $permissionKey['shop_id'] : null;

                foreach ($myPermissions as $myPermission) {
                    if ($permissionKey['key'] == $myPermission['key']
                        && $opLevel <= $myPermission['op_level']
                    ) {
                        if (!is_null($buildingId)) {
                            if ($buildingId == $myPermission['building_id']) {
                                return;
                            } else {
                                continue;
                            }
                        }

                        if (!is_null($shopId)) {
                            if ($shopId == $myPermission['shop_id']) {
                                return;
                            } else {
                                continue;
                            }
                        }

                        return;
                    }
                }
            }
        }

        throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
    }

    /**
     * @param $adminId
     * @param $platform
     * @param $salesCompanyId
     *
     * @return bool
     */
    public function hasSuperAdminPosition(
        $adminId,
        $platform,
        $salesCompanyId = null
    ) {
        $superAdminPositionBindings = $this->doctrine
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getPositionBindingsByIsSuperAdmin(
                $adminId,
                true,
                $platform,
                $salesCompanyId
            );

        if (count($superAdminPositionBindings) >= 1) {
            return true;
        }

        return false;
    }

    /**
     * @param string $platform
     * @param int    $salesCompanyId
     *
     * @return array
     */
    public function findSuperAdminPermissionsByPlatform(
        $platform,
        $salesCompanyId = null
    ) {
        $excludePermissionIds = $this->findAdminExcludePermissionIds($platform, $salesCompanyId);

        $permission = $this->doctrine->getManager()
            ->createQueryBuilder()
            ->select('
                ap.id,
                ap.name,
                ap.key,
                ap.maxOpLevel as op_level
            ')
            ->from('SandboxApiBundle:Admin\AdminPermission', 'ap')
            ->where('ap.platform = :platform')
            ->setParameter('platform', $platform);

        if (!empty($excludePermissionIds)) {
            $permission->andWhere('ap.id NOT IN (:excludePermissionIds)')
                ->setParameter('excludePermissionIds', $excludePermissionIds);
        }

        return $permission->getQuery()->getResult();
    }

    /**
     * @param $permissionKey
     * @param $salesCompanyId
     */
    public function checkSpecifyResourcePermissionIfSuperAdmin(
        $permissionKey,
        $salesCompanyId
    ) {
        if (isset($permissionKey['building_id'])) {
            $building = $this->doctrine
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($permissionKey['building_id']);

            if (is_null($building)) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }

            if ($building->getCompanyId() != $salesCompanyId) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }
        }

        if (isset($permissionKey['shop_id'])) {
            $shop = $this->doctrine
                ->getRepository('SandboxApiBundle:Shop\Shop')
                ->find($permissionKey['shop_id']);

            if (is_null($shop)) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }

            if ($shop->getBuilding()->getCompanyId() != $salesCompanyId) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }
        }
    }

    /**
     * @param $platform
     * @param $adminId
     *
     * @return bool
     */
    public function checkSalesMonitoringPermission(
        $platform,
        $adminId = null
    ) {
        if ($platform == AdminPermission::PERMISSION_PLATFORM_OFFICIAL) {
            return false;
        }

        if (is_null($adminId)) {
            $adminId = $this->user->getUserId();
        }

        $isOfficialSuperAdmin = $this->hasSuperAdminPosition(
            $adminId,
            AdminPermission::PERMISSION_PLATFORM_OFFICIAL
        );

        if ($isOfficialSuperAdmin) {
            return true;
        }

        $myOfficialPermissions = $this->getMyAdminPermissions(
            $adminId,
            AdminPermission::PERMISSION_PLATFORM_OFFICIAL
        );

        $salesMonitoringPermission = $this->doctrine
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES_MONITORING,
            ));

        if (is_null($salesMonitoringPermission)) {
            return false;
        }

        $salesMonitoringPermissionArray = array(
            'key' => $salesMonitoringPermission->getKey(),
            'op_level' => $salesMonitoringPermission->getMaxOpLevel(),
            'name' => $salesMonitoringPermission->getName(),
            'id' => $salesMonitoringPermission->getId(),
            'building_id' => null,
            'shop_id' => null,
        );

        if (in_array($salesMonitoringPermissionArray, $myOfficialPermissions)) {
            return true;
        }

        return false;
    }

    /**
     * @param $adminId
     * @param $platform
     * @param $salesCompanyId
     *
     * @return array
     */
    public function getMyAdminPermissions(
        $adminId,
        $platform,
        $salesCompanyId = null
    ) {
        $commonAdminPositionBindings = $this->doctrine
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getPositionBindingsByIsSuperAdmin(
                $adminId,
                false,
                $platform,
                $salesCompanyId
            );

        $myPermissions = array();
        foreach ($commonAdminPositionBindings as $binding) {
            $position = $binding->getPosition();

            $positionPermissionMaps = $this->doctrine
                ->getRepository('SandboxApiBundle:Admin\AdminPositionPermissionMap')
                ->findBy(array(
                    'position' => $position,
                ));

            foreach ($positionPermissionMaps as $map) {
                $permission = $map->getPermission();
                $permissionArray = array(
                    'key' => $permission->getKey(),
                    'op_level' => $map->getOpLevel(),
                    'building_id' => $binding->getBuildingId(),
                    'shop_id' => $binding->getShopId(),
                    'name' => $permission->getName(),
                    'id' => $permission->getId(),
                );

                array_push($myPermissions, $permissionArray);
            }
        }

        return $myPermissions;
    }

    /**
     * @param $platform
     * @param $salesCompanyId
     *
     * @return array
     */
    public function findAdminExcludePermissionIds($platform, $salesCompanyId)
    {
        // filter by exclude permission ids
        $excludePermissionIdsQuery = $this->doctrine->getManager()
            ->createQueryBuilder()
            ->select('ep.permissionId')
            ->from('SandboxApiBundle:Admin\AdminExcludePermission', 'ep')
            ->where('ep.platform = :platform')
            ->andWhere('ep.permissionId IS NOT NULL')
            ->setParameter('platform', $platform);

        if (!is_null($salesCompanyId)) {
            $excludePermissionIdsQuery
                ->andWhere('(ep.salesCompanyId = :salesCompanyId OR ep.salesCompanyId IS NULL)')
                ->setParameter('salesCompanyId', $salesCompanyId);
        }

        return array_map('current', $excludePermissionIdsQuery->getQuery()->getResult());
    }

    /**
     * @param $adminId
     */
    public function checkHasPosition(
        $adminId,
        $platform,
        $salesCompanyId
    ) {
        $bindings = $this->doctrine
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getBindingsByUser(
                $adminId,
                $platform,
                $salesCompanyId
            );

        if (count($bindings) >= 1) {
            return;
        }

        throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
    }

    /**
     * @param $permission
     * @param $platform
     *
     * @return array
     */
    public function checkPermissionByCookie(
        $permission,
        $platform
    ) {
        $cookie_name = self::ADMIN_COOKIE_NAME;
        if (!isset($_COOKIE[$cookie_name])) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $token = $_COOKIE[$cookie_name];
        $adminToken = $this->doctrine
            ->getRepository('SandboxApiBundle:User\UserToken')
            ->findOneBy(array(
                'token' => $token,
            ));
        if (is_null($adminToken)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $admin = $adminToken->getUser();
        $adminId = $admin->getId();

        $adminPlatform = $this->doctrine
            ->getRepository('SandboxApiBundle:Admin\AdminPlatform')
            ->findOneBy(array(
                'userId' => $adminId,
                'clientId' => $adminToken->getClientId(),
            ));
        if (is_null($adminPlatform)) {
            throw new PreconditionFailedHttpException(self::PRECONDITION_NOT_SET);
        }

        $companyId = $adminPlatform->getSalesCompanyId();

        // check user permission
        $this->checkPermissions(
            $adminId,
            [
                ['key' => $permission],
            ],
            AdminPermission::OP_LEVEL_VIEW,
            $platform,
            $companyId
        );

        $myBuildingIds = $this->getMySalesBuildingIds(
            $adminId,
            array(
                $permission,
            ),
            AdminPermission::OP_LEVEL_VIEW,
            $platform,
            $companyId
        );

        $result = array(
            'company_id' => $companyId,
            'user_id' => $adminId,
            'building_ids' => $myBuildingIds,
        );

        return $result;
    }

    /**
     * @param $adminId
     * @param $permissionKeys
     * @param int  $opLevel
     * @param null $platform
     * @param null $salesCompanyId
     *
     * @return array
     */
    public function getMySalesBuildingIds(
        $adminId,
        $permissionKeys,
        $opLevel = AdminPermission::OP_LEVEL_VIEW,
        $platform = null,
        $salesCompanyId = null
    ) {
        // get permission
        if (empty($permissionKeys)) {
            return array();
        }

        if (is_null($platform)) {
            // get platform cookies
            $adminPlatform = $this->container->get('sandbox_api.admin_platform')->getAdminPlatform();
            $platform = $adminPlatform['platform'];
            $salesCompanyId = $adminPlatform['sales_company_id'];
        }

        $isSuperAdmin = $this->hasSuperAdminPosition(
            $adminId,
            $platform,
            $salesCompanyId
        );

        // check permission by sales monitoring permission
        $hasSalesMonitoringPermission = $this->checkSalesMonitoringPermission(
            $platform,
            $adminId
        );

        if ($isSuperAdmin || $hasSalesMonitoringPermission) {
            // if user is super admin, get all buildings
            $myBuildings = $this->doctrine
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

        if (in_array(AdminPermission::KEY_SALES_PLATFORM_BUILDING, $permissionKeys) ||
            in_array(AdminPermission::KEY_SALES_PLATFORM_INVOICE, $permissionKeys)) {
            foreach ($myPermissions  as $myPermission) {
                if (AdminPermission::KEY_SALES_PLATFORM_BUILDING == $myPermission['key'] ||
                    AdminPermission::KEY_SALES_PLATFORM_INVOICE == $myPermission['key']) {
                    $myBuildings = $this->doctrine
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
            }
        }

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
}
