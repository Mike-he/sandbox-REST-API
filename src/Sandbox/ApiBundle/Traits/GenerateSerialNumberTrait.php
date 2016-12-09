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

    public function generateLeaseSerialNumber()
    {
        $date = new \DateTime('now');

        return 'C'.'-'.
            $date->format('Ymd').'-'.
            $date->format('His').'-'.
            rand(100, 999);
    }
}
