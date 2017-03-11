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
    public function array_sort($array, $keys, $type = 'asc')
    {
        if (!is_array($array) || empty($array) || !in_array(strtolower($type), array('asc', 'desc'))) {
            return '';
        }

        $keysValue = array();
        foreach ($array as $key => $val) {
            $val[$keys] = str_replace('-', '', $val[$keys]);
            $val[$keys] = str_replace(' ', '', $val[$keys]);
            $val[$keys] = str_replace(':', '', $val[$keys]);
            $keysValue[] = $val[$keys];
        }

        asort($keysValue); // sort by key
        reset($keysValue); // a pointer to the first array again

        $keySort = array();
        foreach ($keysValue as $key => $val) {
            $keySort[] = $key;
        }

        $keysValue = array();
        $count = count($keySort);
        if (strtolower($type) != 'asc') {
            for ($i = $count - 1; $i >= 0; --$i) {
                $keysValue[] = $array[$keySort[$i]];
            }
        } else {
            for ($i = 0; $i < $count; ++$i) {
                $keysValue[] = $array[$keySort[$i]];
            }
        }

        return $keysValue;
    }

    /**
     * @param $array
     *
     * @return array
     */
    public function remove_duplicate($array)
    {
        $result = array();
        for ($i = 0; $i < count($array); ++$i) {
            $source = $array[$i];
            if (array_search($source, $array) == $i && $source != '') {
                $result[] = $source;
            }
        }

        return $result;
    }
}
