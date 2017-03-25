<?php

namespace Sandbox\ApiBundle\Service;

use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;

/**
 * Class AdminPermissionCheckService
 * @package Sandbox\ApiBundle\Service
 */
class AdminPermissionCheckService
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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

        throw new AccessDeniedException(self::NOT_ALLOWED_MESSAGE);
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
        $superAdminPositionBindings = $this->getDoctrine()
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
}