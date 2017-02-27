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
        $date = round(microtime(true) * 1000).rand(1000, 9999);

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

    public function generateAccessNumber()
    {
        $date = new \DateTime('now');

        return 'AN'.$date->format('YmdHis').rand(1000, 9999);
    }
}
