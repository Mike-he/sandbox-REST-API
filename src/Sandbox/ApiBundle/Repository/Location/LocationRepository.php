<?php

namespace Sandbox\ApiBundle\Repository\Location;

use Doctrine\ORM\EntityRepository;

class LocationRepository extends EntityRepository
{
    /**
     * @param float $latitude
     * @param float $longitude
     * @param int   $range
     *
     * @return array
     */
    public function findClosestBuilding(
        $lat,
        $lng,
        $range
    ) {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT rb,
                  (
                    6371
                    * acos(cos(radians(:latitude)) * cos(radians(rb.lat))
                    * cos(radians(rb.lng) - radians(:longitude))
                    + sin(radians(:latitude)) * sin(radians(rb.lat)))
                    ) as HIDDEN distance
                    FROM SandboxApiBundle:Room\RoomBuilding rb
                    HAVING distance < :range
                    ORDER BY distance ASC
                '
            )
            ->setParameter('latitude', $lat)
            ->setParameter('longitude', $lng)
            ->setParameter('range', $range)
            ->setMaxResults(1);

        return $query->getResult();
    }
}
