<?php

namespace Sandbox\SalesApiBundle\Controller;

use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;

class SalesRestController extends ChatGroupController
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
            $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
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

        if (in_array(AdminPermission::KEY_SALES_PLATFORM_BUILDING, $permissionKeys) ||
        in_array(AdminPermission::KEY_SALES_PLATFORM_INVOICE, $permissionKeys)) {
            foreach ($myPermissions  as $myPermission) {
                if (AdminPermission::KEY_SALES_PLATFORM_BUILDING == $myPermission['key'] ||
                    AdminPermission::KEY_SALES_PLATFORM_INVOICE == $myPermission['key']) {
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

    /**
     * @return mixed
     */
    protected function getSalesCompanyId()
    {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();

        return $adminPlatform['sales_company_id'];
    }
}
