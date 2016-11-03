<?php

namespace Sandbox\ApiBundle\Traits;

trait HandleCoordinateTrait
{
    /**
     * @param double $firstLatitude
     * @param double $firstLongitude
     * @param double $secondLatitude
     * @param double $secondLongitude
     *
     * @return double distance in kilometers
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

        return $angle * 6371;
    }
}
