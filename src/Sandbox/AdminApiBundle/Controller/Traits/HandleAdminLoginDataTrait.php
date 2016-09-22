<?php

namespace Sandbox\AdminApiBundle\Controller\Traits;

/**
 * Announcement Notification Trait.
 *
 * @category Sandbox
 *
 * @author   Albert Feng <albert.f@sandbox3.cn>
 * @license  http://www.Sandbox3.cn/ Proprietary
 *
 * @link     http://www.Sandbox3.cn/
 */
trait HandleAdminLoginDataTrait
{
    use HandleArrayTrait;

    private function handlePositionData($positions)
    {
        $platform = array();
        foreach ($positions as $position) {
            switch ($position['platform']) {
                case 'shop':
                    $platform['shop'][] = $position;
                    break;
                case 'sales':
                    $platform['sales'][] = $position;
                    break;
                default:
                    $platform['official'][] = $position;
            }
        }

        return $platform;
    }

    private function handlePermissionData($permissions)
    {
        $data = array();
        foreach ($permissions as $permission) {
            $data[$permission['id']][] = $permission;
        }

        $newPermissions = array();
        foreach ($data as $item) {
            if (count($item) > 1) {
                $item = $this->array_sort($item, 'op_level', 'desc');

                $newPermissions[] = $item[0];
            } else {
                $newPermissions[] = $item[0];
            }
        }

        return $newPermissions;
    }
}
