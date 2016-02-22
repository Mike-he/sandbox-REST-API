<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;

class RoomBuildingRepository extends EntityRepository
{
    /**
     * Get list of room buildings.
     *
     * @param int    $cityId
     * @param string $query
     * @param array  $myBuildingIds
     *
     * @return array
     */
    public function getRoomBuildings(
        $cityId,
        $query,
        $myBuildingIds
    ) {
        $notFirst = false;
        $buildingsQuery = $this->createQueryBuilder('rb');

        // query by key words
        if (!is_null($query)) {
            $buildingsQuery->where('rb.name LIKE :query')
                ->andWhere('rb.address LIKE :query')
                ->setParameter('query', $query.'%');

            $notFirst = true;
        }

        // query by city id
        if (!is_null($cityId)) {
            if ($notFirst) {
                $buildingsQuery->andWhere('rb.cityId = :cityId');
            } else {
                $buildingsQuery->where('rb.cityId = :cityId');
            }
            $buildingsQuery->setParameter('cityId', $cityId);
        }

        $buildingsQuery->andWhere('rb.id IN (:ids)');
        $buildingsQuery->setParameter('ids', $myBuildingIds);

        // order by creation date
        $buildingsQuery->orderBy('rb.creationDate', 'DESC');

        return $buildingsQuery->getQuery()->getResult();
    }

    /**
     * @param $companyId
     *
     * @return array
     */
    public function getMySalesBuildings(
        $companyId
    ) {
        $query = $this->createQueryBuilder('b')
            ->select('b.id as buildingId')
            ->where('b.companyId = :companyId')
            ->setParameter('companyId', $companyId);

        return $query->getQuery()->getResult();
    }

    /**
     * @param string $lat
     * @param string $lng
     * @param int    $range
     *
     * @return array
     */
    public function findNearbyBuildings(
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

    /**
     * @return array
     */
    public function getDistinctServers()
    {
        $query = $this->createQueryBuilder('rb')
            ->select('DISTINCT rb.server')
            ->getQuery();

        return $query->getResult();
    }
}
