<?php

namespace Sandbox\ApiBundle\Repository\Lease;

use Doctrine\ORM\EntityRepository;

class LeaseClueRepository extends EntityRepository
{
    public function findClues(
        $salesCompanyId,
        $buildingId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('lc')
            ->where('lc.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId);

        if ($buildingId) {
            $query->where('lc.buildingId = :building')
                ->setParameter('building', $buildingId);
        }

        $query->orderBy('lc.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    public function countClues(
        $salesCompanyId,
        $buildingId
    ) {
        $query = $this->createQueryBuilder('lc')
            ->select('count(lc.id)')
            ->where('lc.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId);

        if ($buildingId) {
            $query->where('lc.buildingId = :building')
                ->setParameter('building', $buildingId);
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return $result;
    }
}
