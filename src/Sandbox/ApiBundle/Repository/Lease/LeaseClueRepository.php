<?php

namespace Sandbox\ApiBundle\Repository\Lease;

use Doctrine\ORM\EntityRepository;

class LeaseClueRepository extends EntityRepository
{
    public function findClues(
        $myBuildingIds,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('lc')
            ->where('lc.buildingId in (:buildings)')
            ->setParameter('buildings', $myBuildingIds);

        $query->orderBy('lc.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    public function countClues(
        $myBuildingIds
    ) {
        $query = $this->createQueryBuilder('lc')
            ->select('count(lc.id)')
            ->where('lc.buildingId in (:buildings)')
            ->setParameter('buildings', $myBuildingIds);

        $result = $query->getQuery()->getSingleScalarResult();

        return $result;
    }
}
