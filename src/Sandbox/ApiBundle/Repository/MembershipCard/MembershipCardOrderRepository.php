<?php

namespace Sandbox\ApiBundle\Repository\MembershipCard;

use Doctrine\ORM\EntityRepository;

class MembershipCardOrderRepository extends EntityRepository
{
    /**
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function getOfficialCardOrders(
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('mo')
            ->where('mo.paymentDate >= :start')
            ->andWhere('mo.paymentDate <= :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        return $query->getQuery()->getResult();
    }
}