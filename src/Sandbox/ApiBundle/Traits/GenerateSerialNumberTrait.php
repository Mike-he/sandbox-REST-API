<?php

namespace Sandbox\ApiBundle\Traits;

trait GenerateSerialNumberTrait
{
    /**
     * @param $letter
     *
     * @return string
     */
    public function generateSerialNumber(
        $letter
    ) {
        $date = round(microtime(true) * 1000);

        return $letter.$date;
    }
}
