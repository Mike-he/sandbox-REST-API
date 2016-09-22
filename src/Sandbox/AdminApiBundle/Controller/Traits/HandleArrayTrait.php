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
trait HandleArrayTrait
{
    private function array_sort($array, $keys, $type = 'asc')
    {
        if (!is_array($array) || empty($array) || !in_array(strtolower($type), array('asc', 'desc'))) {
            return '';
        }
        $keysvalue = array();
        foreach ($array as $key => $val) {
            $val[$keys] = str_replace('-', '', $val[$keys]);
            $val[$keys] = str_replace(' ', '', $val[$keys]);
            $val[$keys] = str_replace(':', '', $val[$keys]);
            $keysvalue[] = $val[$keys];
        }
        asort($keysvalue); //key值排序
        reset($keysvalue); //指针重新指向数组第一个
        foreach ($keysvalue as $key => $vals) {
            $keysort[] = $key;
        }
        $keysvalue = array();
        $count = count($keysort);
        if (strtolower($type) != 'asc') {
            for ($i = $count - 1; $i >= 0; --$i) {
                $keysvalue[] = $array[$keysort[$i]];
            }
        } else {
            for ($i = 0; $i < $count; ++$i) {
                $keysvalue[] = $array[$keysort[$i]];
            }
        }

        return $keysvalue;
    }
}
