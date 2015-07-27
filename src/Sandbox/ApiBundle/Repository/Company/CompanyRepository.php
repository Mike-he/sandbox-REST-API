<?php

namespace Sandbox\ApiBundle\Repository\Company;

use Doctrine\ORM\EntityRepository;

class CompanyRepository extends EntityRepository
{
    /**
     * @param float $latitude
     * @param float $longitude
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    public function findNearbyCompanies(
        $latitude,
        $longitude,
        $limit,
        $offset
    ) {
        // find nearby buildings
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT rb.id,
                  (
                    6371
                    * acos(cos(radians(:latitude)) * cos(radians(rb.lat))
                    * cos(radians(rb.lng) - radians(:longitude))
                    + sin(radians(:latitude)) * sin(radians(rb.lat)))
                    ) as HIDDEN distance
                    FROM SandboxApiBundle:Room\RoomBuilding rb
                    HAVING distance < :range
                    ORDER BY distance
                '
            )
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('range', 100);

        $buildingIds = $query->getResult();

        // find companies
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT c,
                  field(c.buildingId, :buildingIds) as HIDDEN field
                  FROM SandboxApiBundle:Company\Company c

                  WHERE

                  c.buildingId IN (:buildingIds)
                  ORDER BY field
                '
            )
            ->setParameter('buildingIds', $buildingIds);

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }
}
