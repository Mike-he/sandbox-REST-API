<?php

namespace Sandbox\ApiBundle\Repository\Lease;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;

class LeaseBillRepository extends EntityRepository
{
    /**
     * @param $lease
     *
     * @return array
     */
    public function findBills(
        $lease
    ) {
        $query = $this->createQueryBuilder('lb')
            ->where('lb.status != :status')
            ->andWhere('lb.lease = :lease')
            ->setParameter('status', LeaseBill::STATUS_PENDING)
            ->setParameter('lease', $lease);

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
