<?php

namespace Sandbox\SalesApiBundle\Controller;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;

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
        $adminPlatform = $this->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $salesCompanyId = $adminPlatform['sales_company_id'];

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
     * @return mixed
     */
    protected function getSalesCompanyId()
    {
        $adminPlatform = $this->getAdminPlatform();

        return $adminPlatform['sales_company_id'];
    }
}
