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
}
