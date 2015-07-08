<?php

namespace Sandbox\ApiBundle\Repository\Order;

use Doctrine\ORM\EntityRepository;

class OrderRepository extends EntityRepository
{
    public function checkProductForClient(
        $productId,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->Where('o.productId = :productId')
            ->andWhere('o.status <> \'cancelled\'')
            ->andWhere(
                '(
                    (o.startDate <= :startDate AND o.endDate > :startDate) OR
                    (o.startDate < :endDate AND o.endDate >= :endDate)
                )'
            )
            ->setParameter('productId', $productId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery();

        return $query->getResult();
    }
}
