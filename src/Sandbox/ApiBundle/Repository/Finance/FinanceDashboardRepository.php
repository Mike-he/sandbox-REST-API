<?php

namespace Sandbox\ApiBundle\Repository\Finance;

use Doctrine\ORM\EntityRepository;

class FinanceDashboardRepository extends EntityRepository
{
    public function getTimePeriods(
        $type
    ) {
        $query = $this->createQueryBuilder('d')
            ->select('DISTINCT(d.timePeriod)')
            ->where('d.type = :type')
            ->setParameter('type', $type);

        $periods = $query->getQuery()->getResult();
        $periods = array_map('current', $periods);

        return $periods;
    }
}
