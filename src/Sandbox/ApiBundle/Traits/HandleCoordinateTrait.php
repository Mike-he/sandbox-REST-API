<?php

namespace Sandbox\ApiBundle\Traits;

trait HandleCoordinateTrait
{
    /**
     * @param float $firstLatitude
     * @param float $firstLongitude
     * @param float $secondLatitude
     * @param float $secondLongitude
     *
     * @return float distance in kilometers
     */
    public function calculateDistanceBetweenCoordinates(
        $firstLatitude,
        $firstLongitude,
        $secondLatitude,
        $secondLongitude
    ) {
        $radFirstLatitude = deg2rad($firstLatitude);
        $radFirstLongitude = deg2rad($firstLongitude);
        $radSecondLatitude = deg2rad($secondLatitude);
        $radSecondLongitude = deg2rad($secondLongitude);

        $distanceLatitudes = $radSecondLatitude - $radFirstLatitude;
        $distanceLongitudes = $radSecondLongitude - $radFirstLongitude;

        $angle = 2 * asin(sqrt(pow(sin($distanceLatitudes / 2), 2) +
                cos($radFirstLatitude) * cos($radSecondLatitude) *
                pow(sin($distanceLongitudes / 2), 2)));

        $s = $angle * 6371;

        $s = round($s, 3);

        return $s;
    }
}
