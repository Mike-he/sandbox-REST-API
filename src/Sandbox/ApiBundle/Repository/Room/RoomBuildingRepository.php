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
     *
     * @return array
     */
    public function getRoomBuildings(
        $cityId,
        $query
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

        // order by creation date
        $buildingsQuery->orderBy('rb.creationDate', 'DESC');

        return $buildingsQuery->getQuery()->getResult();
    }
}
